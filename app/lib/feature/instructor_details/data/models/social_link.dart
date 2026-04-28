class SocialLink {
  SocialLink({this.name = '', this.link = ''});

  factory SocialLink.fromJson(Map<String, Object?> json) => SocialLink(
        name: json['name'] != null ? json['name']! as String : '',
        link: json['link'] != null ? json['link']! as String : '',
      );
  late String name;
  late String link;

  Map<String, Object?> toJson() => {
        'name': name,
        'link': link,
      };
}
