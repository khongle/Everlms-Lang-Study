import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';

class MyCourseOffline {
  MyCourseOffline({
    this.id,
    this.thumbnail,
    this.title,
    this.url,
    this.course,
  });

  factory MyCourseOffline.fromJson(Map<String, dynamic> json) =>
      MyCourseOffline(
        id: json['id'] as String?,
        // thumbnail: json['thumbnail'] as String?,
        thumbnail:
            json['field_image'] != null ? json['field_image'][0]['url'] : null,
        title: json['title'] as String?,
        url: json['url'] as String?,
        course: json['course'] as LMSMyCourseModel?,
      );
  String? id;
  String? thumbnail;
  String? title;
  String? url;
  LMSMyCourseModel? course;

  Map<String, dynamic> toJson() => {
        'id': id,
        'thumbnail': thumbnail,
        'title': title,
        'url': url,
        'course': course,
      };
}
