import 'package:everlms/feature/order/data/models/order_history.dart';

class Data {
  Data({this.orderHistories});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        orderHistories: (json['order_histories'] as List<dynamic>?)
            ?.map((e) => OrderHistory.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<OrderHistory>? orderHistories;

  Map<String, Object?> toJson() => {
        'order_histories': orderHistories?.map((e) => e.toJson()).toList(),
      };
}
