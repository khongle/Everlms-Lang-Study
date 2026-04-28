/// Full course detail DTOs for `GET /api/v1/courses/:id`.
library;

class CourseDetailDto {
  const CourseDetailDto({
    required this.id,
    required this.title,
    required this.thumbnailUrl,
    required this.price,
    required this.priceType,
    required this.rating,
    required this.ratingCount,
    required this.studentCount,
    required this.durationMinutes,
    required this.lessonCount,
    required this.chapters,
    required this.enrollment,
    this.shortDescription = '',
    this.fullDescription = '',
    this.whatYouLearn = const [],
    this.prerequisites = const [],
    this.whoThisIsFor = const [],
    this.instructors = const [],
    this.tags = const [],
    this.level,
    this.category,
    this.bestseller = false,
    this.featured = false,
  });

  factory CourseDetailDto.fromJson(Map<String, dynamic> json) {
    final rawChapters = json['chapters'] as List? ?? [];
    final rawInstructors = json['instructors'] as List? ?? [];
    final rawWhatLearn = json['what_you_learn'] as List? ?? [];
    final rawPrereqs = json['prerequisites'] as List? ?? [];
    final rawWhoFor = json['who_this_is_for'] as List? ?? [];
    final rawTags = json['tags'] as List? ?? [];
    final levelMap = json['level'] as Map<String, dynamic>?;
    final categoryMap = json['category'] as Map<String, dynamic>?;

    return CourseDetailDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String? ?? '',
      shortDescription: json['short_description'] as String? ?? '',
      fullDescription: json['full_description'] as String? ?? '',
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      priceType: json['price_type'] as String? ?? 'free',
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      ratingCount: (json['rating_count'] as num?)?.toInt() ?? 0,
      studentCount: (json['student_count'] as num?)?.toInt() ?? 0,
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      lessonCount: (json['lesson_count'] as num?)?.toInt() ?? 0,
      whatYouLearn: rawWhatLearn.whereType<String>().toList(),
      prerequisites: rawPrereqs.whereType<String>().toList(),
      whoThisIsFor: rawWhoFor.whereType<String>().toList(),
      chapters: rawChapters
          .whereType<Map<String, dynamic>>()
          .map(ChapterDto.fromJson)
          .toList(),
      instructors: rawInstructors
          .whereType<Map<String, dynamic>>()
          .map(CourseInstructorDto.fromJson)
          .toList(),
      tags: rawTags.whereType<String>().toList(),
      enrollment: json['enrollment'] != null
          ? EnrollmentDto.fromJson(json['enrollment'] as Map<String, dynamic>)
          : null,
      level: levelMap != null
          ? CourseTermRefDto(
              id: (levelMap['id'] as num?)?.toInt() ?? 0,
              name: levelMap['name'] as String? ?? '',
            )
          : null,
      category: categoryMap != null
          ? CourseTermRefDto(
              id: (categoryMap['id'] as num?)?.toInt() ?? 0,
              name: categoryMap['name'] as String? ?? '',
            )
          : null,
      bestseller: json['bestseller'] as bool? ?? false,
      featured: json['featured'] as bool? ?? false,
    );
  }

  final int id;
  final String title;
  final String thumbnailUrl;
  final String shortDescription;
  final String fullDescription;
  final double price;
  final String priceType;
  final double rating;
  final int ratingCount;
  final int studentCount;
  final int durationMinutes;
  final int lessonCount;
  final List<String> whatYouLearn;
  final List<String> prerequisites;
  final List<String> whoThisIsFor;
  final List<ChapterDto> chapters;
  final List<CourseInstructorDto> instructors;
  final List<String> tags;
  final EnrollmentDto? enrollment;
  final CourseTermRefDto? level;
  final CourseTermRefDto? category;
  final bool bestseller;
  final bool featured;

  bool get isFree => priceType == 'free' || price == 0;
  bool get isEnrolled => enrollment?.isActive ?? false;
}

class ChapterDto {
  const ChapterDto({
    required this.id,
    required this.title,
    required this.position,
    required this.lessons,
    this.description = '',
  });

  factory ChapterDto.fromJson(Map<String, dynamic> json) {
    final rawLessons = json['lessons'] as List? ?? [];
    return ChapterDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      position: (json['position'] as num?)?.toInt() ?? 0,
      description: json['description'] as String? ?? '',
      lessons: rawLessons
          .whereType<Map<String, dynamic>>()
          .map(LessonItemDto.fromJson)
          .toList(),
    );
  }

  final int id;
  final String title;
  final String description;
  final int position;
  final List<LessonItemDto> lessons;
}

class LessonItemDto {
  const LessonItemDto({
    required this.id,
    required this.title,
    required this.type,
    required this.position,
    required this.durationMinutes,
    this.isFreePreview = false,
    this.isCompleted = false,
    this.isLocked = true,
  });

  factory LessonItemDto.fromJson(Map<String, dynamic> json) {
    return LessonItemDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      position: (json['position'] as num?)?.toInt() ?? 0,
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      isFreePreview: json['free_preview'] as bool? ?? false,
      isCompleted: json['is_completed'] as bool? ?? false,
      isLocked: json['is_locked'] as bool? ?? true,
    );
  }

  final int id;
  final String title;
  final String type;
  final int position;
  final int durationMinutes;
  final bool isFreePreview;
  final bool isCompleted;
  final bool isLocked;
}

class CourseInstructorDto {
  const CourseInstructorDto({
    required this.id,
    required this.name,
    required this.avatarUrl,
    this.bio = '',
    this.rating = 0.0,
    this.studentCount = 0,
    this.courseCount = 0,
  });

  factory CourseInstructorDto.fromJson(Map<String, dynamic> json) {
    return CourseInstructorDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name'] as String? ?? '',
      avatarUrl: json['avatar_url'] as String? ?? '',
      bio: json['bio'] as String? ?? '',
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      studentCount: (json['student_count'] as num?)?.toInt() ?? 0,
      courseCount: (json['course_count'] as num?)?.toInt() ?? 0,
    );
  }

  final int id;
  final String name;
  final String avatarUrl;
  final String bio;
  final double rating;
  final int studentCount;
  final int courseCount;
}

class EnrollmentDto {
  const EnrollmentDto({
    required this.id,
    required this.progress,
    required this.completedLessons,
    required this.totalLessons,
    this.lastLessonId,
    this.isActive = false,
    this.completedAt,
  });

  factory EnrollmentDto.fromJson(Map<String, dynamic> json) {
    return EnrollmentDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      progress: (json['progress'] as num?)?.toDouble() ?? 0.0,
      completedLessons: (json['completed_lessons'] as num?)?.toInt() ?? 0,
      totalLessons: (json['total_lessons'] as num?)?.toInt() ?? 0,
      lastLessonId: (json['last_lesson_id'] as num?)?.toInt(),
      isActive: json['is_active'] as bool? ?? false,
      completedAt: json['completed_at'] != null
          ? DateTime.tryParse(json['completed_at'] as String)
          : null,
    );
  }

  final int id;
  final double progress;
  final int completedLessons;
  final int totalLessons;
  final int? lastLessonId;
  final bool isActive;
  final DateTime? completedAt;
}

class ReviewDto {
  const ReviewDto({
    required this.id,
    required this.userName,
    required this.userAvatarUrl,
    required this.rating,
    required this.comment,
    required this.createdAt,
  });

  factory ReviewDto.fromJson(Map<String, dynamic> json) {
    return ReviewDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      userName: json['user_name'] as String? ?? '',
      userAvatarUrl: json['user_avatar_url'] as String? ?? '',
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      comment: json['comment'] as String? ?? '',
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ??
          DateTime.now(),
    );
  }

  final int id;
  final String userName;
  final String userAvatarUrl;
  final double rating;
  final String comment;
  final DateTime createdAt;
}

class CourseTermRefDto {
  const CourseTermRefDto({required this.id, required this.name});
  final int id;
  final String name;
}
