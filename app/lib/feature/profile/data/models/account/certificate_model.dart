class Certificate {
  Certificate({
    required this.id,
    required this.uid,
    required this.name,
    required this.body,
    required this.link,
  });

  factory Certificate.fromJson(Map<String, dynamic> json) {
    return Certificate(
      id: json['lms_user_certificate_id'],
      uid: json['uid'],
      name: json['name'],
      body: json['field_body'],
      link: json['field_link'],
    );
  }
  String? id;
  String? uid;
  String? name;
  String? body;
  String? link;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['lms_user_certificate_id'] = id;
    data['uid'] = uid;
    data['name'] = name;
    data['field_body'] = body;
    data['field_link'] = link;
    return data;
  }
}
