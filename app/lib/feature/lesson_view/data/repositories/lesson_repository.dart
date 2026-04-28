import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/lesson_view/data/models/lesson_detail_dto.dart';

class LessonRepository {
  const LessonRepository(this._apiClient);

  final ApiClient _apiClient;

  Future<LessonDetailDto> getLessonDetail(int courseId, int lessonId) async {
    try {
      final response = await _apiClient.getData(
        AppConstants.mobileLessonDetail(
            courseId.toString(), lessonId.toString(),),
      );
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return LessonDetailDto.fromJson(body, courseId);
      }
      throw ApiException(
        message: 'Failed to load lesson',
        statusCode: response.statusCode,
      );
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }

  Future<void> markComplete(int courseId, int lessonId) async {
    try {
      final response = await _apiClient.postData(
        AppConstants.mobileMarkComplete,
        {'course_id': courseId, 'lesson_id': lessonId},
      );
      if (response.statusCode != 200 && response.statusCode != 201) {
        throw ApiException(
          message: 'Failed to mark lesson complete',
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

  Future<LessonNoteDto?> getNote(int lessonId) async {
    try {
      final response = await _apiClient
          .getData('${AppConstants.mobileLessonNoteGet}?lesson_id=$lessonId');
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>?;
        if (body == null || body.isEmpty) return null;
        return LessonNoteDto.fromJson(body);
      }
      return null;
    } on DioException {
      return null;
    }
  }

  Future<void> saveNote(
      int lessonId, String content, int timestampSeconds,) async {
    try {
      await _apiClient.postData(AppConstants.mobileLessonNoteSave, {
        'lesson_id': lessonId,
        'content': content,
        'timestamp_seconds': timestampSeconds,
      });
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }
}
