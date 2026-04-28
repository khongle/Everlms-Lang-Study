class Calculations {
  Calculations({
    this.subTotal,
    this.discount,
    this.couponDiscount,
    this.tax,
    this.totalPayable,
    this.currencyCode,
  });

  factory Calculations.fromJson(Map<String, dynamic> json) => Calculations(
        subTotal: json['sub_total'] as String?,
        discount: json['discount'] as String?,
        couponDiscount: json['coupon_discount'] as String?,
        tax: json['tax'] as String?,
        totalPayable: json['total_payable'] as String?,
        currencyCode: json['currencyCode'] as String?,
      );
  factory Calculations.fromJsonDrupal(Map<String, Object?> json) =>
      Calculations(
        subTotal: json['sub_total'] as String?,
        discount: json['discount'] as String?,
        couponDiscount: json['coupon_discount'] as String?,
        tax: json['tax'] as String?,
        totalPayable: json['number'] as String?,
        currencyCode: json['currencyCode'] as String?,
      );
  String? subTotal;
  String? discount;
  String? couponDiscount;
  String? tax;
  String? totalPayable;
  String? currencyCode;

  Map<String, Object?> toJson() => {
        'sub_total': subTotal,
        'discount': discount,
        'coupon_discount': couponDiscount,
        'tax': tax,
        'total_payable': totalPayable,
      };
}
