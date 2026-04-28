import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:everlms/feature/quiz/data/models/quiz.dart';

class DataQuiz {
  DataQuiz({required this.questions, this.quiz});

  DataQuiz.fromJson(Map<String, dynamic> json) {
    if (json['quiz'] != null) {
      quiz = Quiz.fromJson(json['quiz']);
    } else {
      quiz = Quiz.fromJson(json);
    }

    if (json['field_questions'] != null) {
      questions = <Questions>[];
      json['field_questions'].forEach((v) {
        questions.add(Questions.fromJson(v));
      });
    }
  }
  Quiz? quiz;
  List<Questions> questions = [];

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (quiz != null) {
      data['quiz'] = quiz!.toJson();
    }
    data['field_questions'] = questions.map((v) => v.toJson()).toList();
    return data;
  }
}
