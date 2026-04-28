import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/coupon/data/models/coupon_list.dart';

class CouponRepository {
  const CouponRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<CouponList?> getCouponList() async {
    final response = await _apiClient.getData(AppConstants.couponList);
    if (response.statusCode == 200 &&
        response.body != null &&
        response.body['success'] == true) {
      return CouponList.fromJson(response.body);
    }
    return null;
  }
}
