import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/dashboard/data/models/dashboard_dto.dart';

class DashboardRepository {
  const DashboardRepository(this._apiClient);

  final ApiClient _apiClient;

  /// Fetches the authenticated user's dashboard summary.
  Future<DashboardDto> getDashboard() async {
    try {
      final response = await _apiClient.getData(AppConstants.mobileDashboard);
      if (response.statusCode == 200) {
        final body = response.body as Map<String, dynamic>;
        return DashboardDto.fromJson(body);
      }
      throw ApiException(
        message: 'Failed to load dashboard',
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
