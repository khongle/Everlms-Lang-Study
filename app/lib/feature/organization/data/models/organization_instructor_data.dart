import 'package:everlms/feature/root/data/models/common/instructor_old.dart';

class Data {
  Data({this.instructors});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        instructors: (json['instructors'] as List<dynamic>?)
            ?.map((e) => InstructorOld.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<InstructorOld>? instructors;

  Map<String, Object?> toJson() => {
        'instructors': instructors?.map((e) => e.toJson()).toList(),
      };
}
