import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CartRepository {
  CartRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  String? get _uid => _prefs.getString(AppConstants.uid);

  Future<ApiResponse?> getCartList() async {
    return _apiClient.getData(AppConstants.getCart(_uid ?? ''));
  }

  Future<ApiResponse?> addToCart(int id) async {
    return _apiClient.postData(AppConstants.addToCart, {
      'course_id': id,
      'quantity': 1,
      'user_id': _uid,
    });
  }

  Future<ApiResponse?> removeFromCart(int orderItemId) async {
    return _apiClient.postData(AppConstants.lmsRemoveItemCart, {
      'order_item_id': orderItemId,
      'user_id': _uid,
    });
  }

  Future<ApiResponse?> emptyCart() async {
    return _apiClient.postData(AppConstants.emptyCart, {
      'user_id': _uid,
    });
  }

  Future<ApiResponse?> updateCompleteCart(String typePayment) async {
    return _apiClient.postData(AppConstants.updateCompleteCart, {
      'type-payment': typePayment,
      'user_id': _uid,
    });
  }

  Future<ApiResponse?> applyCoupon({
    required String code,
    required String type,
    required String id,
  }) async {
    return _apiClient.postData(AppConstants.applyCoupon, {
      'type': type,
      'id': id,
      'code': code,
    });
  }
}
