import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';

class MeetingRepository {
  MeetingRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<ApiResponse?> getMeetingList() async {
    return _apiClient.getData(AppConstants.meetingList);
  }
}
