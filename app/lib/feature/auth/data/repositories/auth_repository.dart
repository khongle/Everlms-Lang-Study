import 'dart:io';

import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/auth/data/models/signup_body.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthRepository {
  const AuthRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  Future<ApiResponse?> registration(SignUpBody signUpBody) async {
    return _apiClient.postData(AppConstants.register, signUpBody.toJson());
  }

  Future<ApiResponse?> sendEmailOTP(Map<String, dynamic> body) async {
    return _apiClient.postData(AppConstants.sendEmailOTP, body);
  }

  Future<ApiResponse?> registerWithPhoneNumber(SignUpBody body) async {
    return _apiClient.postData(
      AppConstants.registerWithPhone,
      body.toJsonForPhone(),
    );
  }

  Future<ApiResponse?> login({
    required String name,
    required String password,
  }) async {
    _apiClient.updateHeader(null, AppConstants.languageCode);
    return _apiClient
        .postData(AppConstants.loginUrl, {'name': name, 'pass': password});
  }

  Future<ApiResponse?> socialAuth({
    required String name,
    required String email,
    required String avatarUrl,
    required String provider,
  }) async {
    _apiClient.updateHeader(null, AppConstants.languageCode);
    _prefs.setString(AppConstants.provider, provider);
    final body = <String, dynamic>{
      'name': name,
      'mail': email,
      'avatar_url': avatarUrl,
    };
    return _apiClient.postData(AppConstants.socialAuth, body);
  }

  Future<ApiResponse?> logout() async {
    final token = _prefs.getString(AppConstants.logoutToken);
    return _apiClient.postData(AppConstants.logout(token ?? ''), {});
  }

  Future<ApiResponse?> updateToken() async {
    String? deviceToken;
    if (Platform.isIOS) {
      FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );
      final settings = await FirebaseMessaging.instance.requestPermission();
      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        deviceToken = await _saveDeviceToken();
      }
    } else {
      deviceToken = await _saveDeviceToken();
    }
    return _apiClient.postData(
      AppConstants.tokenUrl,
      {'_method': 'put', 'fcm_token': deviceToken},
    );
  }

  Future<String?> _saveDeviceToken() async {
    String? deviceToken = '@';
    try {
      deviceToken = await FirebaseMessaging.instance.getToken();
    } on Exception {
      /**/
    }
    return deviceToken;
  }

  Future<ApiResponse?> verifyOTPForForgotPassword(
    String email,
    String otp,
  ) async {
    return _apiClient.postData(
      AppConstants.verifyOTPForForgotPassword,
      {'email': email, 'otp': otp},
    );
  }

  // ---------------------------------------------------------------------------
  // Mobile API — OAuth2 + new auth endpoints
  // ---------------------------------------------------------------------------

  /// OAuth2 password-grant login.
  /// Sends [application/x-www-form-urlencoded] as required by the spec.
  Future<ApiResponse?> oauthLogin({
    required String email,
    required String password,
    required String clientId,
    String clientSecret = '',
    String scope = '',
  }) async {
    final body = <String, dynamic>{
      'grant_type': 'password',
      'client_id': clientId,
      'client_secret': clientSecret,
      'username': email,
      'password': password,
    };
    if (scope.isNotEmpty) body['scope'] = scope;
    return _apiClient.postFormUrlEncoded(AppConstants.oauthToken, body);
  }

  /// Creates a pending (blocked) user and sends an OTP email.
  /// Returns {status, message, otp_token}.
  Future<ApiResponse?> registerMobile({
    required String email,
    required String password,
    required String passwordConfirm,
    String name = '',
  }) async {
    return _apiClient.postData(AppConstants.mobileRegister, {
      'name': name,
      'email': email,
      'password': password,
      'password_confirm': passwordConfirm,
    });
  }

  /// Verifies the registration OTP; activates the user and returns OAuth tokens.
  Future<ApiResponse?> verifyRegisterOtp({
    required String otpToken,
    required String otpCode,
  }) async {
    return _apiClient.postData(AppConstants.mobileRegisterVerifyOtp, {
      'otp_token': otpToken,
      'otp_code': otpCode,
    });
  }

  /// Resends the registration OTP email; returns a new otp_token.
  Future<ApiResponse?> resendRegisterOtp(String otpToken) async {
    return _apiClient.postData(AppConstants.mobileRegisterResendOtp, {
      'otp_token': otpToken,
    });
  }

  /// Requests a forgot-password OTP email.
  /// Always returns 200 (server never reveals whether email exists).
  Future<ApiResponse?> forgotPasswordRequest(String email) async {
    return _apiClient.postData(AppConstants.mobileForgotPassword, {
      'email': email,
    });
  }

  /// Verifies the forgot-password OTP; returns {reset_token}.
  Future<ApiResponse?> verifyForgotPasswordOtpMobile({
    required String otpToken,
    required String otpCode,
  }) async {
    return _apiClient.postData(AppConstants.mobileForgotPasswordVerifyOtp, {
      'otp_token': otpToken,
      'otp_code': otpCode,
    });
  }

  /// Resets the user password using the one-time reset_token.
  Future<ApiResponse?> resetPasswordWithToken({
    required String resetToken,
    required String password,
    required String passwordConfirm,
  }) async {
    return _apiClient.postData(AppConstants.mobileResetPassword, {
      'reset_token': resetToken,
      'password': password,
      'password_confirm': passwordConfirm,
    });
  }

  // ---------------------------------------------------------------------------
  // Token helpers — OAuth2
  // ---------------------------------------------------------------------------

  Future<bool?> saveOauthRefreshToken(String token) async {
    return _prefs.setString(AppConstants.oauthRefreshToken, token);
  }

  String getOauthRefreshToken() {
    return _prefs.getString(AppConstants.oauthRefreshToken) ?? '';
  }

  Future<bool?> saveOtpSessionToken(String token) async {
    return _prefs.setString(AppConstants.otpSessionToken, token);
  }

  String getOtpSessionToken() {
    return _prefs.getString(AppConstants.otpSessionToken) ?? '';
  }

  Future<bool?> saveResetToken(String token) async {
    return _prefs.setString(AppConstants.resetToken, token);
  }

  String getResetToken() {
    return _prefs.getString(AppConstants.resetToken) ?? '';
  }

  Future<ApiResponse?> updatePassword(String uid, String password) async {
    return _apiClient
        .patchData(AppConstants.updateUser(uid), {'pass': password});
  }

  Future<ApiResponse?> resetPassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    final uid = _prefs.getString(AppConstants.uid);
    if (uid != null) {
      final body = {
        'pass': [
          {'existing': currentPassword, 'value': newPassword},
        ],
      };
      return _apiClient.patchData(AppConstants.resetPassword(uid), body);
    }
    return null;
  }

  Future<ApiResponse?> userDataDrupal(String email) async {
    return _apiClient.getData(AppConstants.userDataDrupal(email));
  }

  Future<ApiResponse?> checkUserState(String email) async {
    return _apiClient.getData(AppConstants.statusUser(email));
  }

  Future<ApiResponse?> resendEmailOTP(String email) async {
    return _apiClient.postData(AppConstants.resendEmailOTP, {'email': email});
  }

  Future<ApiResponse?> blockUser(String uid) async {
    return _apiClient.patchData(
      AppConstants.updateUser(uid),
      {'status': 0},
    );
  }

  // ---------------------------------------------------------------------------
  // UID Login — replaces Simple OAuth token flow
  // ---------------------------------------------------------------------------

  /// UID Login: email + password → {uid, access_token, email, name, ...}.
  /// After a successful call, save uid + access_token and call
  /// [_apiClient.updateHeader] so subsequent requests are authenticated.
  Future<ApiResponse?> uidLogin({
    required String email,
    required String password,
  }) async {
    return _apiClient.postData(AppConstants.mobileLogin, {
      'username': email,
      'password': password,
    });
  }

  /// Returns the authenticated user's profile from the backend.
  /// Requires a valid access token to be present in [_apiClient]'s headers.
  Future<ApiResponse?> getMobileProfile() async {
    return _apiClient.getData(AppConstants.mobileUserProfile);
  }

  /// Changes the authenticated user's password.
  Future<ApiResponse?> changeMobilePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirm,
  }) async {
    return _apiClient.postData(AppConstants.mobileChangePassword, {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirm': newPasswordConfirm,
    });
  }

  // ---- Token/Session Management ----

  Future<bool?> saveUserToken(String token) async {
    return _prefs.setString(AppConstants.token, token);
  }

  Future<bool?> saveUserUid(String uid) async {
    return _prefs.setString(AppConstants.uid, uid);
  }

  Future<bool?> saveUserCsrfToken(String token) async {
    return _prefs.setString(AppConstants.csrfToken, token);
  }

  Future<bool?> saveLogoutToken(String token) async {
    return _prefs.setString(AppConstants.logoutToken, token);
  }

  Future<bool?> saveCookie(String cookie) async {
    return _prefs.setString(AppConstants.cookie, cookie);
  }

  String getUserToken() {
    return _prefs.getString(AppConstants.token) ?? '';
  }

  String getUserUid() {
    return _prefs.getString(AppConstants.uid) ?? '';
  }

  bool isLoggedIn() {
    return _prefs.containsKey(AppConstants.token);
  }

  Future<void> clearSharedData() async {
    // Get the current user UID before clearing it
    final uid = _prefs.getString(AppConstants.uid);

    // Remove auth-related data
    await _prefs.remove(AppConstants.token);
    await _prefs.remove(AppConstants.csrfToken);
    await _prefs.remove(AppConstants.uid);
    await _prefs.remove(AppConstants.cookie);
    await _prefs.remove(AppConstants.logoutToken);
    await _prefs.remove(AppConstants.oauthRefreshToken);
    await _prefs.remove(AppConstants.otpSessionToken);
    await _prefs.remove(AppConstants.resetToken);

    // Clear per-user theme preference
    if (uid != null && uid.isNotEmpty) {
      final themeKey = '${AppConstants.theme}_$uid';
      await _prefs.remove(themeKey);
    }
  }

  Future<void> saveUserNumberAndPassword(String number, String password) async {
    await _prefs.setString(AppConstants.userNumber, number);
    await _prefs.setString(AppConstants.userPassword, password);
  }

  String getUserNumber() {
    return _prefs.getString(AppConstants.userNumber) ?? '';
  }

  String getUserPassword() {
    return _prefs.getString(AppConstants.userPassword) ?? '';
  }

  Future<bool> clearUserNumberAndPassword() async {
    await _prefs.remove(AppConstants.userNumber);
    await _prefs.remove(AppConstants.userPassword);
    return true;
  }
}
