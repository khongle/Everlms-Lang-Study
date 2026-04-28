import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class UserRepository {
  UserRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  String? get _uid => _prefs.getString(AppConstants.uid);

  Future<ApiResponse?> getUser(String uid) async {
    return _apiClient.getData(AppConstants.user(uid));
  }

  Future<ApiResponse?> getProfile(String uid) async {
    return _apiClient.getData(AppConstants.updateUser(uid));
  }

  Future<ApiResponse?> updateProfile({
    required String uid,
    required String username,
    required String firstName,
    required String lastName,
    String? phone,
    String? gender,
    String? dateOfBirth,
    String? address,
    String? fullName,
  }) async {
    final body = <String, dynamic>{
      'username': username,
      'first_name': firstName,
      'last_name': lastName,
    };
    if (phone != null) body['phone'] = phone;
    if (gender != null) body['gender'] = gender;
    if (dateOfBirth != null) body['date_of_birth'] = dateOfBirth;
    if (address != null) body['address'] = address;
    if (fullName != null) body['full_name'] = fullName;

    return _apiClient.patchData(AppConstants.updateUser(uid), body);
  }

  String getUserUid() {
    return _uid ?? '';
  }

  Future<ApiResponse?> deleteUser() async {
    final uid = _uid ?? '';
    return _apiClient.deleteData(AppConstants.deleteAccount(uid));
  }
}
