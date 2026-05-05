/// Item in GET /api/courses/search response
class CourseSearchItem {
  const CourseSearchItem({
    required this.id,
    required this.title,
    this.uuid,
    this.shortDescription,
    this.thumbnailUrl,
    this.price = 0,
    this.priceType = 'free',
    this.level,
    this.category,
    this.instructor,
    this.rating = 0.0,
    this.ratingCount = 0,
    this.studentCount = 0,
    this.durationMinutes = 0,
    this.bestseller = false,
    this.featured = false,
    this.created,
  });

  factory CourseSearchItem.fromJson(Map<String, dynamic> json) {
    return CourseSearchItem(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      uuid: json['uuid'] as String?,
      shortDescription: json['short_description'] as String?,
      thumbnailUrl: json['thumbnail_url'] as String?,
      price: (json['price'] as num?)?.toDouble() ?? 0,
      priceType: json['price_type'] as String? ?? 'free',
      level: json['level'] is Map<String, dynamic>
          ? CourseTermItem.fromJson(json['level'] as Map<String, dynamic>)
          : null,
      category: json['category'] is Map<String, dynamic>
          ? CourseTermItem.fromJson(json['category'] as Map<String, dynamic>)
          : null,
      instructor: json['instructor'] is Map<String, dynamic>
          ? CourseInstructorItem.fromJson(
              json['instructor'] as Map<String, dynamic>,)
          : null,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      ratingCount: json['rating_count'] as int? ?? 0,
      studentCount: json['student_count'] as int? ?? 0,
      durationMinutes: json['duration_minutes'] as int? ?? 0,
      bestseller: json['bestseller'] as bool? ?? false,
      featured: json['featured'] as bool? ?? false,
      created: json['created'] as String?,
    );
  }

  final int id;
  final String title;
  final String? uuid;
  final String? shortDescription;
  final String? thumbnailUrl;
  final double price;
  final String priceType;
  final CourseTermItem? level;
  final CourseTermItem? category;
  final CourseInstructorItem? instructor;
  final double rating;
  final int ratingCount;
  final int studentCount;
  final int durationMinutes;
  final bool bestseller;
  final bool featured;
  final String? created;

  bool get isFree => priceType == 'free' || price == 0;
}

/// Paginated wrapper for GET /api/courses/search
class CourseSearchResult {
  const CourseSearchResult({
    this.total = 0,
    this.page = 0,
    this.limit = 20,
    this.items = const [],
  });

  factory CourseSearchResult.fromJson(Map<String, dynamic> json) {
    final raw = json['items'];
    final items = raw is List
        ? raw
            .whereType<Map<String, dynamic>>()
            .map(CourseSearchItem.fromJson)
            .toList()
        : <CourseSearchItem>[];
    return CourseSearchResult(
      total: json['total'] as int? ?? 0,
      page: json['page'] as int? ?? 0,
      limit: json['limit'] as int? ?? 20,
      items: items,
    );
  }

  final int total;
  final int page;
  final int limit;
  final List<CourseSearchItem> items;
}

class CourseTermItem {
  const CourseTermItem({required this.id, required this.name});

  factory CourseTermItem.fromJson(Map<String, dynamic> json) {
    return CourseTermItem(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
    );
  }

  final int id;
  final String name;
}

class CourseInstructorItem {
  const CourseInstructorItem({
    required this.id,
    required this.name,
    this.avatarUrl,
  });

  factory CourseInstructorItem.fromJson(Map<String, dynamic> json) {
    return CourseInstructorItem(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      avatarUrl: json['avatar_url'] as String?,
    );
  }

  final int id;
  final String name;
  final String? avatarUrl;
}
