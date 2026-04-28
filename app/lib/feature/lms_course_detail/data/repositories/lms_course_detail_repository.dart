import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';

class LmsCourseDetailRepository {
  const LmsCourseDetailRepository(this._apiClient);

  final ApiClient _apiClient;

  /// Fetches full course detail including curriculum and enrollment status.
  Future<CourseDetailDto> getCourseDetail(int courseId) async {
    try {
      final response = await _apiClient
          .getData(AppConstants.mobileCourseDetail(courseId.toString()));
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return CourseDetailDto.fromJson(body);
      }
      throw ApiException(
        message: 'Failed to load course detail',
        statusCode: response.statusCode,
      );
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }

  /// Enrolls the authenticated user in a free course.
  Future<void> enrollFree(int courseId) async {
    try {
      final response = await _apiClient
          .postData(AppConstants.mobileEnrollFree(courseId.toString()), {});
      if (response.statusCode != 200 && response.statusCode != 201) {
        throw ApiException(
          message: 'Enrollment failed',
          statusCode: response.statusCode,
        );
      }
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }

  /// Toggles wishlist status for a course. Returns new wishlisted state.
  Future<bool> toggleWishlist(int courseId) async {
    try {
      final response = await _apiClient.postData(
        AppConstants.mobileWishlistToggle,
        {'course_id': courseId},
      );
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return body['wishlisted'] as bool? ?? false;
      }
      throw ApiException(
        message: 'Wishlist toggle failed',
        statusCode: response.statusCode,
      );
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }

  /// Returns whether the course is in the user's wishlist.
  Future<bool> getWishlistStatus(int courseId) async {
    try {
      final response = await _apiClient
          .getData('${AppConstants.mobileWishlistStatus}?course_id=$courseId');
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return body['wishlisted'] as bool? ?? false;
      }
      return false;
    } on DioException catch (_) {
      return false;
    }
  }
}
