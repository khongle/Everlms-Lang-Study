class InstructorOld {
  InstructorOld({this.id, this.name, this.image, this.instructor, this.about});

  factory InstructorOld.fromJson(Map<String, Object?> json) => InstructorOld(
        id: json['id'] as int?,
        name: json['name'] as String?,
        image: json['image'] as String?,
        instructor: json['instructor'] as String?,
        about: json['about'] as String?,
      );
  int? id;
  String? name;
  String? image;
  String? instructor;
  String? about;

  Map<String, Object?> toJson() => {
        'id': id,
        'name': name,
        'image': image,
        'instructor': instructor,
        'about': about,
      };
}
