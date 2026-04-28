class Features {
  Features({
    this.courseName,
    this.rating,
    this.totalLesson,
    this.totalEnroll,
    this.totalQuiz,
    this.totalVideos,
    this.totalNoteFiles,
    this.totalAudioFiles,
    this.category,
    this.updated,
    this.language,
  });

  factory Features.fromJson(Map<String, Object?> json) => Features(
        totalLesson: json['total_lesson'] as String?,
        totalEnroll: json['total_enroll'] as String?,
        totalQuiz: json['total_quiz'] as String?,
        totalVideos: json['total_videos'] as String?,
        totalNoteFiles: json['total_note_files'] as String?,
        totalAudioFiles: json['total_audio_files'] as String?,
      );
  String? courseName;
  String? rating;
  String? totalLesson;
  String? totalEnroll;
  String? totalQuiz;
  String? totalVideos;
  String? totalNoteFiles;
  String? totalAudioFiles;
  String? category;
  String? updated;
  String? language;

  Map<String, Object?> toJson() => {
        'total_lesson': totalLesson,
        'total_enroll': totalEnroll,
        'total_quiz': totalQuiz,
        'total_videos': totalVideos,
        'total_note_files': totalNoteFiles,
        'total_audio_files': totalAudioFiles,
      };
}
