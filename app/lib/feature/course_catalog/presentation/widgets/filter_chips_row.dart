import 'package:everlms/feature/course_catalog/data/models/course_catalog_dto.dart';
import 'package:everlms/feature/course_catalog/presentation/providers/course_list_provider.dart';
import 'package:flutter/material.dart';

class FilterChipsRow extends StatelessWidget {
  const FilterChipsRow({
    required this.categories,
    required this.filter,
    required this.onFilterChanged,
    super.key,
  });

  final List<CategoryDto> categories;
  final CourseFilterParams filter;
  final void Function(CourseFilterParams) onFilterChanged;

  static const List<({int id, String label})> _levels = [
    (id: 1, label: 'Beginner'),
    (id: 2, label: 'Intermediate'),
    (id: 3, label: 'Advanced'),
  ];

  static const List<({String label, String value})> _sorts = [
    (value: 'newest', label: 'Newest'),
    (value: 'popular', label: 'Popular'),
    (value: 'rating', label: 'Top Rated'),
  ];

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 36,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          // All chip (resets filters)
          _Chip(
            label: 'All',
            selected: filter.isEmpty,
            onTap: () => onFilterChanged(const CourseFilterParams()),
          ),
          const SizedBox(width: 8),
          // Category chips
          for (final cat in categories) ...[
            _Chip(
              label: cat.name,
              selected: filter.categoryIds.contains(cat.id),
              onTap: () {
                final ids = List<int>.from(filter.categoryIds);
                if (ids.contains(cat.id)) {
                  ids.remove(cat.id);
                } else {
                  ids.add(cat.id);
                }
                onFilterChanged(filter.copyWith(categoryIds: ids));
              },
            ),
            const SizedBox(width: 8),
          ],
          // Level chips
          for (final level in _levels) ...[
            _Chip(
              label: level.label,
              selected: filter.levelIds.contains(level.id),
              onTap: () {
                final ids = List<int>.from(filter.levelIds);
                if (ids.contains(level.id)) {
                  ids.remove(level.id);
                } else {
                  ids.add(level.id);
                }
                onFilterChanged(filter.copyWith(levelIds: ids));
              },
            ),
            const SizedBox(width: 8),
          ],
          // Price chips
          for (final p in ['free', 'paid']) ...[
            _Chip(
              label: p == 'free' ? 'Free' : 'Paid',
              selected: filter.priceFilters.contains(p),
              onTap: () {
                final prices = List<String>.from(filter.priceFilters);
                if (prices.contains(p)) {
                  prices.remove(p);
                } else {
                  prices.add(p);
                }
                onFilterChanged(filter.copyWith(priceFilters: prices));
              },
            ),
            const SizedBox(width: 8),
          ],
          // Sort chips
          for (final s in _sorts) ...[
            _Chip(
              label: s.label,
              selected: filter.sort == s.value,
              onTap: () => onFilterChanged(filter.copyWith(sort: s.value)),
            ),
            const SizedBox(width: 8),
          ],
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          gradient: selected
              ? const LinearGradient(
                  colors: [Color(0xFF6C63FF), Color(0xFF8B5CF6)],
                )
              : null,
          color: selected ? null : cs.surfaceContainerHighest,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: selected ? Colors.transparent : cs.outline,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: selected ? Colors.white : cs.onSurfaceVariant,
          ),
        ),
      ),
    );
  }
}
