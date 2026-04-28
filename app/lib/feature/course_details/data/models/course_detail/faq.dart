class Faq {
  Faq({this.id, this.question, this.answer});

  factory Faq.fromJson(Map<String, dynamic> json) => Faq(
        id: int.parse(json['nid'][0]['value']),
        question: json['title'][0]['value'] as String?,
        answer: json['body'][0]['value'] as String?,
      );
  int? id;
  String? question;
  String? answer;

  Map<String, Object?> toJson() => {
        'nid': id,
        'title': question,
        'body': answer,
      };
}
