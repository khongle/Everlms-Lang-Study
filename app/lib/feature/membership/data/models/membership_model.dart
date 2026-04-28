class MembershipModel {
  MembershipModel({
    this.title,
    this.fieldSubtitle,
    this.priceNumber,
    this.listPriceNumber,
    this.body,
    this.productId,
  });

  MembershipModel.fromJson(Map<String, dynamic> json) {
    title = json['title'];
    fieldSubtitle = json['field_subtitle'];
    priceNumber = json['price__number'];
    listPriceNumber = json['list_price__number'];
    body = json['body'];
    productId = json['product_id'];
  }
  String? title;
  String? fieldSubtitle;
  String? priceNumber;
  String? listPriceNumber;
  String? body;
  String? productId;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['title'] = title;
    data['field_subtitle'] = fieldSubtitle;
    data['price__number'] = priceNumber;
    data['list_price__number'] = listPriceNumber;
    data['body'] = body;
    data['product_id'] = productId;
    return data;
  }
}
