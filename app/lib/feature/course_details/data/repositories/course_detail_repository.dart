import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CourseDetailRepository {
  const CourseDetailRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  Future<ApiResponse?> getReviews(int id, int pageNumber) async {
    return _apiClient.getData(
      '${AppConstants.reviews}?id=$id&type=course&page=$pageNumber',
    );
  }

  Future<ApiResponse?> getListChapter(String courseId) async {
    return _apiClient.getData(AppConstants.listChapter(courseId));
  }

  Future<ApiResponse?> addCourse(String courseId, String userId) async {
    return _apiClient.postData(
      AppConstants.addCourse,
      {'course_id': courseId, 'uid': userId},
    );
  }

  Future<ApiResponse?> postReview({
    required int id,
    required String review,
    required double rating,
  }) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.postData(
      AppConstants.writeReviews,
      {
        'entity_id': '$id',
        'entity_type': 'course',
        'comment_body': review,
        'rating': '$rating',
        'uid': uid,
      },
    );
  }

  Future<ApiResponse?> lmsCheckCoursePayment({
    required String productId,
  }) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.getData(
      '${AppConstants.lmsCheckCoursePayment}?uid=$uid&product_id=$productId',
    );
  }

  Future<ApiResponse?> lmsCheckPermission({
    String? courseId,
    String? lessonId,
    String? quizId,
  }) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    final body = <String, dynamic>{'uid': uid};
    if (courseId != null) body['course_id'] = courseId;
    if (lessonId != null) body['lesson_id'] = lessonId;
    if (quizId != null) body['quiz_id'] = quizId;
    return _apiClient.postData(AppConstants.lmsCheckPermission, body);
  }

  Future<ApiResponse?> lmsUserCourseCheck(String courseId) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.postData(
      AppConstants.lmsUserCourseCheck,
      {'course_id': courseId, 'uid': uid},
    );
  }

  Future<ApiResponse?> startCourse(String productId) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.postData(
      AppConstants.lmsStartCourse,
      {'product_id': productId, 'uid': uid},
    );
  }

  Future<ApiResponse?> getUserChapter(String courseId) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.getData(
      '${AppConstants.lmsUserChapterProgress}?uid=$uid&course_id=$courseId',
    );
  }

  Future<ApiResponse?> lmsUpdateStatusLesson({
    required String lessonId,
  }) async {
    final uid = _prefs.getString(AppConstants.uid) ?? '';
    return _apiClient.postData(
      AppConstants.lmsUpdateStatusLesson,
      {'lesson_id': lessonId, 'uid': uid},
    );
  }
}
