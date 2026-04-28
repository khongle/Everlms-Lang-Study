import 'package:everlms/feature/class_room/data/models/classroom_data.dart';

class Classroom {
  Classroom({this.success, this.message, this.data});

  factory Classroom.fromJson(Map<String, Object?> json) => Classroom(
        success: json['success'] as bool?,
        message: json['message'] as String?,
        data: json['data'] == null
            ? null
            : Data.fromJson(json['data']! as Map<String, Object?>),
      );
  bool? success;
  String? message;
  Data? data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data?.toJson(),
      };
}
