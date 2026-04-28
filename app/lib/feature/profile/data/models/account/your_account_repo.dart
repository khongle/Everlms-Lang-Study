import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class YourAccountRepo {
  YourAccountRepo({required this.apiClient});
  final ApiClient apiClient;

  Future<ApiResponse?> getFaq() async {
    return apiClient.getData(AppConstants.getFaq);
  }

  Future<ApiResponse?> getNews() async {
    return apiClient.getData(AppConstants.getNews);
  }

  Future<ApiResponse?> getUserCertificates() async {
    final sharedPreferences = await SharedPreferences.getInstance();
    final uid = sharedPreferences.getString(AppConstants.uid) ?? '';
    return apiClient.getData(AppConstants.userCertificates(uid));
  }
}
