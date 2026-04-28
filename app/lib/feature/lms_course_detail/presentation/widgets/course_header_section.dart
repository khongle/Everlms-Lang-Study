import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:flutter/material.dart';

class CourseHeaderSection extends StatelessWidget {
  const CourseHeaderSection({required this.course, super.key});

  final CourseDetailDto course;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Banner image
        AspectRatio(
          aspectRatio: 16 / 9,
          child: CustomImage(
            image: course.thumbnailUrl,
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Badges row
              Row(
                children: [
                  if (course.category != null)
                    _Badge(
                      label: course.category!.name,
                      gradient: AppColors.gradientPrimary,
                    ),
                  if (course.level != null) ...[
                    const SizedBox(width: 8),
                    _Badge(
                      label: course.level!.name,
                      gradient: AppColors.gradientInfo,
                    ),
                  ],
                  if (course.bestseller) ...[
                    const SizedBox(width: 8),
                    const _Badge(
                      label: 'Bestseller',
                      gradient: AppColors.gradientDanger,
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 10),
              // Title
              Text(
                course.title,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                  height: 1.3,
                ),
              ),
              if (course.shortDescription.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  course.shortDescription,
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppColors.textSecondary,
                    height: 1.5,
                  ),
                ),
              ],
              const SizedBox(height: 12),
              // Stats
              Wrap(
                spacing: 16,
                runSpacing: 8,
                children: [
                  _Stat(
                    icon: Icons.star_rounded,
                    iconColor: AppColors.accentStar,
                    label:
                        '${course.rating.toStringAsFixed(1)} (${course.ratingCount})',
                  ),
                  _Stat(
                    icon: Icons.people_outline_rounded,
                    label: '${course.studentCount} students',
                  ),
                  _Stat(
                    icon: Icons.play_lesson_outlined,
                    label: '${course.lessonCount} lessons',
                  ),
                  _Stat(
                    icon: Icons.schedule_outlined,
                    label: '${course.durationMinutes} min',
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _Badge extends StatelessWidget {
  const _Badge({required this.label, required this.gradient});

  final String label;
  final List<Color> gradient;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: gradient),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({
    required this.icon,
    required this.label,
    this.iconColor = AppColors.textMuted,
  });

  final IconData icon;
  final String label;
  final Color iconColor;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: iconColor),
        const SizedBox(width: 4),
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            color: AppColors.textMuted,
          ),
        ),
      ],
    );
  }
}
