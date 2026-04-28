import 'package:everlms/feature/root/data/models/common/my_course.dart';

class Data {
  Data({this.courses});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        courses: (json['courses'] as List<dynamic>?)
            ?.map((e) => MyCourse.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<MyCourse>? courses;

  Map<String, Object?> toJson() => {
        'courses': courses?.map((e) => e.toJson()).toList(),
      };
}
