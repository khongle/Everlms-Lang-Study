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

  /// Parses the legacy Drupal JSON API response from `lms-user/{uid}`.
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

  /// Parses the phase-01 response from `GET /api/v1/user/me`.
  factory UserData.fromMobileJson(Map<String, dynamic> json) {
    final rawRoles = json['roles'];
    String? role;
    if (rawRoles is List && rawRoles.isNotEmpty) {
      role = rawRoles.last?.toString();
    }
    return UserData(
      uid: json['id']?.toString(),
      uuid: json['uuid'] as String?,
      name: json['name'] as String?,
      mail: json['email'] as String?,
      image: json['avatar_url'] as String?,
      roles: role,
      firstName: json['first_name'] as String?,
      lastName: json['last_name'] as String?,
      fullName: json['name'] as String?,
      phone: json['phone'] as String?,
      gender: json['gender'] as String?,
      dateOfBirth: json['date_of_birth'] as String?,
    );
  }

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

  String get displayName {
    final candidates = [
      fullName,
      [firstName, lastName].whereType<String>().join(' ').trim(),
      name,
      if (mail != null && mail!.contains('@')) mail!.split('@').first else mail,
    ];
    for (final candidate in candidates) {
      if (candidate != null && candidate.trim().isNotEmpty) {
        return candidate.trim();
      }
    }
    return '';
  }

  String get firstDisplayName {
    final value = displayName;
    if (value.isEmpty) return '';
    return value.split(' ').first;
  }

  String get displayEmail => (mail ?? '').trim();

  String get displayAvatarUrl => (image ?? '').trim();

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
