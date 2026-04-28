import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/dashboard/data/models/dashboard_dto.dart';
import 'package:flutter/material.dart';

/// Card showing an in-progress course with a progress bar and Continue button.
class ContinueLearningCard extends StatelessWidget {
  const ContinueLearningCard({
    required this.course,
    required this.onContinue,
    required this.onCourseTap,
    super.key,
  });

  final InProgressCourseDto course;
  final VoidCallback onContinue;
  final VoidCallback onCourseTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onCourseTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.bgCard,
          border: Border.all(color: AppColors.border),
          borderRadius: BorderRadius.circular(12),
          boxShadow: const [
            BoxShadow(
              color: Color(0x66000000),
              blurRadius: 12,
              offset: Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            // Thumbnail
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: CustomImage(
                image: course.thumbnailUrl,
                width: 80,
                height: 60,
              ),
            ),
            const SizedBox(width: 12),
            // Info + progress
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    course.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textPrimary,
                      height: 1.3,
                    ),
                  ),
                  if (course.lastLessonTitle != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      course.lastLessonTitle!,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                  const SizedBox(height: 8),
                  // Progress bar
                  ClipRRect(
                    borderRadius: BorderRadius.circular(9999),
                    child: LinearProgressIndicator(
                      value: (course.progress / 100).clamp(0.0, 1.0),
                      minHeight: 4,
                      backgroundColor: AppColors.bgTertiary,
                      valueColor: const AlwaysStoppedAnimation<Color>(
                        AppColors.accentPrimary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${course.progress.toInt()}% complete',
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.textMuted,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            // Continue button
            GestureDetector(
              onTap: onContinue,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: AppColors.gradientPrimary,
                  ),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'Continue',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
