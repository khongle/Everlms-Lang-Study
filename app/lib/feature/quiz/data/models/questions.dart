import 'package:everlms/feature/quiz/data/models/option.dart';

enum QuestionType { defaultQuestion, mcq, shortQuestion }

class Questions {
  Questions({
    required this.question,
    required this.questionType,
    this.option,
    this.correctAnswerIndex,
  });

  Questions.fromJson(Map<String, dynamic> json) {
    question = json['question_name'];
    //questionType = json['question_type'];
    switch (json['question_type']) {
      case 'short_question':
        questionType = QuestionType.shortQuestion;
      case 'mcq':
        questionType = QuestionType.mcq;
      case 'default':
        questionType = QuestionType.defaultQuestion;
      default:
        questionType = QuestionType.defaultQuestion;
    }

    if (json['choices_name'] != null) {
      option = <Option>[];
      correctAnswerIndex = <int>[];
      final list = json['choices_name'] as List;
      for (var i = 0; i < list.length; i++) {
        option!.add(Option.fromJson(list[i]));
        if (json['correct_answer_index'] != null) {
          correctAnswerIndex = <int>[];
          final list = json['correct_answer_index'] as List;
          for (var i = 0; i < list.length; i++) {
            correctAnswerIndex!.add(list[i]);
          }
        } else if (list[i]['field_correct'] == '1') {
          correctAnswerIndex!.add(i);
        }
      }
    }
  }
  late String question;
  late QuestionType questionType;
  List<Option>? option;
  List<int>? correctAnswerIndex;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['question_name'] = question;
    switch (questionType) {
      case QuestionType.shortQuestion:
        data['question_type'] = 'short_question';
      case QuestionType.mcq:
        data['question_type'] = 'mcq';
      case QuestionType.defaultQuestion:
        data['question_type'] = 'default';
    }
    if (option != null) {
      data['choices_name'] = option!.map((v) => v.toJson()).toList();
    }
    data['correct_answer_index'] = correctAnswerIndex;
    return data;
  }
}
