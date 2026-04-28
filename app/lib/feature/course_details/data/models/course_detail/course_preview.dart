class CoursePreview {
  CoursePreview({this.id, this.type, this.label, this.url});

  factory CoursePreview.fromJson(Map<String, dynamic> json) {
    return CoursePreview(
      id: json['media_id'],
      type: json['type'],
      label: json['label'],
      url: json['url'],
    );
  }
  String? id;
  String? type;
  String? label;
  String? url;

  Map<String, dynamic> toJson() => {
        'media_id': id,
        'type': type,
        'label': label,
        'url': url,
      };
}
