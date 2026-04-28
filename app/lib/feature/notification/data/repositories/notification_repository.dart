import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/notification/data/models/notification_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class NotificationRepository {
  const NotificationRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  Future<List<CustomNotification>> getNotificationList() async {
    final uid = _prefs.getString(AppConstants.uid);
    final response =
        await _apiClient.getData(AppConstants.notification(uid ?? ''));
    if (response.statusCode == 200) {
      final data = response.body['data'];
      if (data is Map<String, dynamic>) {
        return data.values
            .map((e) => CustomNotification.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }

  /// Fetches the unread notification count from the new mobile API.
  Future<int> getUnreadCount() async {
    try {
      final response =
          await _apiClient.getData(AppConstants.mobileNotificationUnreadCount);
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return (body['count'] as num?)?.toInt() ?? 0;
      }
      return 0;
    } on DioException {
      return 0;
    }
  }

  void saveSeenNotificationCount(int count) {
    _prefs.setInt(AppConstants.notificationCount, count);
  }

  int getSeenNotificationCount() {
    return _prefs.getInt(AppConstants.notificationCount) ?? 0;
  }
}
