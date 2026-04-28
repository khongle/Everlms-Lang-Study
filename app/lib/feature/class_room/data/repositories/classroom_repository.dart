import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';

class ClassroomRepository {
  const ClassroomRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<ApiResponse?> getClassroomData({required int courseID}) async {
    return _apiClient.getData('${AppConstants.courseSection}/$courseID');
  }

  Future<ApiResponse?> saveProgress({
    required int id,
    required String lessonID,
    required String sectionID,
    required String totalSpentTime,
    required String totalDuration,
  }) async {
    return _apiClient.postData(
      AppConstants.saveProgress,
      {
        'course_id': '$id',
        'lesson_id': lessonID,
        'section_id': sectionID,
        'total_spent_time': totalSpentTime,
        'total_duration': totalDuration,
      },
    );
  }
}
