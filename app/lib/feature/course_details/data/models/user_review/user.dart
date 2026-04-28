class User {
  User({this.id, this.name, this.image});

  factory User.fromJson(Map<String, Object?> json) => User(
        id: json['id'] as int?,
        name: json['name'] as String?,
        image: json['image'] as String?,
      );
  int? id;
  String? name;
  String? image;

  Map<String, Object?> toJson() => {
        'id': id,
        'name': name,
        'image': image,
      };
}
