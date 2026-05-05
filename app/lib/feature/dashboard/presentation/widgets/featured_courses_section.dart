import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/rating_bar.dart';
import 'package:flutter/material.dart';

// AppColors.accentStar is kept for brand consistency (star icon color).

/// Horizontal scroll strip of small featured course cards.
class FeaturedCoursesSection extends StatelessWidget {
  const FeaturedCoursesSection({
    required this.courses,
    required this.onCourseTap,
    super.key,
  });

  final List<Map<String, dynamic>> courses;
  final void Function(int courseId) onCourseTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 200,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        scrollDirection: Axis.horizontal,
        itemCount: courses.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final course = courses[index];
          return _FeaturedCourseCard(
            course: course,
            onTap: () => onCourseTap((course['id'] as num?)?.toInt() ?? 0),
          );
        },
      ),
    );
  }
}

class _FeaturedCourseCard extends StatelessWidget {
  const _FeaturedCourseCard({
    required this.course,
    required this.onTap,
  });

  final Map<String, dynamic> course;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cs = theme.colorScheme;
    final title = course['title'] as String? ?? '';
    final thumbnailUrl = course['thumbnail_url'] as String? ?? '';
    final instructor = course['instructor'] as Map<String, dynamic>?;
    final instructorName = instructor?['name'] as String? ?? '';
    final rating = (course['rating'] as num?)?.toDouble() ?? 0.0;
    final priceType = course['price_type'] as String? ?? 'paid';
    final price = (course['price'] as num?)?.toDouble() ?? 0.0;
    final isFree = priceType == 'free' || price == 0;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 150,
        decoration: BoxDecoration(
          color: theme.cardColor,
          border: Border.all(color: cs.outline),
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: theme.shadowColor.withAlpha(102),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(12)),
              child: AspectRatio(
                aspectRatio: 16 / 9,
                child: CustomImage(
                  image: thumbnailUrl,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: theme.textTheme.labelMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                      height: 1.3,
                    ),
                  ),
                  if (instructorName.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(
                      instructorName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.labelSmall?.copyWith(
                        color: cs.onSurfaceVariant,
                      ),
                    ),
                  ],
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      CustomRatingBar(
                        rating: rating,
                        size: 12,
                        iconColor: AppColors.accentStar,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        rating.toStringAsFixed(1),
                        style: TextStyle(
                          fontSize: 11,
                          color: theme.hintColor,
                        ),
                      ),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: isFree
                                ? AppColors.gradientSuccess
                                : AppColors.gradientPrimary,
                          ),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          isFree ? 'Free' : '\$${price.toStringAsFixed(0)}',
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
