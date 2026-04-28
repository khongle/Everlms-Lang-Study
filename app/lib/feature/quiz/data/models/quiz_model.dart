import 'package:everlms/feature/quiz/data/models/data.dart';

class QuizModel {
  QuizModel({required this.message, required this.data});

  QuizModel.fromJson(Map<String, dynamic> json) {
    message = json['message'];
    data = json['data'] != null
        ? (json['data'] as List).map((i) => DataQuiz.fromJson(i)).toList()
        : <DataQuiz>[];
  }
  late String message;
  late List<DataQuiz> data;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};

    data['message'] = message;
    data['data'] = this.data.map((v) => v.toJson()).toList();
    return data;
  }
}
