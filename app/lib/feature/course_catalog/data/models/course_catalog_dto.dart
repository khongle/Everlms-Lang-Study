/// DTO for a single course item in search/list results.
class CourseListItemDto {
  const CourseListItemDto({
    required this.id,
    required this.title,
    required this.thumbnailUrl,
    required this.price,
    required this.priceType,
    required this.rating,
    required this.ratingCount,
    required this.studentCount,
    required this.durationMinutes,
    this.shortDescription = '',
    this.level,
    this.category,
    this.instructor,
    this.bestseller = false,
    this.featured = false,
  });

  factory CourseListItemDto.fromJson(Map<String, dynamic> json) {
    final level = json['level'] as Map<String, dynamic>?;
    final category = json['category'] as Map<String, dynamic>?;

    // instructor may be a map or inside 'instructors' list
    Map<String, dynamic>? instructor;
    if (json['instructor'] is Map<String, dynamic>) {
      instructor = json['instructor'] as Map<String, dynamic>;
    } else if (json['instructors'] is List) {
      final list = json['instructors'] as List;
      if (list.isNotEmpty && list.first is Map<String, dynamic>) {
        instructor = list.first as Map<String, dynamic>;
      }
    }

    return CourseListItemDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String? ?? '',
      shortDescription: json['short_description'] as String? ?? '',
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      priceType: json['price_type'] as String? ?? 'free',
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      ratingCount: (json['rating_count'] as num?)?.toInt() ?? 0,
      studentCount: (json['student_count'] as num?)?.toInt() ?? 0,
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      level: level != null
          ? TermRefDto(
              id: (level['id'] as num?)?.toInt() ?? 0,
              name: level['name'] as String? ?? '',
            )
          : null,
      category: category != null
          ? TermRefDto(
              id: (category['id'] as num?)?.toInt() ?? 0,
              name: category['name'] as String? ?? '',
            )
          : null,
      instructor: instructor != null
          ? InstructorRefDto(
              id: (instructor['id'] as num?)?.toInt() ?? 0,
              name: instructor['name'] as String? ?? '',
              avatarUrl: instructor['avatar_url'] as String? ?? '',
            )
          : null,
      bestseller: (json['bestseller'] as bool?) ?? false,
      featured: (json['featured'] as bool?) ?? false,
    );
  }

  final int id;
  final String title;
  final String thumbnailUrl;
  final String shortDescription;
  final double price;
  final String priceType;
  final double rating;
  final int ratingCount;
  final int studentCount;
  final int durationMinutes;
  final TermRefDto? level;
  final TermRefDto? category;
  final InstructorRefDto? instructor;
  final bool bestseller;
  final bool featured;

  bool get isFree => priceType == 'free' || price == 0;
}

/// DTO for `GET /api/v1/categories` response item.
class CategoryDto {
  const CategoryDto({
    required this.id,
    required this.name,
    required this.courseCount,
    this.description = '',
    this.thumbnailUrl = '',
  });

  factory CategoryDto.fromJson(Map<String, dynamic> json) {
    return CategoryDto(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name'] as String? ?? '',
      description: json['description'] as String? ?? '',
      thumbnailUrl: json['thumbnail_url'] as String? ?? '',
      courseCount: (json['course_count'] as num?)?.toInt() ?? 0,
    );
  }

  final int id;
  final String name;
  final String description;
  final String thumbnailUrl;
  final int courseCount;
}

class TermRefDto {
  const TermRefDto({required this.id, required this.name});
  final int id;
  final String name;
}

class InstructorRefDto {
  const InstructorRefDto({
    required this.id,
    required this.name,
    required this.avatarUrl,
  });
  final int id;
  final String name;
  final String avatarUrl;
}

/// Pagination-aware response from the course search endpoint.
class CourseSearchResponseDto {
  const CourseSearchResponseDto({
    required this.items,
    required this.total,
    required this.page,
    required this.limit,
  });

  factory CourseSearchResponseDto.fromJson(Map<String, dynamic> json) {
    final rawItems = json['items'] as List? ?? [];
    final items = rawItems
        .whereType<Map<String, dynamic>>()
        .map(CourseListItemDto.fromJson)
        .toList();

    return CourseSearchResponseDto(
      items: items,
      total: (json['total'] as num?)?.toInt() ?? items.length,
      page: (json['page'] as num?)?.toInt() ?? 0,
      limit: (json['limit'] as num?)?.toInt() ?? 20,
    );
  }

  final List<CourseListItemDto> items;
  final int total;
  final int page;
  final int limit;

  bool get hasMore => (page + 1) * limit < total;
}
