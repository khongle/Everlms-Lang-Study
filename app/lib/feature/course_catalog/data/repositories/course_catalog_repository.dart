import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/course_catalog/data/models/course_catalog_dto.dart';

class CourseCatalogRepository {
  const CourseCatalogRepository(this._apiClient);

  final ApiClient _apiClient;

  /// Searches courses with filters and pagination.
  Future<CourseSearchResponseDto> searchCourses({
    String? search,
    List<int>? categoryIds,
    List<int>? levelIds,
    List<String>? priceFilters,
    String sort = 'newest',
    int page = 0,
    int limit = 20,
  }) async {
    try {
      final query = <String, dynamic>{
        'page': page,
        'limit': limit,
        'sort': sort,
        if (search != null && search.isNotEmpty) 'search': search,
      };
      if (categoryIds != null && categoryIds.isNotEmpty) {
        query['category[]'] = categoryIds.map((e) => '$e').toList();
      }
      if (levelIds != null && levelIds.isNotEmpty) {
        query['level[]'] = levelIds.map((e) => '$e').toList();
      }
      if (priceFilters != null && priceFilters.isNotEmpty) {
        query['price[]'] = priceFilters;
      }

      final response = await _apiClient.getDataPublic(
        AppConstants.mobileCourseSearch,
        query: query,
      );

      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return CourseSearchResponseDto.fromJson(body);
      }
      throw ApiException(
        message: 'Failed to load courses',
        statusCode: response.statusCode,
      );
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }

  /// Fetches all published course categories with course counts.
  Future<List<CategoryDto>> getCategories() async {
    try {
      final response = await _apiClient.getData(AppConstants.mobileCategories);
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        final rawItems = body['items'] as List? ?? [];
        return rawItems
            .whereType<Map<String, dynamic>>()
            .map(CategoryDto.fromJson)
            .toList();
      }
      throw ApiException(
        message: 'Failed to load categories',
        statusCode: response.statusCode,
      );
    } on DioException catch (e) {
      throw ApiException(
        message: e.message ?? 'Network error',
        statusCode: e.response?.statusCode,
      );
    }
  }
}
