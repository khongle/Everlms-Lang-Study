class Student {
  Student({this.id, this.name, this.image});

  factory Student.fromJson(Map<String, Object?> json) => Student(
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
