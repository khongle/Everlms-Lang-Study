import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/feature/auth/data/models/signup_body.dart';
import 'package:everlms/feature/auth/data/repositories/auth_repository.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'package:jwt_decoder/jwt_decoder.dart';
import 'package:msal_auth/msal_auth.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class AuthState {
  const AuthState({
    this.isLoggedIn = false,
    this.isLoading = false,
    this.errorMessage,
    this.rememberMe = false,
    this.savedNumber = '',
    this.savedPassword = '',
    this.checkSupportSocialAuth = false,
    this.forgotPasswordUid = '',
    this.pendingPassword = '',
    this.pendingOtpToken = '',
    this.pendingResetToken = '',
  });

  final bool isLoggedIn;
  final bool isLoading;
  final String? errorMessage;
  final bool rememberMe;
  final String savedNumber;
  final String savedPassword;

  /// Whether the backend supports social authentication.
  final bool checkSupportSocialAuth;

  /// Resolved Drupal UID for the forgot-password flow.
  final String forgotPasswordUid;

  /// Password stored temporarily between sign-up and email-OTP verification.
  final String pendingPassword;

  /// OTP session token returned by /register or /forgot-password endpoints.
  final String pendingOtpToken;

  /// One-time reset token returned after forgot-password OTP verification.
  final String pendingResetToken;

  AuthState copyWith({
    bool? isLoggedIn,
    bool? isLoading,
    String? errorMessage,
    bool? rememberMe,
    String? savedNumber,
    String? savedPassword,
    bool? checkSupportSocialAuth,
    String? forgotPasswordUid,
    String? pendingPassword,
    String? pendingOtpToken,
    String? pendingResetToken,
  }) {
    return AuthState(
      isLoggedIn: isLoggedIn ?? this.isLoggedIn,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      rememberMe: rememberMe ?? this.rememberMe,
      savedNumber: savedNumber ?? this.savedNumber,
      savedPassword: savedPassword ?? this.savedPassword,
      checkSupportSocialAuth:
          checkSupportSocialAuth ?? this.checkSupportSocialAuth,
      forgotPasswordUid: forgotPasswordUid ?? this.forgotPasswordUid,
      pendingPassword: pendingPassword ?? this.pendingPassword,
      pendingOtpToken: pendingOtpToken ?? this.pendingOtpToken,
      pendingResetToken: pendingResetToken ?? this.pendingResetToken,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class AuthNotifier extends AsyncNotifier<AuthState> {
  bool _isFirstLogin = false;
  bool get isFirstLogin => _isFirstLogin;

  void updateFirstLogin(bool value) {
    _isFirstLogin = value;
  }

  Future<bool> isProfileComplete() async {
    final prefs = ref.read(sharedPreferencesProvider);
    final firstName = prefs.getString(AppConstants.userFirstName) ?? '';
    return firstName.isNotEmpty;
  }

  @override
  Future<AuthState> build() async {
    final repo = ref.read(authRepositoryProvider);
    final loggedIn = repo.isLoggedIn();
    final number = repo.getUserNumber();
    final password = repo.getUserPassword();
    return AuthState(
      isLoggedIn: loggedIn,
      savedNumber: number,
      savedPassword: password,
      rememberMe: number.isNotEmpty,
    );
  }

  Future<void> login({
    required String name,
    required String password,
  }) async {
    final current = state.valueOrNull ?? const AuthState();
    state = AsyncData(current.copyWith(isLoading: true));

    try {
      final repo = ref.read(authRepositoryProvider);

      final response = await repo.uidLogin(
        email: name,
        password: password,
      );

      if (response != null && response.statusCode == 200) {
        final body = response.body;
        if (body['access_token'] != null) {
          await _handleUidLoginSuccess(body as Map<String, dynamic>);
          state = AsyncData(
            current.copyWith(
              isLoggedIn: true,
              isLoading: false,
            ),
          );
          appRouter.go(AppRoutes.main);
          return;
        }
      }

      state = AsyncData(
        current.copyWith(
          isLoading: false,
          errorMessage: response?.body?['message']?.toString(),
        ),
      );
      customSnackBar(
        response?.body?['message']?.toString() ??
            'Invalid username or password.',
      );
    } on DioException catch (e) {
      final apiError = e.error;
      final message = apiError is ApiException
          ? apiError.message
          : (e.message ?? 'Invalid username or password.');
      state = AsyncData(
        current.copyWith(
          isLoading: false,
          errorMessage: message,
        ),
      );
      customSnackBar(message);
    } on ApiException catch (e) {
      state = AsyncData(
        current.copyWith(
          isLoading: false,
          errorMessage: e.message,
        ),
      );
      customSnackBar(e.message);
    } catch (e) {
      state = AsyncData(
        current.copyWith(
          isLoading: false,
          errorMessage: 'An unexpected error occurred. Please try again.',
        ),
      );
      customSnackBar('An unexpected error occurred. Please try again.');
    }
  }

  /// Stores UID Login tokens (uid + access_token) and updates the API client header.
  /// Used by [login], [verifyRegisterOtpServer], and any flow that receives
  /// a `{uid, access_token}` response from the custom UID Login endpoints.
  Future<void> _handleUidLoginSuccess(Map<String, dynamic> body) async {
    final repo = ref.read(authRepositoryProvider);
    final apiClient = ref.read(apiClientProvider);

    final accessToken = body['access_token'] as String;
    final uid = body['uid']?.toString() ?? '';

    await repo.saveUserToken(accessToken);
    if (uid.isNotEmpty) await repo.saveUserUid(uid);

    final prefs = ref.read(sharedPreferencesProvider);
    apiClient.updateHeader(
      accessToken,
      prefs.getString(AppConstants.languageCode),
    );

    try {
      await repo.updateToken();
    } catch (_) {
      // FCM token update is non-critical — a failure must not break the login
      // session. _AuthInterceptor may have cleared the token on a 401 response,
      // so restore it here to keep the user authenticated.
      await repo.saveUserToken(accessToken);
      apiClient.updateHeader(
        accessToken,
        prefs.getString(AppConstants.languageCode),
      );
    }
  }

  /// Handles the legacy session-cookie login response (used by social auth).
  Future<void> _handleLoginSuccess(Map<String, dynamic> body) async {
    final repo = ref.read(authRepositoryProvider);
    final apiClient = ref.read(apiClientProvider);
    final currentUser = body['current_user'];
    final cookie = body['session_cookie'] as String?;
    final csrfToken = body['csrf_token'] as String?;
    final logoutToken = body['logout_token'] as String?;
    final uid = currentUser['uid']?.toString();

    if (cookie != null) await repo.saveCookie(cookie);
    if (csrfToken != null) await repo.saveUserCsrfToken(csrfToken);
    if (logoutToken != null) await repo.saveLogoutToken(logoutToken);
    if (uid != null) await repo.saveUserUid(uid);

    final token = cookie ?? '';
    await repo.saveUserToken(token);

    final prefs = ref.read(sharedPreferencesProvider);
    try {
      await repo.updateToken();
    } catch (_) {
      // FCM token update is non-critical — restore session state if
      // _AuthInterceptor cleared the token on a 401 response.
      await repo.saveUserToken(token);
      apiClient.updateHeader(
        token,
        prefs.getString(AppConstants.languageCode),
      );
    }
  }

  Future<void> socialAuth({
    required String name,
    required String email,
    required String avatarUrl,
    required String provider,
  }) async {
    final current = state.valueOrNull ?? const AuthState();
    state = AsyncData(current.copyWith(isLoading: true));

    final repo = ref.read(authRepositoryProvider);
    final response = await repo.socialAuth(
      name: name,
      email: email,
      avatarUrl: avatarUrl,
      provider: provider,
    );

    if (response != null && response.statusCode == 200) {
      final body = response.body;
      if (body['current_user'] != null) {
        await _handleLoginSuccess(body);
        state = AsyncData(
          current.copyWith(
            isLoggedIn: true,
            isLoading: false,
          ),
        );
        appRouter.go(AppRoutes.main);
        return;
      }
    }

    state = AsyncData(
      current.copyWith(
        isLoading: false,
        errorMessage: response?.body?['message']?.toString(),
      ),
    );
  }

  Future<void> logout() async {
    final repo = ref.read(authRepositoryProvider);
    await repo.logout();
    await repo.clearSharedData();
    state = const AsyncData(AuthState());
    appRouter.go(AppRoutes.signIn);
  }

  bool isLoggedIn() {
    return ref.read(authRepositoryProvider).isLoggedIn();
  }

  void toggleRememberMe() {
    final current = state.valueOrNull;
    if (current == null) return;
    state = AsyncData(current.copyWith(rememberMe: !current.rememberMe));
  }

  Future<void> saveUserNumberAndPassword(String number, String password) async {
    await ref
        .read(authRepositoryProvider)
        .saveUserNumberAndPassword(number, password);
  }

  // ---- Registration ----

  /// Stores [password] for use in [registration] after email-OTP verification.
  void storePendingPassword(String password) {
    final current = state.valueOrNull ?? const AuthState();
    state = AsyncData(current.copyWith(pendingPassword: password));
  }

  // ---------------------------------------------------------------------------
  // Mobile API — Registration with server-side OTP
  // ---------------------------------------------------------------------------

  /// Calls POST /api/v1/user/register: creates a pending user and sends OTP.
  /// On success navigates to the email-verification screen and stores the
  /// otp_token in state.
  Future<void> requestRegisterOtp({
    required String email,
    required String password,
  }) async {
    final current = state.valueOrNull ?? const AuthState();
    state = AsyncData(current.copyWith(isLoading: true));

    final repo = ref.read(authRepositoryProvider);
    final response = await repo.registerMobile(
      email: email,
      password: password,
      passwordConfirm: password,
    );

    if (response != null && response.statusCode == 200) {
      final otpToken = response.body['otp_token'] as String? ?? '';
      state = AsyncData(
        current.copyWith(
          isLoading: false,
          pendingPassword: password,
          pendingOtpToken: otpToken,
        ),
      );
      // Navigate to OTP verification screen.
      appRouter.push(
        AppRoutes.getEmailVerificationScreen(AppRoutes.signUp, email),
      );
      return;
    }

    state = AsyncData(current.copyWith(isLoading: false));

    final violations = response?.body?['violations'];
    if (violations is List && violations.isNotEmpty) {
      customSnackBar(
        violations[0]['message']?.toString() ?? 'Registration failed.',
      );
    } else {
      customSnackBar(
        response?.body?['message']?.toString() ??
            'Registration failed. Please try again.',
      );
    }
  }

  /// Verifies the registration OTP and auto-logs in on success.
  /// Returns an error message string on failure, null on success.
  Future<String?> verifyRegisterOtpServer(String otpCode) async {
    final current = state.valueOrNull ?? const AuthState();
    final otpToken = current.pendingOtpToken;

    if (otpToken.isEmpty) {
      return 'Session expired. Please start registration again.';
    }

    state = AsyncData(current.copyWith(isLoading: true));
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.verifyRegisterOtp(
      otpToken: otpToken,
      otpCode: otpCode,
    );

    if (response != null && response.statusCode == 200) {
      final body = response.body;
      if (body['access_token'] != null) {
        // Server returned {uid, access_token} — auto-login via UID Login handler.
        await _handleUidLoginSuccess(body as Map<String, dynamic>);
        state = AsyncData(
          current.copyWith(
            isLoggedIn: true,
            isLoading: false,
            pendingOtpToken: '',
            pendingPassword: '',
          ),
        );
        appRouter.go(AppRoutes.main);
        return null;
      }
      // Fallback: server verified but didn't issue tokens.
      state = AsyncData(current.copyWith(isLoading: false));
      appRouter.go(AppRoutes.getSignInRoute('fromSplash'));
      return null;
    }

    state = AsyncData(current.copyWith(isLoading: false));
    return response?.body?['message']?.toString() ??
        'The code is incorrect or has expired.';
  }

  /// Resends the registration OTP, updating the stored otp_token.
  Future<void> resendRegisterOtpServer() async {
    final current = state.valueOrNull ?? const AuthState();
    final otpToken = current.pendingOtpToken;
    if (otpToken.isEmpty) return;

    final repo = ref.read(authRepositoryProvider);
    final response = await repo.resendRegisterOtp(otpToken);

    if (response != null && response.statusCode == 200) {
      final newToken = response.body['otp_token'] as String? ?? '';
      if (newToken.isNotEmpty) {
        state = AsyncData(current.copyWith(pendingOtpToken: newToken));
      }
    }
  }

  // ---------------------------------------------------------------------------
  // Mobile API — Forgot password with server-side OTP
  // ---------------------------------------------------------------------------

  /// Calls POST /api/v1/user/forgot-password; stores the otp_token in state.
  /// Returns true when the server accepted the request.
  Future<bool> sendForgotPasswordOtp(String email) async {
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.forgotPasswordRequest(email);

    if (response != null && response.statusCode == 200) {
      final otpToken = response.body['otp_token'] as String? ?? '';
      final current = state.valueOrNull ?? const AuthState();
      state = AsyncData(current.copyWith(pendingOtpToken: otpToken));
      return true;
    }
    return false;
  }

  /// Verifies the forgot-password OTP.
  /// On success stores reset_token and navigates to NewPassScreen.
  Future<void> verifyForgotPasswordOtpServer(
    String otpCode,
  ) async {
    final current = state.valueOrNull ?? const AuthState();
    final otpToken = current.pendingOtpToken;

    if (otpToken.isEmpty) {
      customSnackBar('Session expired. Please start over.');
      return;
    }

    state = AsyncData(current.copyWith(isLoading: true));
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.verifyForgotPasswordOtpMobile(
      otpToken: otpToken,
      otpCode: otpCode,
    );

    if (response != null && response.statusCode == 200) {
      final resetToken = response.body['reset_token'] as String? ?? '';
      state = AsyncData(
        current.copyWith(
          isLoading: false,
          pendingResetToken: resetToken,
          pendingOtpToken: '',
        ),
      );
      appRouter.push(AppRoutes.newPasswordScreen);
      return;
    }

    state = AsyncData(current.copyWith(isLoading: false));
    customSnackBar(
      response?.body?['message']?.toString() ??
          'The code is incorrect or has expired.',
    );
  }

  /// Resets the password using the stored one-time reset_token.
  Future<void> resetPasswordWithToken(String newPassword) async {
    final current = state.valueOrNull ?? const AuthState();
    final resetToken = current.pendingResetToken;

    if (resetToken.isEmpty) {
      customSnackBar('Reset session expired. Please start over.');
      return;
    }

    state = AsyncData(current.copyWith(isLoading: true));
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.resetPasswordWithToken(
      resetToken: resetToken,
      password: newPassword,
      passwordConfirm: newPassword,
    );

    if (response != null && response.statusCode == 200) {
      customSnackBar('Password updated successfully.', isError: false);
      state = AsyncData(
        current.copyWith(isLoading: false, pendingResetToken: ''),
      );
      appRouter.go(AppRoutes.getSignInRoute('fromSplash'));
      return;
    }

    state = AsyncData(current.copyWith(isLoading: false));
    customSnackBar(
      response?.body?['message']?.toString() ??
          'Failed to reset password. Please try again.',
    );
  }

  Future<void> registration({
    required String email,
    String password = '',
  }) async {
    final current = state.valueOrNull ?? const AuthState();
    // Use the pre-stored pending password when none is provided directly.
    final resolvedPassword =
        password.isNotEmpty ? password : current.pendingPassword;
    state = AsyncData(current.copyWith(isLoading: true));

    final username = email.split('@').first;
    final signUpBody = SignUpBody(
      username: username,
      email: email,
      pass: resolvedPassword,
    );
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.registration(signUpBody);

    if (response != null && response.statusCode == 200) {
      customSnackBar('Registration successful', isError: false);
      final uid = response.body['data']['uid'][0]['value'].toString();
      await repo.saveUserUid(uid);
      appRouter.go(AppRoutes.getSignInRoute('fromSplash'));
    } else {
      customSnackBar(
        'Something went wrong. Please use another email or try again.',
      );
    }
    state = AsyncData(current.copyWith(isLoading: false));
  }

  Future<void> autoLoginDefault() async {
    Config.defaultAccountName =
        'Guest_${DateTime.now().millisecondsSinceEpoch}';
    Config.defaultAccountPass =
        DateTime.now().millisecondsSinceEpoch.toString();
    Config.defaultAccountMail =
        '${Config.defaultAccountName}.training@gmail.com';
    final signUpBody = SignUpBody(
      username: Config.defaultAccountName,
      email: Config.defaultAccountMail,
      pass: Config.defaultAccountPass,
    );
    final repo = ref.read(authRepositoryProvider);
    final regResponse = await repo.registration(signUpBody);
    if (regResponse != null && regResponse.statusCode == 200) {
      final loginResponse = await repo.login(
        name: Config.defaultAccountName,
        password: Config.defaultAccountPass,
      );
      if (loginResponse != null && loginResponse.statusCode == 200) {
        await _handleLoginSuccessJwt(loginResponse);
      }
    }
  }

  Future<String?> getUserDrupalUID(String email) async {
    final repo = ref.read(authRepositoryProvider);
    final response = await repo.userDataDrupal(email);
    final data = response?.body['data'];
    if (data != null && (data as List).isNotEmpty) {
      final uid = data[0]['attributes']['drupal_internal__uid'];
      if (uid != null) return uid.toString();
    }
    return null;
  }

  // ---- Email OTP ---- (kept for backward compat; routes to server for forgot-password)

  final Map<String, String> _otp = {};
  int _attemptsVerification = 0;
  int get attemptsVerification => _attemptsVerification;
  final int _timeoutOtp = 600;

  /// Sends a server-side OTP for the forgot-password flow.
  /// Returns true when the server accepted the request.
  Future<bool> sendEmailOTP(String email) async {
    _attemptsVerification = 0;
    return sendForgotPasswordOtp(email);
  }

  String? validateEmailOTP(String code, String fromPage) {
    if (_isExpireEmailOTP()) return 'Your OTP has expired.';
    if (_otp.containsKey('otp') && code == _otp['otp']) return null;
    if (code.length == 6) _attemptsVerification++;
    if (fromPage == AppRoutes.forgotPasswordScreen &&
        _attemptsVerification != 0 &&
        _attemptsVerification <= 5) {
      return 'You have ${5 - _attemptsVerification} attempts left.';
    }
    if (_attemptsVerification % 3 == 0 && _attemptsVerification != 0) {
      return 'Incorrect OTP. Too many attempts. Try another email.';
    }
    return 'Incorrect OTP';
  }

  bool _isExpireEmailOTP() {
    if (_otp.containsKey('created')) {
      final created = DateTime.parse(_otp['created'] ?? '');
      return DateTime.now().difference(created).inSeconds > _timeoutOtp;
    }
    return false;
  }

  Future<void> blockUser(String uid) async {
    await ref.read(authRepositoryProvider).blockUser(uid);
  }

  // ---- Password Management ----

  Future<void> verifyOTPForForgotPassword(String email, String otp) async {
    await verifyForgotPasswordOtpServer(otp);
  }

  Future<void> resetPassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    final current = state.valueOrNull ?? const AuthState();
    if (currentPassword.isEmpty) {
      customSnackBar('Your current password is missing');
      return;
    }
    if (newPassword != confirmPassword) {
      customSnackBar('Passwords do not match');
      return;
    }
    state = AsyncData(current.copyWith(isLoading: true));

    final repo = ref.read(authRepositoryProvider);
    final response = await repo.resetPassword(
      currentPassword: currentPassword,
      newPassword: confirmPassword,
    );

    if (response != null && response.statusCode == 200) {
      appRouter.go(AppRoutes.getSignInRoute(AppRoutes.changePassword));
      customSnackBar(
        'Change password successfully, please login again',
        isError: false,
      );
    } else {
      if (response?.statusCode == 422) {
        customSnackBar('Your current password is missing or incorrect');
      } else {
        customSnackBar(response?.body['message']);
      }
    }
    state = AsyncData(current.copyWith(isLoading: false));
  }

  // ---- Social Auth ----

  final GoogleSignIn _googleSignIn = GoogleSignIn(scopes: ['email', 'profile']);
  SingleAccountPca? _msalAuth;

  Future<void> allowSocialAuth() async {
    // Always show social auth without calling the API
    final current = state.valueOrNull ?? const AuthState();
    state = AsyncData(
      current.copyWith(
        checkSupportSocialAuth: true,
      ),
    );
  }

  Future<void> googleAuth() async {
    final current = state.valueOrNull ?? const AuthState();
    try {
      final googleAccount = await _googleSignIn.signIn();
      if (googleAccount == null) {
        customSnackBar('Something went wrong');
        return;
      }
      final auth = await googleAccount.authentication;
      if (auth.idToken == null) throw Exception('Google idToken is null');

      state = AsyncData(current.copyWith(isLoading: true));
      final repo = ref.read(authRepositoryProvider);
      final response = await repo.socialAuth(
        name: googleAccount.displayName ?? '',
        email: googleAccount.email,
        avatarUrl: googleAccount.photoUrl ?? '',
        provider: 'google',
      );
      await _handleSocialLoginSuccess(response, 'google');
    } on Exception catch (e) {
      if (kDebugMode) {
        debugPrint('Google login error: $e');
      }
    } finally {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }

  Future<void> facebookAuth() async {
    final current = state.valueOrNull ?? const AuthState();
    try {
      final result = await FacebookAuth.instance.login();
      if (result.status == LoginStatus.success) {
        state = AsyncData(current.copyWith(isLoading: true));
        final user = await FacebookAuth.instance.getUserData();
        final email =
            user['email'] ?? '${user['id']}.everlms@facebookuser.temp';
        final repo = ref.read(authRepositoryProvider);
        final response = await repo.socialAuth(
          name: user['name'],
          email: email,
          avatarUrl: user['picture']['data']['url'],
          provider: 'facebook',
        );
        await _handleSocialLoginSuccess(response, 'facebook');
      } else if (result.status == LoginStatus.failed) {
        customSnackBar('Facebook login failed');
      }
    } on Exception {
      customSnackBar('Facebook login failed');
    }
    state = AsyncData(current.copyWith(isLoading: false));
  }

  Future<void> microsoftAuth() async {
    final current = state.valueOrNull ?? const AuthState();
    try {
      const filePath = 'assets/msal_config.json';
      final clientID = dotenv.env['CLIENT_ID'] ?? '';
      final redirect = kDebugMode
          ? dotenv.env['AAD_DEBUG_ANDROID_REDIRECT_URI']
          : dotenv.env['AAD_RELEASE_ANDROID_REDIRECT_URI'];
      _msalAuth = await SingleAccountPca.create(
        clientId: clientID,
        androidConfig: AndroidConfig(
          configFilePath: filePath,
          redirectUri: redirect ?? '',
        ),
        appleConfig: AppleConfig(broker: Broker.webView),
      );
      final authResult = await _msalAuth?.acquireToken(
        scopes: ['https://graph.microsoft.com/user.read'],
        prompt: Prompt.selectAccount,
      );
      if (authResult != null) {
        state = AsyncData(current.copyWith(isLoading: true));
        final token = authResult.accessToken;
        final graphResponse = await http.get(
          Uri.parse(r'https://graph.microsoft.com/v1.0/me/photo/$value'),
          headers: {'Authorization': 'Bearer $token'},
        );
        var avatarUrl = '';
        if (graphResponse.statusCode == 200) {
          avatarUrl = base64Encode(graphResponse.bodyBytes);
        }
        final repo = ref.read(authRepositoryProvider);
        final response = await repo.socialAuth(
          name: authResult.account.name ?? '',
          email: authResult.account.username ?? '',
          avatarUrl: avatarUrl,
          provider: 'microsoft',
        );
        await _handleSocialLoginSuccess(response, 'microsoft');
      }
    } on MsalException catch (e) {
      if (e is! MsalUserCancelException) {
        customSnackBar('Microsoft login failed');
      }
    }
    state = AsyncData(current.copyWith(isLoading: false));
  }

  Future<void> _handleSocialLoginSuccess(
    ApiResponse? response,
    String provider,
  ) async {
    if (response != null && response.statusCode == 200) {
      await _handleLoginSuccessJwt(response);
      final prefs = ref.read(sharedPreferencesProvider);
      await prefs.setString(AppConstants.provider, provider);
      state = AsyncData(
        (state.valueOrNull ?? const AuthState()).copyWith(
          isLoggedIn: true,
          isLoading: false,
        ),
      );
      appRouter.go(AppRoutes.getMainRoute('0'));
    } else {
      customSnackBar('Sorry, unrecognized email or password.');
    }
  }

  Future<void> _handleLoginSuccessJwt(ApiResponse response) async {
    final repo = ref.read(authRepositoryProvider);
    final setCookie = response.headers?['set-cookie'];
    if (setCookie != null) {
      await repo.saveCookie(setCookie.split(';')[0]);
    }
    final token = response.body['access_token'] as String?;
    if (token == null) return;
    await repo.saveUserToken(token);
    final csrfToken = response.body['csrf_token'] as String?;
    if (csrfToken != null) await repo.saveUserCsrfToken(csrfToken);
    final logoutToken = response.body['logout_token'] as String?;
    if (logoutToken != null) await repo.saveLogoutToken(logoutToken);
    try {
      final decodedToken = JwtDecoder.decode(token);
      final uid = decodedToken['drupal']['uid'].toString();
      await repo.saveUserUid(uid);
    } catch (_) {
      // Not a Drupal JWT — skip uid extraction.
    }
    await repo.updateToken();
  }

  Future<void> socialLogout() async {
    final prefs = ref.read(sharedPreferencesProvider);
    final provider = prefs.getString(AppConstants.provider);
    if (provider == null) return;
    try {
      switch (provider) {
        case 'google':
          await _googleSignIn.signOut();
        case 'microsoft':
          await _msalAuth?.signOut();
        case 'facebook':
          await FacebookAuth.instance.logOut();
      }
    } on Exception catch (_) {}
    prefs.remove(AppConstants.provider);
  }

  /// Pre-loads essential app data after login.
  /// Replaces the old GetX `AuthController.loadingData()`.
  Future<void> loadAppData() async {
    await Future.wait([
      ref.read(userProfileProvider.notifier).getUserProfile(),
      ref.read(homeProvider.notifier).refreshAllCourses().then((_) {
        ref.read(cartProvider.notifier).getCartList();
      }),
    ]);
    ref.read(forumProvider.notifier).handleRefresh();
    ref.read(homeProvider.notifier).loadMyCourses();
    ref.read(myCourseProvider.notifier).loadMyCourses();
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final authProvider =
    AsyncNotifierProvider<AuthNotifier, AuthState>(AuthNotifier.new);
