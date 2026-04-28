class Option {
  Option({required this.id, required this.answer, required this.isCorrect});

  Option.fromJson(Map<String, dynamic> value) {
    answer = value['name'];
    id = value['target_id'];
  }
  String answer = '';
  String id = '';
  int isCorrect = 0;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['name'] = answer;
    data['target_id'] = id;

    return data;
  }
}
