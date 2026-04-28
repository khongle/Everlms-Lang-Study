import 'package:everlms/feature/course_details/data/models/user_review/review.dart';

class ReviewData {
  ReviewData({this.reviews});

  factory ReviewData.fromJson(Map<String, Object?> json) => ReviewData(
        reviews: (json['reviews'] as List<dynamic>?)
            ?.map((e) => Review.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Review>? reviews;

  Map<String, Object?> toJson() => {
        'reviews': reviews?.map((e) => e.toJson()).toList(),
      };
}
