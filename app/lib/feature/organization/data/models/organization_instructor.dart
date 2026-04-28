import 'package:everlms/feature/organization/data/models/organization_instructor_data.dart';

class OrganizationInstructor {
  OrganizationInstructor({this.success, this.message, this.data});

  factory OrganizationInstructor.fromJson(Map<String, Object?> json) {
    return OrganizationInstructor(
      success: json['success'] as bool?,
      message: json['message'] as String?,
      data: json['data'] == null
          ? null
          : Data.fromJson(json['data']! as Map<String, Object?>),
    );
  }
  bool? success;
  String? message;
  Data? data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data?.toJson(),
      };
}
