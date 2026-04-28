import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:everlms/feature/lms_course_detail/presentation/providers/course_detail_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class StickyBottomBar extends ConsumerWidget {
  const StickyBottomBar({required this.course, super.key});

  final CourseDetailDto course;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final enrollStatus = ref.watch(enrollCommandProvider(course.id));
    final wishlistAsync = ref.watch(wishlistStatusQueryProvider(course.id));
    final isWishlisted = wishlistAsync.valueOrNull ?? false;

    final isLoading = enrollStatus == EnrollStatus.loading;
    final enrollment = course.enrollment;
    final isEnrolled = enrollment?.isActive ?? false;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
      decoration: const BoxDecoration(
        color: AppColors.bgSecondary,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          // Wishlist button
          GestureDetector(
            onTap: () => ref
                .read(wishlistCommandProvider(course.id).notifier)
                .toggle(course.id),
            child: Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: AppColors.bgTertiary,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: Icon(
                isWishlisted ? Icons.favorite : Icons.favorite_border,
                color: isWishlisted
                    ? AppColors.accentDanger
                    : AppColors.textSecondary,
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Main CTA
          Expanded(
            child: _CtaButton(
              course: course,
              isEnrolled: isEnrolled,
              isLoading: isLoading,
              onTap: () {
                if (!isEnrolled && course.isFree) {
                  ref
                      .read(enrollCommandProvider(course.id).notifier)
                      .enrollFree(course.id);
                }
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _CtaButton extends StatelessWidget {
  const _CtaButton({
    required this.course,
    required this.isEnrolled,
    required this.isLoading,
    required this.onTap,
  });

  final CourseDetailDto course;
  final bool isEnrolled;
  final bool isLoading;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    String label;
    List<Color> gradient;
    if (isEnrolled) {
      label = 'Continue Learning';
      gradient = AppColors.gradientSuccess;
    } else if (course.isFree) {
      label = 'Enroll for Free';
      gradient = AppColors.gradientPrimary;
    } else {
      label = 'Buy Now — \$${course.price.toStringAsFixed(0)}';
      gradient = AppColors.gradientDanger;
    }

    return GestureDetector(
      onTap: isLoading ? null : onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        height: 44,
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: gradient),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Center(
          child: isLoading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.white,
                  ),
                )
              : Text(
                  label,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
        ),
      ),
    );
  }
}
