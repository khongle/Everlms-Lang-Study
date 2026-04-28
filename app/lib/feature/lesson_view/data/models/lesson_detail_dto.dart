/// DTOs for `GET /api/v1/courses/:courseId/lessons/:lessonId`.
library;

class LessonDetailDto {
  const LessonDetailDto({
    required this.id,
    required this.title,
    required this.type,
    required this.courseId,
    required this.courseTitle,
    required this.chapterTitle,
    this.content,
    this.videoUrl = '',
    this.htmlContent = '',
    this.pdfUrl = '',
    this.durationMinutes = 0,
    this.attachments = const [],
    this.prevLessonId,
    this.nextLessonId,
    this.isCompleted = false,
    this.zoomMeetingId,
    this.zoomPassword,
    this.zoomJoinUrl,
    this.zoomStartTime,
  });

  factory LessonDetailDto.fromJson(Map<String, dynamic> json, int courseId) {
    final rawAttachments = json['attachments'] as List? ?? [];
    return LessonDetailDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      courseId: courseId,
      courseTitle: json['course_title'] as String? ?? '',
      chapterTitle: json['chapter_title'] as String? ?? '',
      content: json['content'] as Map<String, dynamic>?,
      videoUrl: json['video_url'] as String? ?? '',
      htmlContent: json['html_content'] as String? ?? '',
      pdfUrl: json['pdf_url'] as String? ?? '',
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      attachments: rawAttachments
          .whereType<Map<String, dynamic>>()
          .map(LessonAttachmentDto.fromJson)
          .toList(),
      prevLessonId: (json['prev_lesson_id'] as num?)?.toInt(),
      nextLessonId: (json['next_lesson_id'] as num?)?.toInt(),
      isCompleted: json['is_completed'] as bool? ?? false,
      zoomMeetingId: json['zoom_meeting_id'] as String?,
      zoomPassword: json['zoom_password'] as String?,
      zoomJoinUrl: json['zoom_join_url'] as String?,
      zoomStartTime: json['zoom_start_time'] != null
          ? DateTime.tryParse(json['zoom_start_time'] as String)
          : null,
    );
  }

  final int id;
  final String title;
  final String type;
  final int courseId;
  final String courseTitle;
  final String chapterTitle;
  final Map<String, dynamic>? content;
  final String videoUrl;
  final String htmlContent;
  final String pdfUrl;
  final int durationMinutes;
  final List<LessonAttachmentDto> attachments;
  final int? prevLessonId;
  final int? nextLessonId;
  final bool isCompleted;

  // Zoom fields
  final String? zoomMeetingId;
  final String? zoomPassword;
  final String? zoomJoinUrl;
  final DateTime? zoomStartTime;
}

class LessonAttachmentDto {
  const LessonAttachmentDto({
    required this.id,
    required this.name,
    required this.url,
    required this.fileType,
  });

  factory LessonAttachmentDto.fromJson(Map<String, dynamic> json) {
    return LessonAttachmentDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name'] as String? ?? '',
      url: json['url'] as String? ?? '',
      fileType: json['file_type'] as String? ?? '',
    );
  }

  final int id;
  final String name;
  final String url;
  final String fileType;
}

class LessonNoteDto {
  const LessonNoteDto({
    required this.lessonId,
    required this.content,
    required this.timestampSeconds,
    this.updatedAt,
  });

  factory LessonNoteDto.fromJson(Map<String, dynamic> json) {
    return LessonNoteDto(
      lessonId: (json['lesson_id'] as num?)?.toInt() ?? 0,
      content: json['content'] as String? ?? '',
      timestampSeconds: (json['timestamp_seconds'] as num?)?.toInt() ?? 0,
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'] as String)
          : null,
    );
  }

  final int lessonId;
  final String content;
  final int timestampSeconds;
  final DateTime? updatedAt;
}
