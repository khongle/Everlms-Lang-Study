import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:flutter/material.dart';

class CurriculumTab extends StatelessWidget {
  const CurriculumTab({
    required this.chapters,
    required this.isEnrolled,
    required this.onLessonTap,
    super.key,
  });

  final List<ChapterDto> chapters;
  final bool isEnrolled;
  final void Function(LessonItemDto lesson) onLessonTap;

  @override
  Widget build(BuildContext context) {
    if (chapters.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Text(
            'No curriculum available.',
            style: TextStyle(color: AppColors.textMuted),
          ),
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: chapters.length,
      itemBuilder: (context, index) => _ChapterExpansion(
        chapter: chapters[index],
        isEnrolled: isEnrolled,
        onLessonTap: onLessonTap,
      ),
    );
  }
}

class _ChapterExpansion extends StatelessWidget {
  const _ChapterExpansion({
    required this.chapter,
    required this.isEnrolled,
    required this.onLessonTap,
  });

  final ChapterDto chapter;
  final bool isEnrolled;
  final void Function(LessonItemDto) onLessonTap;

  @override
  Widget build(BuildContext context) {
    final completedCount = chapter.lessons.where((l) => l.isCompleted).length;

    return Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        initiallyExpanded: true,
        collapsedIconColor: AppColors.textSecondary,
        iconColor: AppColors.accentPrimary,
        tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        title: Text(
          chapter.title,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppColors.textPrimary,
          ),
        ),
        subtitle: Text(
          '$completedCount/${chapter.lessons.length} completed',
          style: const TextStyle(
            fontSize: 11,
            color: AppColors.textMuted,
          ),
        ),
        children: chapter.lessons
            .map((lesson) => _LessonRow(
                  lesson: lesson,
                  isEnrolled: isEnrolled,
                  onTap: () => onLessonTap(lesson),
                ),)
            .toList(),
      ),
    );
  }
}

class _LessonRow extends StatelessWidget {
  const _LessonRow({
    required this.lesson,
    required this.isEnrolled,
    required this.onTap,
  });

  final LessonItemDto lesson;
  final bool isEnrolled;
  final VoidCallback onTap;

  IconData _typeIcon(String type) {
    return switch (type) {
      'video' => Icons.play_circle_outline_rounded,
      'pdf' => Icons.picture_as_pdf_outlined,
      'quiz' => Icons.quiz_outlined,
      'zoom' => Icons.video_camera_front_outlined,
      _ => Icons.article_outlined,
    };
  }

  @override
  Widget build(BuildContext context) {
    final locked = lesson.isLocked && !isEnrolled && !lesson.isFreePreview;
    return InkWell(
      onTap: locked ? null : onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        child: Row(
          children: [
            Icon(
              _typeIcon(lesson.type),
              size: 18,
              color: lesson.isCompleted
                  ? AppColors.accentSuccess
                  : AppColors.textSecondary,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    lesson.title,
                    style: TextStyle(
                      fontSize: 13,
                      color:
                          locked ? AppColors.textMuted : AppColors.textPrimary,
                    ),
                  ),
                  if (lesson.durationMinutes > 0)
                    Text(
                      '${lesson.durationMinutes} min',
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.textMuted,
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            if (lesson.isCompleted)
              const Icon(Icons.check_circle_rounded,
                  size: 16, color: AppColors.accentSuccess,)
            else if (lesson.isFreePreview && !isEnrolled)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.accentPrimary),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: const Text(
                  'Preview',
                  style: TextStyle(
                    fontSize: 10,
                    color: AppColors.accentPrimary,
                  ),
                ),
              )
            else if (locked)
              const Icon(Icons.lock_outline,
                  size: 16, color: AppColors.textMuted,),
          ],
        ),
      ),
    );
  }
}
