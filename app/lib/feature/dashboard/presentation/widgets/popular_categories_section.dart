import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/course_catalog/data/models/course_catalog_dto.dart';
import 'package:flutter/material.dart';

class PopularCategoriesSection extends StatelessWidget {
  const PopularCategoriesSection({
    required this.categories,
    required this.onCategoryTap,
    super.key,
  });

  final List<CategoryDto> categories;
  final void Function(CategoryDto category) onCategoryTap;

  @override
  Widget build(BuildContext context) {
    final items = categories.take(6).toList(growable: false);
    if (items.isEmpty) return const SizedBox.shrink();

    return SizedBox(
      height: 156,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        scrollDirection: Axis.horizontal,
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final category = items[index];
          return _CategoryCard(
            category: category,
            onTap: () => onCategoryTap(category),
          );
        },
      ),
    );
  }
}

class _CategoryCard extends StatelessWidget {
  const _CategoryCard({
    required this.category,
    required this.onTap,
  });

  final CategoryDto category;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cs = theme.colorScheme;

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Ink(
        width: 128,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: theme.cardColor,
          border: Border.all(color: cs.outline),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: SizedBox(
                height: 72,
                width: double.infinity,
                child: category.thumbnailUrl.isNotEmpty
                    ? CustomImage(image: category.thumbnailUrl)
                    : Container(
                        color: cs.surfaceContainerHighest,
                        alignment: Alignment.center,
                        child: Icon(
                          Icons.auto_stories_outlined,
                          color: cs.primary,
                          size: 28,
                        ),
                      ),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              category.name,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: theme.textTheme.titleSmall,
            ),
            const Spacer(),
            Text(
              '${category.courseCount} courses',
              style: theme.textTheme.labelSmall?.copyWith(
                color: theme.hintColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
