class CommenterModel {
  CommenterModel({
    required this.uid,
    required this.name,
    required this.firstName,
    required this.lastName,
    required this.image,
  });

  factory CommenterModel.fromJson(Map<String, dynamic> json) {
    return CommenterModel(
      uid: json['uid'],
      name: json['name'],
      firstName: json['field_first_name'],
      lastName: json['field_last_name'],
      image: json['user_picture'] != null
          ? (json['user_picture'] is List) &&
                  (json['user_picture'] as List).isNotEmpty
              ? json['user_picture'][0]['picture_url']
              : ''
          : '',
    );
  }
  String? uid;
  String? name;
  String? firstName;
  String? lastName;
  String? image;

  Map<String, dynamic> json() => {
        'uid': uid,
        'name': name,
        'field_first_name': firstName,
        'field_last_name': lastName,
        'user_picture': image,
      };
}
