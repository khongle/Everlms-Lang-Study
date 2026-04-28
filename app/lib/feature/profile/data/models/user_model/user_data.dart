class UserData {
  UserData({
    this.name,
    this.firstName,
    this.lastName,
    this.mail,
    this.gender,
    this.phone,
    this.dateOfBirth,
    this.address,
    this.image,
    this.fullName,
    this.roles,
    this.uid,
    this.uuid,
  });

  factory UserData.fromJson(Map<String, dynamic> json) => UserData(
        name: json['name'] != null && (json['name'] as List).isNotEmpty
            ? json['name'][0]['value'] as String?
            : null,
        firstName: json['field_first_name'] != null &&
                (json['field_first_name'] as List).isNotEmpty
            ? json['field_first_name'][0]['value']
            : null,
        lastName: json['field_last_name'] != null &&
                (json['field_last_name'] as List).isNotEmpty
            ? json['field_last_name'][0]['value']
            : null,
        fullName: json['field_full_name'] != null &&
                (json['field_full_name'] as List).isNotEmpty
            ? json['field_full_name'][0]['value']
            : null,
        mail: json['mail'] != null && (json['mail'] as List).isNotEmpty
            ? json['mail'][0]['value'] as String?
            : null,
        gender: json['field_gender'] != null &&
                (json['field_gender'] as List).isNotEmpty
            ? json['field_gender'][0]['value']
            : null,
        phone: json['field_phone_number'] != null &&
                (json['field_phone_number'] as List).isNotEmpty
            ? json['field_phone_number'][0]['value']
            : null,
        dateOfBirth: json['field_date_of_birth'] != null &&
                (json['field_date_of_birth'] as List).isNotEmpty
            ? json['field_date_of_birth'][0]['value']
            : null,
        address: json['field_address'] != null &&
                (json['field_address'] as List).isNotEmpty
            ? '${json['field_address'][0]['address_line1'] ?? ''} ${json['field_address'][0]['locality'] ?? ''}'
                .trim()
            : null,
        image: json['user_picture'] != null &&
                (json['user_picture'] as List).isNotEmpty
            ? json['user_picture'][0]['picture_url']
            : null,
        roles: json['roles'] != null && (json['roles'] as List).isNotEmpty
            ? (json['roles'] as List).last['target_id']
            : null,
        uid: json['uid'] != null && (json['uid'] as List).isNotEmpty
            ? json['uid'][0]['value'].toString()
            : null,
        uuid: json['uuid'] != null && (json['uuid'] as List).isNotEmpty
            ? json['uuid'][0]['value'].toString()
            : null,
      );
  String? name;
  String? firstName;
  String? lastName;
  String? mail;
  String? gender;
  String? phone;
  String? dateOfBirth;
  String? address;
  String? image;
  String? fullName;
  String? roles;
  String? uid;
  String? uuid;

  Map<String, dynamic> toJson() => {
        'name': name,
        'field_first_name': firstName,
        'field_last_name': lastName,
        'mail': mail,
        'field_gender': gender,
        // 'date_of_birth': dateOfBirth,
        // 'address': address,
        'user_picture': image,
        'roles': roles,
        'uid': uid,
        'uuid': uuid,
      };
}
