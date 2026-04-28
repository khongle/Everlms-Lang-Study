import 'package:everlms/feature/course_details/data/models/user_review/review_data.dart';

class UserReview {
  UserReview({this.success, this.message, this.data});

  factory UserReview.fromJson(Map<String, Object?> json) => UserReview(
        success: json['success'] as bool?,
        message: json['message'] as String?,
        data: json['data'] == null
            ? null
            : ReviewData.fromJson(json['data']! as Map<String, Object?>),
      );
  bool? success;
  String? message;
  ReviewData? data;

  Map<String, Object?> toJson() => {
        'success': success,
        'message': message,
        'data': data?.toJson(),
      };
}
