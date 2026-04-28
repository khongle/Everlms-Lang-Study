// ignore_for_file: sort_constructors_first
class ForumTopicModel {
  String id;
  String title;
  String name;
  String body;
  String created;
  String commentCount;
  LastReply? lastReply;

  ForumTopicModel({
    required this.id,
    required this.title,
    required this.name,
    required this.body,
    required this.created,
    required this.commentCount,
    required this.lastReply,
  });

  factory ForumTopicModel.fromJson(Map<String, dynamic> json) {
    return ForumTopicModel(
      id: json['nid'][0]['value'],
      title: json['title'][0]['value'],
      name: json['name'],
      body: (json['body'] as List).isNotEmpty
          ? json['body'][0]['value'] ?? ''
          : '',
      commentCount: json['comment_count'],
      created: json['created'][0]['value'],
      lastReply: json['last_reply'] != null
          ? LastReply.fromJson(json['last_reply'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'nid': [
        {'value': id},
      ],
      'title': [
        {'value': title},
      ],
      'name': name,
      'body': [
        {'value': body},
      ],
      'created': [
        {'value': created},
      ],
      'comment_count': commentCount,
      'last_reply': lastReply?.toJson(),
    };
  }
}

class LastReply {
  String name;
  String uid;
  String created;
  LastReply({
    required this.name,
    required this.uid,
    required this.created,
  });

  factory LastReply.fromJson(Map<String, dynamic> json) {
    return LastReply(
      name: json['name'] ?? '',
      uid: json['uid'] ?? '',
      created: json['created'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'uid': uid,
      'created': created,
    };
  }
}
