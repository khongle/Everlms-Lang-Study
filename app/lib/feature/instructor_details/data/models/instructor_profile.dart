import 'package:everlms/feature/instructor_details/data/models/instructor_profile_data.dart';

class InstructorProfile {
  InstructorProfile({this.success, this.message, this.data});

  factory InstructorProfile.fromJson(Map<String, Object?> json) {
    return InstructorProfile(
      success: json['success'] as bool?,
      message: json['message'] as String?,
      data: json['data'] == null
          ? null
          : InstructorProfileData.fromJson(
              json['data']! as Map<String, Object?>,
            ),
    );
  }
  bool? success;
  String? message;
  InstructorProfileData? data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data?.toJson(),
      };
}
