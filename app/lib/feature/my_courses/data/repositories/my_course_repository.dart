import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';

class MyCourseRepository {
  const MyCourseRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<ApiResponse?> getMyCourseList(String uid) async {
    return _apiClient.getData(AppConstants.myCourse(uid));
  }

  Future<ApiResponse?> getMyCourseListMobile({String status = 'all'}) async {
    return _apiClient.getData(
      AppConstants.mobileEnrolledCourses,
      query: {'status': status},
    );
  }

  Future<ApiResponse?> getTeacherCourseList(String teacherUid) async {
    return _apiClient.getData(AppConstants.teacherCourse(teacherUid));
  }

  Future<ApiResponse?> getListChapter(String courseId) async {
    return _apiClient.getData(AppConstants.listChapter(courseId));
  }

  Future<ApiResponse?> createCommentLesson({
    required String lessonId,
    required String subject,
    required String content,
  }) async {
    return _apiClient.postData(
      AppConstants.lmsCreateComment,
      {
        'lesson_id': lessonId,
        'subject': subject,
        'comment_body': content,
      },
    );
  }
}
