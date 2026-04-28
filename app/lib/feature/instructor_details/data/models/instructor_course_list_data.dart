import 'package:everlms/feature/root/data/models/common/course.dart';

class InstructorCourseListData {
  InstructorCourseListData({this.courses});

  factory InstructorCourseListData.fromJson(Map<String, Object?> json) =>
      InstructorCourseListData(
        courses: (json['courses'] as List<dynamic>?)
            ?.map((e) => Course.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Course>? courses;

  Map<String, Object?> toJson() => {
        'courses': courses?.map((e) => e.toJson()).toList(),
      };
}
