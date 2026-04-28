import 'package:everlms/feature/quiz/data/models/quiz.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';

class Chapter {
  Chapter({
    this.id,
    this.title,
    this.totalLesson,
    this.completeLesson,
    this.lessons,
    this.quizzes,
  });

  factory Chapter.fromJson(Map<String, dynamic> json) => Chapter(
        id: json['lms_chapter_id'] != null
            ? int.parse(json['lms_chapter_id'][0]['value'])
            : null,
        title:
            json['title'] != null ? json['title'][0]['value'] as String? : null,
        totalLesson: json['lesson_count'],
        completeLesson: json['progress'],
        lessons: json['field_lesson'] != null
            ? (json['field_lesson'] as Map<String, dynamic>)
                .values
                .map((e) => Lesson.fromJsonGetCourse(e as Map<String, Object?>))
                .toList()
            : null,
      );

  factory Chapter.fromJsonOffline(Map<String, dynamic> json) => Chapter(
        id: json['lms_chapter_id'],
        title: json['title'] != null ? json['title'] as String? : null,
        totalLesson: json['lesson_count'],
        completeLesson: json['progress'],
        lessons: [],
      );
  int? id;
  String? title;
  int? totalLesson;
  int? completeLesson;
  List<Lesson>? lessons;
  List<Quiz>? quizzes;

  Map<String, Object?> toJson() => {
        'lms_chapter_id': id,
        'title': title,
        'field_lesson': lessons?.map((e) => e.toJson()).toList(),
        'field_chapter_quiz': quizzes?.map((e) => e.toJson()).toList(),
      };

  // factory Chapter.fromJson(Map<String, Object?> json) => Chapter(
  //       id: json['id'] as int?,
  //       title: json['title'] as String?,
  //       lessons: (json['lessons'] as List<dynamic>?)
  //           ?.map((e) => Lesson.fromJson(e as Map<String, Object?>))
  //           .toList(),
  //       quizes: (json['quizzes'] as List<dynamic>?)
  //           ?.map((e) => Quiz.fromJson(e as Map<String, Object?>))
  //           .toList(),
  //     );

  // Map<String, Object?> toJson() => {
  //       'id': id,
  //       'title': title,
  //       'lessons': lessons?.map((e) => e.toJson()).toList(),
  //       'quizzes': quizes?.map((e) => e.toJson()).toList(),
  //     };
}
