import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/main.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class StatusApi {
  StatusApi({this.method, this.isError});
  final String? method;
  final bool? isError;
}

enum SwitchDomainStep { idle, verifying, domainNotExist, failed, completed }

class SwitchDomainState {
  const SwitchDomainState({
    this.listResultApi = const [],
    this.domain,
    this.progress = 0,
    this.step = SwitchDomainStep.idle,
  });

  final List<StatusApi> listResultApi;
  final String? domain;
  final double progress;
  final SwitchDomainStep step;

  SwitchDomainState copyWith({
    List<StatusApi>? listResultApi,
    String? domain,
    double? progress,
    SwitchDomainStep? step,
  }) {
    return SwitchDomainState(
      listResultApi: listResultApi ?? this.listResultApi,
      domain: domain ?? this.domain,
      progress: progress ?? this.progress,
      step: step ?? this.step,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class SwitchDomainNotifier extends Notifier<SwitchDomainState> {
  ApiClient? _tempApiClient;
  String _logoutToken = '';
  String _uid = '';
  String _username = '';
  String _currentPassword = '';

  @override
  SwitchDomainState build() {
    return const SwitchDomainState();
  }

  void setDomain(String domain) {
    final prefs = ref.read(sharedPreferencesProvider);
    _tempApiClient = ApiClient(
      appBaseUrl: domain,
      sharedPreferences: prefs,
      auth: basicAuthWeb,
    );
    state = state.copyWith(domain: domain);
  }

  String? checkValidDomain(String domain) {
    if (domain == Config.baseUrl) {
      return 'Domain is same as current';
    }
    return null;
  }

  Future<SwitchDomainStep> verifyDomain() async {
    if (_tempApiClient == null || state.domain == null) {
      return SwitchDomainStep.domainNotExist;
    }

    state = state.copyWith(
      step: SwitchDomainStep.verifying,
      progress: 0,
      listResultApi: [],
    );

    try {
      // Step 1: Register
      await _authRegister();
      state = state.copyWith(progress: 0.2);

      // Step 2: Login
      await _authLogin();
      state = state.copyWith(progress: 0.4);

      // Step 3: List courses
      await _listCourseNotAccess();
      state = state.copyWith(progress: 0.6);

      // Step 4: Logout
      await _authLogout();
      state = state.copyWith(progress: 0.8);

      // Step 5: Delete test user
      await _authDeleteUser();
      state = state.copyWith(progress: 1);

      final hasError = state.listResultApi.any((s) => s.isError == true);
      final step =
          hasError ? SwitchDomainStep.failed : SwitchDomainStep.completed;
      state = state.copyWith(step: step);
      return step;
    } on Exception catch (_) {
      state = state.copyWith(step: SwitchDomainStep.failed);
      return SwitchDomainStep.failed;
    }
  }

  void _checkAndRecordStatus(String method, ApiResponse? response) {
    final isError = response == null || response.statusCode != 200;
    final updated = [
      ...state.listResultApi,
      StatusApi(method: method, isError: isError),
    ];
    state = state.copyWith(listResultApi: updated);
  }

  Future<void> _authRegister() async {
    _username = 'test_${DateTime.now().millisecondsSinceEpoch}';
    _currentPassword = 'Test@12345';
    final body = {
      'name': _username,
      'mail': '$_username@test.com',
      'pass': _currentPassword,
    };
    final res = await _tempApiClient!.postData(AppConstants.register, body);
    _checkAndRecordStatus('Register', res);
  }

  Future<void> _authLogin() async {
    final body = {
      'name': _username,
      'pass': _currentPassword,
    };
    final res = await _tempApiClient!.postData(AppConstants.loginUrl, body);
    if (res.statusCode == 200) {
      final data = res.body;
      _logoutToken = data['logout_token']?.toString() ?? '';
      _uid = data['current_user']?['uid']?.toString() ?? '';
    }
    _checkAndRecordStatus('Login', res);
  }

  Future<void> _listCourseNotAccess() async {
    final res =
        await _tempApiClient!.getData(AppConstants.lmsCoursesNotAccess(_uid));
    _checkAndRecordStatus('List Courses', res);
  }

  Future<void> _authLogout() async {
    final res =
        await _tempApiClient!.getData(AppConstants.logout(_logoutToken));
    _checkAndRecordStatus('Logout', res);
  }

  Future<void> _authDeleteUser() async {
    final res =
        await _tempApiClient!.deleteData(AppConstants.deleteAccount(_uid));
    _checkAndRecordStatus('Delete User', res);
  }

  Future<void> restartApp(String newBaseUrl) async {
    final prefs = ref.read(sharedPreferencesProvider);
    await prefs.clear();
    await prefs.setString(AppConstants.baseUrl, newBaseUrl);
  }

  void reset() {
    _logoutToken = '';
    _uid = '';
    _username = '';
    _currentPassword = '';
    state = const SwitchDomainState();
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final switchDomainProvider =
    NotifierProvider<SwitchDomainNotifier, SwitchDomainState>(
  SwitchDomainNotifier.new,
);
