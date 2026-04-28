import 'package:everlms/feature/course_details/data/models/course_detail/data.dart';

class CourseDetail {
  CourseDetail({
    required this.success,
    required this.message,
    required this.data,
  });

  factory CourseDetail.fromJson(Map<String, Object?> json) => CourseDetail(
        success: json['success']! as bool,
        message: json['message']! as String,
        data: CourseDetailData.fromJson(json['data']! as Map<String, Object?>),
      );
  late bool success;
  late String message;
  late CourseDetailData data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data.toJson(),
      };
}
