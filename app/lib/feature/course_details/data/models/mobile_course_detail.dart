import 'package:everlms/feature/home/data/models/course_search_result.dart';

/// Full course detail — GET /api/v1/courses/:courseId
class MobileCourseDetail {
  const MobileCourseDetail({
    required this.id,
    required this.title,
    this.uuid,
    this.description,
    this.shortDescription,
    this.whatLearn,
    this.prerequisites,
    this.whoFor,
    this.thumbnailUrl,
    this.bannerUrl,
    this.price = 0,
    this.priceType = 'free',
    this.level,
    this.category,
    this.tags = const [],
    this.instructors = const [],
    this.rating = 0.0,
    this.ratingCount = 0,
    this.studentCount = 0,
    this.durationMinutes = 0,
    this.bestseller = false,
    this.featured = false,
    this.curriculum = const [],
    this.enrollment,
    this.isWishlisted = false,
    this.certificateTemplateId,
    this.created,
  });

  factory MobileCourseDetail.fromJson(Map<String, dynamic> json) {
    final rawCurriculum = json['curriculum'];
    final curriculum = rawCurriculum is List
        ? rawCurriculum
            .whereType<Map<String, dynamic>>()
            .map(MobileCourseSection.fromJson)
            .toList()
        : <MobileCourseSection>[];

    final rawInstructors = json['instructors'];
    final instructors = rawInstructors is List
        ? rawInstructors
            .whereType<Map<String, dynamic>>()
            .map(MobileCourseInstructor.fromJson)
            .toList()
        : <MobileCourseInstructor>[];

    final rawTags = json['tags'];
    final tags =
        rawTags is List ? rawTags.whereType<String>().toList() : <String>[];

    final rawEnrollment = json['enrollment'];
    final enrollment = rawEnrollment is Map<String, dynamic>
        ? MobileCourseEnrollment.fromJson(rawEnrollment)
        : null;

    return MobileCourseDetail(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      uuid: json['uuid'] as String?,
      description: json['description'] as String?,
      shortDescription: json['short_description'] as String?,
      whatLearn: json['what_learn'] as String?,
      prerequisites: json['prerequisites'] as String?,
      whoFor: json['who_for'] as String?,
      thumbnailUrl: json['thumbnail_url'] as String?,
      bannerUrl: json['banner_url'] as String?,
      price: (json['price'] as num?)?.toDouble() ?? 0,
      priceType: json['price_type'] as String? ?? 'free',
      level: json['level'] is Map<String, dynamic>
          ? CourseTermItem.fromJson(json['level'] as Map<String, dynamic>)
          : null,
      category: json['category'] is Map<String, dynamic>
          ? CourseTermItem.fromJson(json['category'] as Map<String, dynamic>)
          : null,
      tags: tags,
      instructors: instructors,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      ratingCount: json['rating_count'] as int? ?? 0,
      studentCount: json['student_count'] as int? ?? 0,
      durationMinutes: json['duration_minutes'] as int? ?? 0,
      bestseller: json['bestseller'] as bool? ?? false,
      featured: json['featured'] as bool? ?? false,
      curriculum: curriculum,
      enrollment: enrollment,
      isWishlisted: json['is_wishlisted'] as bool? ?? false,
      certificateTemplateId: json['certificate_template_id'] as int?,
      created: json['created'] as String?,
    );
  }

  final int id;
  final String title;
  final String? uuid;
  final String? description;
  final String? shortDescription;
  final String? whatLearn;
  final String? prerequisites;
  final String? whoFor;
  final String? thumbnailUrl;
  final String? bannerUrl;
  final double price;
  final String priceType;
  final CourseTermItem? level;
  final CourseTermItem? category;
  final List<String> tags;
  final List<MobileCourseInstructor> instructors;
  final double rating;
  final int ratingCount;
  final int studentCount;
  final int durationMinutes;
  final bool bestseller;
  final bool featured;
  final List<MobileCourseSection> curriculum;
  final MobileCourseEnrollment? enrollment;
  final bool isWishlisted;
  final int? certificateTemplateId;
  final String? created;

  bool get isFree => priceType == 'free' || price == 0;
  bool get isEnrolled => enrollment?.enrolled ?? false;
}

class MobileCourseSection {
  const MobileCourseSection({
    required this.id,
    required this.title,
    this.description = '',
    this.weight = 0,
    this.lessons = const [],
  });

  factory MobileCourseSection.fromJson(Map<String, dynamic> json) {
    final rawLessons = json['lessons'];
    final lessons = rawLessons is List
        ? rawLessons
            .whereType<Map<String, dynamic>>()
            .map(MobileCourseLessonItem.fromJson)
            .toList()
        : <MobileCourseLessonItem>[];
    return MobileCourseSection(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      weight: json['weight'] as int? ?? 0,
      lessons: lessons,
    );
  }

  final int id;
  final String title;
  final String description;
  final int weight;
  final List<MobileCourseLessonItem> lessons;
}

class MobileCourseLessonItem {
  const MobileCourseLessonItem({
    required this.id,
    required this.title,
    this.type = 'text',
    this.durationMinutes = 0,
    this.freePreview = false,
    this.isCompleted = false,
    this.isLocked = false,
  });

  factory MobileCourseLessonItem.fromJson(Map<String, dynamic> json) {
    return MobileCourseLessonItem(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      durationMinutes: json['duration_minutes'] as int? ?? 0,
      freePreview: json['free_preview'] as bool? ?? false,
      isCompleted: json['is_completed'] as bool? ?? false,
      isLocked: json['is_locked'] as bool? ?? false,
    );
  }

  final int id;
  final String title;
  final String type;
  final int durationMinutes;
  final bool freePreview;
  final bool isCompleted;
  final bool isLocked;
}

class MobileCourseEnrollment {
  const MobileCourseEnrollment({
    this.enrolled = false,
    this.progress = 0,
    this.status = '',
    this.lastLessonId,
    this.lastLessonUrl,
  });

  factory MobileCourseEnrollment.fromJson(Map<String, dynamic> json) {
    return MobileCourseEnrollment(
      enrolled: json['enrolled'] as bool? ?? false,
      progress: json['progress'] as int? ?? 0,
      status: json['status'] as String? ?? '',
      lastLessonId: json['last_lesson_id'] as int?,
      lastLessonUrl: json['last_lesson_url'] as String?,
    );
  }

  final bool enrolled;
  final int progress;
  final String status;
  final int? lastLessonId;
  final String? lastLessonUrl;
}

class MobileCourseInstructor {
  const MobileCourseInstructor({
    required this.id,
    required this.name,
    this.avatarUrl,
    this.bio,
  });

  factory MobileCourseInstructor.fromJson(Map<String, dynamic> json) {
    return MobileCourseInstructor(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      avatarUrl: json['avatar_url'] as String?,
      bio: json['bio'] as String?,
    );
  }

  final int id;
  final String name;
  final String? avatarUrl;
  final String? bio;
}
