import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/profile/data/models/account/certificate_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CertificateRepository {
  const CertificateRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  Future<List<Certificate>> getCertificateList() async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    final response = await _apiClient.getData(AppConstants.userCertificates(uid));
    if (response.statusCode == 200 && response.body != null) {
      final data = response.body['data'];
      if (data is Map<String, dynamic>) {
        return data.values
            .map((e) => Certificate.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }
}
