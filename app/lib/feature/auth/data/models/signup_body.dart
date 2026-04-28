class SignUpBody {
  SignUpBody({
    this.username = '',
    this.email = '',
    this.pass = '',
    this.language = 'en',
    this.firstName = '',
    this.lastName = '',
    this.phone = '',
  });

  SignUpBody.fromJson(Map<String, dynamic> json)
      : username = json['name'] ?? '',
        email = json['mail'] ?? '',
        pass = json['pass'] ?? '',
        language = json['field_langcode_app'] ?? 'en',
        firstName = json['field_first_name'] ?? '',
        lastName = json['field_last_name'] ?? '',
        phone = json['field_phone_number'] ?? '';

  String username;
  String email;
  String pass;
  String language;
  String firstName;
  String lastName;
  String phone;

  Map<String, dynamic> toJson() {
    return {
      'name': {'value': username},
      'mail': {'value': email},
      'pass': {'value': pass},
      'field_first_name': {'value': firstName},
      'field_last_name': {'value': lastName},
      'field_phone_number': {'value': phone},
      'field_langcode_app': {'value': language},
    };
  }

  Map<String, dynamic> toJsonForPhone() {
    return {
      'name': {'value': username},
      'mail': {'value': email},
      'pass': {'value': pass},
      'field_phone_number': {'value': phone},
      'field_langcode_app': {'value': language},
    };
  }
}
