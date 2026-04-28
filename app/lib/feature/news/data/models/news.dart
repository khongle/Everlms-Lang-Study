import 'package:everlms/core/utils/help_me.dart';

class News {
  News({
    this.id,
    this.title,
    this.thumbnail,
    this.content,
    this.summary,
    this.created,
    this.tag,
  });

  factory News.fromJson(Map<String, dynamic> json) => News(
        id: int.parse(parseJsonDrupal(json['nid'])),
        title: parseJsonDrupal(json['title']) as String?,
        thumbnail:
            parseJsonDrupal(json['field_thumb'], subKey: 'url') as String?,
        content: parseJsonDrupal(json['body']) as String?,
        summary: parseJsonDrupal(json['body'], subKey: 'summary') as String?,
        created: parseJsonDrupal(json['created']) as String?,
        tag: parseJsonDrupal(json['field_tags']) as String?,
      );
  int? id;
  String? title;
  String? thumbnail;
  String? content;
  String? summary;
  String? created;
  String? tag;

  Map<String, Object?> toJson() => {
        'nid': id,
        'title': title,
        'thumbnail': thumbnail,
        'body': content,
        'created': created,
        'field_tags': tag,
      };
}
