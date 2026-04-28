import 'package:everlms/feature/comment/data/models/commenter_model.dart';

class CommentModel {
  CommentModel({
    required this.id,
    required this.uid,
    required this.body,
    required this.created,
    required this.changed,
    required this.commenter,
  });

  factory CommentModel.fromJson(Map<String, dynamic> json) {
    return CommentModel(
      id: json['cid'],
      uid: json['uid'],
      body: json['comment_body'],
      created: json['created'],
      changed: json['changed'],
      commenter: json['commenter'] != null
          ? CommenterModel.fromJson(json['commenter'])
          : null,
    );
  }
  String? id;
  String? uid;
  String? body;
  String? created;
  String? changed;
  CommenterModel? commenter;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['uid'] = uid;
    data['comment_body'] = body;
    data['created'] = created;
    data['changed'] = changed;
    data['commenter'] = commenter;
    return data;
  }
}
