import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:shared_preferences/shared_preferences.dart';

class HomeRepository {
  const HomeRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

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
        .where((course) => course.id != null || (course.title?.isNotEmpty ?? false))
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
    // TODO: currency should be passed as parameter once SplashProvider is wired
    const currency = '';
    return _apiClient
        .getData('${AppConstants.latestCourse}?page=$page&currency=$currency');
  }

  Future<List<Course>> getCourseNotPurchase(String uid) async {
    final response =
        await _apiClient.getData(AppConstants.lmsCoursesNotAccess(uid));
    if (response.statusCode == 200) {
      final body = response.body;
      final isSuccess = _isSuccessResponse(body);
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
      final isSuccess = _isSuccessResponse(body);
      if (isSuccess) {
        final data = body['data'] ?? body['courses'];
        return _parseCourseCollection(data);
      }
    }
    return [];
  }

  String getUserUid() {
    return _prefs.getString(AppConstants.uid) ?? '';
  }
}
