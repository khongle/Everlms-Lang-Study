import 'package:everlms/feature/instructor_details/data/models/instructor_course_list_data.dart';

class InstructorCourseList {
  InstructorCourseList({this.success, this.message, this.data});

  factory InstructorCourseList.fromJson(Map<String, Object?> json) {
    return InstructorCourseList(
      success: json['success'] as bool?,
      message: json['message'] as String?,
      data: json['data'] == null
          ? null
          : InstructorCourseListData.fromJson(
              json['data']! as Map<String, Object?>,
            ),
    );
  }
  bool? success;
  String? message;
  InstructorCourseListData? data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data?.toJson(),
      };
}
