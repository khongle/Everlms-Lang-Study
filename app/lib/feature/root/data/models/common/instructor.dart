class Instructor {
  Instructor({
    this.id,
    this.name,
    this.rating,
    this.birth,
    this.gender,
    this.image,
    this.expertise,
    this.biography,
  });

  factory Instructor.fromJson(Map<String, dynamic> json) => Instructor(
        id: (json['uid'] != null && (json['uid'] as List).isNotEmpty)
            ? int.parse(json['uid'][0]['value'])
            : null,
        name: (json['field_full_name'] != null &&
                (json['field_full_name'] as List).isNotEmpty)
            ? json['field_full_name'][0]['value']
            : null,
        rating: (json['field_rating'] != null &&
                (json['field_rating'] as List).isNotEmpty)
            ? json['field_rating'][0]['value']
            : null,
        birth: (json['field_date_of_birth'] != null &&
                (json['field_date_of_birth'] as List).isNotEmpty)
            ? json['field_date_of_birth'][0]['value']
            : null,
        gender: (json['field_gender'] != null &&
                (json['field_gender'] as List).isNotEmpty)
            ? json['field_gender'][0]['value']
            : null,
        image: (json['user_picture'] != null &&
                (json['user_picture'] as List).isNotEmpty)
            ? json['user_picture'][0]['picture_url']
            : null,
        expertise: (json['field_expertise'] != null &&
                (json['field_expertise'] as List).isNotEmpty)
            ? json['field_expertise'][0]['value']
            : null,
        biography: (json['field_biography'] != null &&
                (json['field_biography'] as List).isNotEmpty)
            ? json['field_biography'][0]['value']
            : null,
      );
  int? id;
  String? name;
  int? rating;
  String? birth;
  String? gender;
  String? image;
  String? expertise;
  String? biography;

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'rating': rating,
        'birth': birth,
        'gender': gender,
        'image': image,
        'expertise': expertise,
        'biography': biography,
      };
}
