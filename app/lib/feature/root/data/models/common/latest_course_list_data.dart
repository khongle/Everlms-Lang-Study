import 'package:everlms/feature/root/data/models/common/course.dart';

class Data {
  Data({this.courses});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        courses: (json['courses'] as List<dynamic>?)
            ?.map((e) => Course.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Course>? courses;

  Map<String, Object?> toJson() => {
        'courses': courses?.map((e) => e.toJson()).toList(),
      };
}
