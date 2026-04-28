import 'package:everlms/feature/home/data/models/slider.dart';
import 'package:everlms/feature/root/data/models/common/category.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/instructor_old.dart';
import 'package:everlms/feature/root/data/models/common/my_course.dart';

class HomeData {
  HomeData({
    this.sectionType,
    this.sliders,
    this.myCourses,
    this.categories,
    this.topCourses,
    this.instructors,
    this.featuredCourses,
    this.offerCourses,
  });

  factory HomeData.fromJson(Map<String, Object?> json) => HomeData(
        sectionType: json['section_type'] as String?,
        sliders: (json['sliders'] as List<dynamic>?)
            ?.map((e) => Slider.fromJson(e as Map<String, Object?>))
            .toList(),
        myCourses: (json['my_courses'] as List<dynamic>?)
            ?.map((e) => MyCourse.fromJson(e as Map<String, Object?>))
            .toList(),
        categories: (json['categories'] as List<dynamic>?)
            ?.map((e) => Category.fromJson(e as Map<String, Object?>))
            .toList(),
        topCourses: (json['top_courses'] as List<dynamic>?)
            ?.map((e) => Course.fromJson(e as Map<String, Object?>))
            .toList(),
        instructors: (json['instructors'] as List<dynamic>?)
            ?.map((e) => InstructorOld.fromJson(e as Map<String, Object?>))
            .toList(),
        featuredCourses: (json['featured_courses'] as List<dynamic>?)
            ?.map((e) => Course.fromJson(e as Map<String, Object?>))
            .toList(),
        offerCourses: (json['offer_courses'] as List<dynamic>?)
            ?.map((e) => Course.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  String? sectionType;
  List<Slider>? sliders;
  List<MyCourse>? myCourses;
  List<Category>? categories;
  List<Course>? topCourses;
  List<InstructorOld>? instructors;
  List<Course>? featuredCourses;
  List<Course>? offerCourses;

  Map<String, Object?> toJson() => {
        'section_type': sectionType,
        'sliders': sliders?.map((e) => e.toJson()).toList(),
        'my_courses': myCourses?.map((e) => e.toJson()).toList(),
        'categories': categories?.map((e) => e.toJson()).toList(),
        'top_courses': topCourses?.map((e) => e.toJson()).toList(),
        'instructors': instructors?.map((e) => e.toJson()).toList(),
        'featured_courses': featuredCourses?.map((e) => e.toJson()).toList(),
        'offer_courses': offerCourses?.map((e) => e.toJson()).toList(),
      };
}
