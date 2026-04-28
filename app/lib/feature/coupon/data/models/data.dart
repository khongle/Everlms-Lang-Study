import 'package:everlms/feature/coupon/data/models/coupon.dart';

class Data {
  Data({this.coupons});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        coupons: (json['coupons'] as List<dynamic>?)
            ?.map((e) => Coupon.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Coupon>? coupons;

  Map<String, Object?> toJson() => {
        'coupons': coupons?.map((e) => e.toJson()).toList(),
      };
}
