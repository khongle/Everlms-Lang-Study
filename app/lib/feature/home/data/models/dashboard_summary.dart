/// Response model for GET /api/v1/dashboard
class DashboardSummary {
  const DashboardSummary({
    this.inProgressCourses = const [],
    this.completedCoursesCount = 0,
    this.certificatesCount = 0,
    this.enrolledCoursesCount = 0,
  });

  factory DashboardSummary.fromJson(Map<String, dynamic> json) {
    final raw = json['in_progress_courses'];
    final courses = raw is List
        ? raw
            .whereType<Map<String, dynamic>>()
            .map(DashboardCourse.fromJson)
            .toList()
        : <DashboardCourse>[];
    return DashboardSummary(
      inProgressCourses: courses,
      completedCoursesCount: json['completed_courses_count'] as int? ?? 0,
      certificatesCount: json['certificates_count'] as int? ?? 0,
      enrolledCoursesCount: json['enrolled_courses_count'] as int? ?? 0,
    );
  }

  final List<DashboardCourse> inProgressCourses;
  final int completedCoursesCount;
  final int certificatesCount;
  final int enrolledCoursesCount;
}

class DashboardCourse {
  const DashboardCourse({
    required this.id,
    required this.title,
    this.thumbnailUrl,
    this.progress = 0,
    this.lastLessonId,
    this.lastLessonTitle,
  });

  factory DashboardCourse.fromJson(Map<String, dynamic> json) {
    return DashboardCourse(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String?,
      progress: json['progress'] as int? ?? 0,
      lastLessonId: json['last_lesson_id'] as int?,
      lastLessonTitle: json['last_lesson_title'] as String?,
    );
  }

  final int id;
  final String title;
  final String? thumbnailUrl;
  final int progress;
  final int? lastLessonId;
  final String? lastLessonTitle;
}
