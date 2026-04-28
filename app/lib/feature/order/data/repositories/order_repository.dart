import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';

class OrderRepository {
  OrderRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<ApiResponse?> getOrderHistory(int page, String type) async {
    return _apiClient.getData(
      '${AppConstants.orderHistory}?type=$type&page=$page',
    );
  }
}
