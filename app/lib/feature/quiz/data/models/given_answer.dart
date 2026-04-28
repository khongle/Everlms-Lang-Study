import 'package:everlms/feature/quiz/data/models/questions.dart';

class GivenAnswer {
  GivenAnswer({
    required this.question,
    required this.selectedIndexes,
    required this.isCorrect,
    this.shortQuestionAns,
  });

  factory GivenAnswer.fromJson(Map<String, dynamic> json) {
    return GivenAnswer(
      question: Questions.fromJson(json['question']),
      isCorrect: json['isCorrect'],
      selectedIndexes: (json['selectedIndexes'] as List<dynamic>).map((e) {
        return e as int;
      }).toList(),
      shortQuestionAns: json['shortQuestionAns'],
    );
  }
  final Questions question;
  final bool isCorrect;
  List<int> selectedIndexes = [];
  String? shortQuestionAns = '';

  Map<String, dynamic> toJson() {
    return {
      'question': question.toJson(),
      'isCorrect': isCorrect,
      'selectedIndexes': selectedIndexes,
      'shortQuestionAns': shortQuestionAns,
    };
  }
}
