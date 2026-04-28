// ignore_for_file: sort_constructors_first
import 'package:everlms/feature/comment/data/models/commenter_model.dart';

class ForumCommentModel {
  String id;
  String body;
  String created;
  CommenterModel commenter;

  ForumCommentModel({
    required this.id,
    required this.body,
    required this.created,
    required this.commenter,
  });

  factory ForumCommentModel.fromJson(Map<String, dynamic> json) {
    return ForumCommentModel(
      id: json['cid'][0]['value'],
      body: json['comment_body'] != null &&
              json['comment_body'] is List &&
              (json['comment_body'] as List).isNotEmpty &&
              json['comment_body'][0]['value'] != null
          ? json['comment_body'][0]['value']
          : '',
      created: json['created'][0]['value'],
      commenter: CommenterModel.fromJson(json['commenter']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'cid': [
        {'value': id},
      ],
      'comment_body': [
        {'value': body},
      ],
      'created': [
        {'value': created},
      ],
    };
  }
}
