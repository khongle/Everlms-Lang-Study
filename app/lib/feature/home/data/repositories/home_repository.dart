import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/home/data/models/course_search_result.dart';
import 'package:everlms/feature/home/data/models/dashboard_summary.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:shared_preferences/shared_preferences.dart';

class HomeRepository {
  const HomeRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  // ---------------------------------------------------------------------------
  // Mobile API — phase-01
  // ---------------------------------------------------------------------------

  /// GET /api/v1/dashboard — in-progress courses, counts.
  Future<DashboardSummary?> getDashboard() async {
    final response = await _apiClient.getData(AppConstants.mobileDashboard);
    if (response.statusCode == 200 && response.body is Map<String, dynamic>) {
      return DashboardSummary.fromJson(response.body as Map<String, dynamic>);
    }
    return null;
  }

  /// GET /api/courses/search — paginated course list with optional filters.
  Future<CourseSearchResult> searchCourses({
    String? search,
    List<int>? categoryIds,
    List<int>? levelIds,
    List<String>? priceTypes,
    String sort = 'newest',
    int page = 0,
    int limit = 20,
    bool featured = false,
  }) async {
    final query = <String, dynamic>{
      'page': page,
      'limit': limit,
      'sort': sort,
      if (search != null && search.isNotEmpty) 'search': search,
      if (featured) 'featured': '1',
    };
    if (categoryIds != null && categoryIds.isNotEmpty) {
      query['category[]'] = categoryIds.map((e) => '$e').toList();
    }
    if (levelIds != null && levelIds.isNotEmpty) {
      query['level[]'] = levelIds.map((e) => '$e').toList();
    }
    if (priceTypes != null && priceTypes.isNotEmpty) {
      query['price[]'] = priceTypes;
    }

    final response = await _apiClient.getDataPublic(
      AppConstants.mobileCourseSearch,
      query: query,
    );
    if (response.statusCode == 200 && response.body is Map<String, dynamic>) {
      return CourseSearchResult.fromJson(response.body as Map<String, dynamic>);
    }
    return const CourseSearchResult();
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  String getUserUid() {
    return _prefs.getString(AppConstants.uid) ?? '';
  }

  // ---------------------------------------------------------------------------
  // Legacy methods — kept for backward compatibility
  // ---------------------------------------------------------------------------

  bool _isSuccessResponse(Map<String, dynamic> body) {
    final status = body['status']?.toString().toLowerCase();
    return body['success'] == true || status == 'success' || status == '200';
  }

  List<Course> _parseCourseCollection(dynamic rawData) {
    Iterable<dynamic> items;
    if (rawData is List) {
      items = rawData;
    } else if (rawData is Map<String, dynamic>) {
      items = rawData.values;
    } else {
      return const [];
    }

    return items
        .whereType<Map<String, dynamic>>()
        .map(_parseCourse)
        .where(
          (course) => course.id != null || (course.title?.isNotEmpty ?? false),
        )
        .toList();
  }

  Course _parseCourse(Map<String, dynamic> json) {
    if (_isCourseDetailPayload(json)) {
      return Course.fromJsonCourseDetail(json);
    }
    return Course.fromJson(json);
  }

  bool _isCourseDetailPayload(Map<String, dynamic> json) {
    return json['product_id'] is List ||
        json['field_free'] is List ||
        json['field_featured_course'] is List ||
        json['field_bestseller'] is List ||
        json['title'] is List;
  }

  Future<ApiResponse?> getHomeData() async {
    return _apiClient.getData(AppConstants.homeScreen);
  }

  Future<ApiResponse?> getLatestCourse(int page) async {
    const currency = '';
    return _apiClient
        .getData('${AppConstants.latestCourse}?page=$page&currency=$currency');
  }

  Future<List<Course>> getCourseNotPurchase(String uid) async {
    final response =
        await _apiClient.getData(AppConstants.lmsCoursesNotAccess(uid));
    if (response.statusCode == 200) {
      final body = response.body;
      final isSuccess = _isSuccessResponse(body as Map<String, dynamic>);
      if (isSuccess) {
        final data = body['data'] ?? body['courses'];
        return _parseCourseCollection(data);
      }
    }
    return [];
  }

  Future<List<Course>> getCourseAccess(String uid) async {
    final response =
        await _apiClient.getData(AppConstants.lmsCoursesAccess(uid));
    if (response.statusCode == 200) {
      final body = response.body;
      final isSuccess = _isSuccessResponse(body as Map<String, dynamic>);
      if (isSuccess) {
        final data = body['data'] ?? body['courses'];
        return _parseCourseCollection(data);
      }
    }
    return [];
  }
}
