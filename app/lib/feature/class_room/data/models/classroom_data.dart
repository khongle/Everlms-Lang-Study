import 'package:everlms/feature/root/data/models/common/section.dart';

class Data {
  Data({this.chapters});

  factory Data.fromJson(Map<String, Object?> json) => Data(
        chapters: (json['chapters'] as List<dynamic>?)
            ?.map((e) => Chapter.fromJson(e as Map<String, Object?>))
            .toList(),
      );
  List<Chapter>? chapters;

  Map<String, Object?> toJson() => {
        'chapters': chapters?.map((e) => e.toJson()).toList(),
      };
}
