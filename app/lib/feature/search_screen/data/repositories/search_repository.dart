import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SearchRepository {
  const SearchRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

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

  bool _isSuccessResponse(Map<String, dynamic> body) {
    final status = body['status']?.toString().toLowerCase();
    return body['success'] == true || status == 'success' || status == '200';
  }

  Future<List<Course>> searchCourses(String query) async {
    final response = await _apiClient.getData(
      '${AppConstants.searchCourses}?search=${Uri.encodeComponent(query)}',
    );
    if (response.statusCode == 200) {
      final body = response.body;
      final isSuccess = _isSuccessResponse(body);
      if (isSuccess) {
        final courses = body['courses'] ?? body['data'];
        if (courses is List) {
          return courses
              .whereType<Map>()
              .map((e) => _parseCourse(Map<String, dynamic>.from(e)))
              .where(
                (course) =>
                    course.id != null || (course.title?.isNotEmpty ?? false),
              )
              .toList();
        }
      }
    }
    return [];
  }

  List<String> getSearchHistory() {
    return _prefs.getStringList(AppConstants.searchHistory) ?? [];
  }

  Future<bool> saveSearchHistory(List<String> history) {
    return _prefs.setStringList(AppConstants.searchHistory, history);
  }

  Future<bool> clearSearchHistory() {
    return _prefs.setStringList(AppConstants.searchHistory, []);
  }
}
