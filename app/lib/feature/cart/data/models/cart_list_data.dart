import 'package:everlms/feature/cart/data/models/calculations.dart';
import 'package:everlms/feature/cart/data/models/cart.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';

class CartListData {
  CartListData({
    this.carts,
    this.calculations,
    this.listCourse,
    this.variation,
  });

  factory CartListData.fromJson(Map<String, dynamic> json) => CartListData(
        carts: (json['order_items'] as List<dynamic>?)
            ?.map((e) => Cart.fromJsonDrupal(e as Map<String, Object?>))
            .toList(),
        variation: json['field_products'] != null
            ? List<Variation>.from(
                (json['field_products'] as Map<String, dynamic>)
                    .values
                    .map((e) {
                  return Variation.fromJson(e['variations']);
                }),
              )
            : null,
        calculations: (json['total_price'] != null &&
                (json['total_price'] as List).isNotEmpty)
            ? Calculations.fromJsonDrupal(json['total_price'][0])
            : null,
        listCourse: json['field_products'] != null
            ? List<Course>.from(
                (json['field_products'] as Map<String, dynamic>)
                    .values
                    .map((e) {
                  return Course.fromJsonCourseDetail(e);
                }),
              )
            : null,
      );
  List<Cart>? carts;
  List<Course>? listCourse;
  List<Variation>? variation;
  Calculations? calculations;

  Map<String, Object?> toJson() => {
        'carts': carts?.map((e) => e.toJson()).toList(),
        'calculations': calculations?.toJson(),
      };
}

class Variation {
  Variation({
    required this.listPrice,
    required this.price,
    required this.currencyCode,
  });

  factory Variation.fromJson(Map<String, dynamic> json) => Variation(
        listPrice: json['list_price'][0]['number'],
        price: json['price'][0]['number'],
        currencyCode: json['price'][0]['currency_code'],
      );
  String? listPrice;
  String? price;
  String? currencyCode;
}
