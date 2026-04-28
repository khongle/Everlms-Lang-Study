import 'package:everlms/feature/instructor_details/data/models/student.dart';

class InstructorStudentData {
  InstructorStudentData({this.students});

  factory InstructorStudentData.fromJson(Map<String, Object?> json) =>
      InstructorStudentData(
        students: (json['students'] as List<dynamic>?)
            ?.map((e) => Student.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Student>? students;

  Map<String, Object?> toJson() => {
        'students': students?.map((e) => e.toJson()).toList(),
      };
}
