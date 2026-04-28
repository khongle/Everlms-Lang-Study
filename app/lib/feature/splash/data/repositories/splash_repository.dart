import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SplashRepository {
  const SplashRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  Future<ApiResponse> getConfigData() async {
    final response = await _apiClient.getData(AppConstants.getBasicPage);
    _apiClient.refreshAccessToken();
    return response;
  }

  Future<bool> initSharedData() async {
    if (!_prefs.containsKey(AppConstants.theme) || _prefs.get(AppConstants.theme) is bool) {
      await _prefs.remove(AppConstants.theme);
    }
    if (!_prefs.containsKey(AppConstants.countryCode)) {
      _prefs.setString(
        AppConstants.countryCode,
        AppConstants.languages[0].countryCode!,
      );
    }
    if (!_prefs.containsKey(AppConstants.languageCode)) {
      _prefs.setString(
        AppConstants.languageCode,
        AppConstants.languages[0].languageCode!,
      );
    }
    return true;
  }

  Future<bool> setSplashSeen(bool value) async {
    return _prefs.setBool(AppConstants.isOnBoardScreen, value);
  }

  bool isSplashSeen() {
    return _prefs.getBool(AppConstants.isOnBoardScreen) ?? false;
  }

  Future<ApiResponse> subscribeEmail(String email) async {
    return _apiClient.postData(AppConstants.subscriptionUrl, {'email': email});
  }
}
