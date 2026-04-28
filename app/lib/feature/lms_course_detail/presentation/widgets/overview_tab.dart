import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/widgets/expandable_html.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:flutter/material.dart';

class OverviewTab extends StatelessWidget {
  const OverviewTab({required this.course, super.key});

  final CourseDetailDto course;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (course.fullDescription.isNotEmpty) ...[
          ExpandableHtml(data: course.fullDescription),
          const SizedBox(height: 16),
        ],
        if (course.whatYouLearn.isNotEmpty) ...[
          const _SectionTitle(title: "What you'll learn"),
          const SizedBox(height: 8),
          ...course.whatYouLearn.map((item) => _BulletItem(text: item)),
          const SizedBox(height: 16),
        ],
        if (course.prerequisites.isNotEmpty) ...[
          const _SectionTitle(title: 'Prerequisites'),
          const SizedBox(height: 8),
          ...course.prerequisites.map((item) => _BulletItem(text: item)),
          const SizedBox(height: 16),
        ],
        if (course.whoThisIsFor.isNotEmpty) ...[
          const _SectionTitle(title: 'Who this is for'),
          const SizedBox(height: 8),
          ...course.whoThisIsFor.map((item) => _BulletItem(text: item)),
          const SizedBox(height: 16),
        ],
        if (course.instructors.isNotEmpty) ...[
          const _SectionTitle(title: 'Instructor'),
          const SizedBox(height: 12),
          ...course.instructors.map(
            (ins) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: _InstructorCard(instructor: ins),
            ),
          ),
        ],
      ],
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title});
  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.w700,
        color: AppColors.textPrimary,
      ),
    );
  }
}

class _BulletItem extends StatelessWidget {
  const _BulletItem({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.check_circle_outline,
              size: 16, color: AppColors.accentSuccess,),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InstructorCard extends StatelessWidget {
  const _InstructorCard({required this.instructor});
  final CourseInstructorDto instructor;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        CircleAvatar(
          radius: 24,
          backgroundColor: AppColors.bgTertiary,
          backgroundImage: instructor.avatarUrl.isNotEmpty
              ? NetworkImage(instructor.avatarUrl)
              : null,
          child: instructor.avatarUrl.isEmpty
              ? const Icon(Icons.person, color: AppColors.textMuted)
              : null,
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                instructor.name,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
              if (instructor.bio.isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(
                  instructor.bio,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                    height: 1.4,
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}
