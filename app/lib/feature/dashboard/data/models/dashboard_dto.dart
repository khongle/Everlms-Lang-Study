/// DTO for `GET /api/v1/dashboard` response.
class DashboardDto {
  const DashboardDto({
    required this.inProgressCourses,
    required this.completedCoursesCount,
    required this.certificatesCount,
    required this.enrolledCoursesCount,
  });

  factory DashboardDto.fromJson(Map<String, dynamic> json) {
    final rawList = json['in_progress_courses'];
    final courses = rawList is List
        ? rawList
            .whereType<Map<String, dynamic>>()
            .map(InProgressCourseDto.fromJson)
            .toList()
        : <InProgressCourseDto>[];

    return DashboardDto(
      inProgressCourses: courses,
      completedCoursesCount:
          (json['completed_courses_count'] as num?)?.toInt() ?? 0,
      certificatesCount: (json['certificates_count'] as num?)?.toInt() ?? 0,
      enrolledCoursesCount:
          (json['enrolled_courses_count'] as num?)?.toInt() ?? 0,
    );
  }

  final List<InProgressCourseDto> inProgressCourses;
  final int completedCoursesCount;
  final int certificatesCount;
  final int enrolledCoursesCount;
}

/// A single in-progress course item in the dashboard.
class InProgressCourseDto {
  const InProgressCourseDto({
    required this.id,
    required this.title,
    required this.thumbnailUrl,
    required this.progress,
    this.lastLessonId,
    this.lastLessonTitle,
  });

  factory InProgressCourseDto.fromJson(Map<String, dynamic> json) {
    return InProgressCourseDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String? ?? '',
      progress: (json['progress'] as num?)?.toDouble() ?? 0.0,
      lastLessonId: (json['last_lesson_id'] as num?)?.toInt(),
      lastLessonTitle: json['last_lesson_title'] as String?,
    );
  }

  final int id;
  final String title;
  final String thumbnailUrl;
  final double progress;
  final int? lastLessonId;
  final String? lastLessonTitle;
}
