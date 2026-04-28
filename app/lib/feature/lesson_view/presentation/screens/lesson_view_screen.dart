import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/lesson_view/data/models/lesson_detail_dto.dart';
import 'package:everlms/feature/lesson_view/presentation/providers/lesson_providers.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/notes_bottom_sheet.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/pdf_lesson_widget.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/quiz_lesson_widget.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/text_lesson_widget.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/video_player_widget.dart';
import 'package:everlms/feature/lesson_view/presentation/widgets/zoom_lesson_widget.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

class LessonViewScreen extends ConsumerWidget {
  const LessonViewScreen({
    required this.courseId,
    required this.lessonId,
    super.key,
  });

  final int courseId;
  final int lessonId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final key = (courseId: courseId, lessonId: lessonId);
    final lessonAsync = ref.watch(lessonDetailQueryProvider(key));

    return lessonAsync.when(
      loading: _LoadingScaffold.new,
      error: (e, _) => Scaffold(
        backgroundColor: AppColors.bgPrimary,
        appBar: AppBar(
          backgroundColor: AppColors.bgSecondary,
          iconTheme: const IconThemeData(color: AppColors.textPrimary),
        ),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline,
                  color: AppColors.accentDanger, size: 48,),
              const SizedBox(height: 12),
              const Text(
                'Failed to load lesson.',
                style: TextStyle(color: AppColors.textSecondary),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.invalidate(lessonDetailQueryProvider(key)),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
      data: (lesson) => _LessonContent(lesson: lesson),
    );
  }
}

class _LessonContent extends ConsumerWidget {
  const _LessonContent({required this.lesson});

  final LessonDetailDto lesson;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final key = (courseId: lesson.courseId, lessonId: lesson.id);
    final markStatus = ref.watch(markCompleteProvider(key));
    final isCompleted =
        lesson.isCompleted || markStatus == MarkCompleteStatus.success;
    final isMarking = markStatus == MarkCompleteStatus.loading;

    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: AppBar(
        backgroundColor: AppColors.bgSecondary,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
        title: Text(
          lesson.courseTitle,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            fontSize: 14,
            color: AppColors.textSecondary,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.menu_book_outlined,
                color: AppColors.textSecondary,),
            onPressed: () {
              // Curriculum drawer placeholder
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Curriculum drawer coming soon')),
              );
            },
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.accentPrimary,
        onPressed: () {
          showModalBottomSheet(
            context: context,
            isScrollControlled: true,
            backgroundColor: Colors.transparent,
            builder: (_) => NotesBottomSheet(
              lessonId: lesson.id,
              currentTimestampSeconds: 0,
            ),
          );
        },
        child: const Icon(Icons.edit_note_rounded, color: Colors.white),
      ),
      body: Column(
        children: [
          // Content area
          Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Lesson content by type
                  _buildContent(),
                  const SizedBox(height: 16),
                  // Lesson title + chapter
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          lesson.chapterTitle,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          lesson.title,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Attachments
                  if (lesson.attachments.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    _AttachmentsSection(attachments: lesson.attachments),
                  ],
                  const SizedBox(height: 80),
                ],
              ),
            ),
          ),
          // Bottom nav: prev / mark complete / next
          _BottomNav(
            lesson: lesson,
            isCompleted: isCompleted,
            isMarking: isMarking,
            onMarkComplete: () =>
                ref.read(markCompleteProvider(key).notifier).markComplete(),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    return switch (lesson.type) {
      'video' => VideoPlayerWidget(videoUrl: lesson.videoUrl),
      'pdf' =>
        SizedBox(height: 500, child: PdfLessonWidget(pdfUrl: lesson.pdfUrl)),
      'quiz' => QuizLessonWidget(lessonId: lesson.id),
      'zoom' => ZoomLessonWidget(lesson: lesson),
      _ => TextLessonWidget(htmlContent: lesson.htmlContent),
    };
  }
}

class _AttachmentsSection extends StatelessWidget {
  const _AttachmentsSection({required this.attachments});

  final List<LessonAttachmentDto> attachments;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Attachments',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          ...attachments.map(
            (a) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: AppColors.bgTertiary,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.attach_file,
                        size: 16, color: AppColors.textMuted,),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        a.name,
                        style: const TextStyle(
                          fontSize: 13,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ),
                    const Icon(Icons.download_outlined,
                        size: 18, color: AppColors.accentPrimary,),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BottomNav extends StatelessWidget {
  const _BottomNav({
    required this.lesson,
    required this.isCompleted,
    required this.isMarking,
    required this.onMarkComplete,
  });

  final LessonDetailDto lesson;
  final bool isCompleted;
  final bool isMarking;
  final VoidCallback onMarkComplete;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 24),
      decoration: const BoxDecoration(
        color: AppColors.bgSecondary,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          // Prev
          _NavButton(
            icon: Icons.arrow_back_ios_new_rounded,
            label: 'Prev',
            enabled: lesson.prevLessonId != null,
            onTap: () => context.pushReplacement(
              '/courses/${lesson.courseId}/lessons/${lesson.prevLessonId}',
            ),
          ),
          const SizedBox(width: 10),
          // Mark complete
          Expanded(
            child: GestureDetector(
              onTap: isCompleted || isMarking ? null : onMarkComplete,
              child: Container(
                height: 42,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isCompleted
                        ? AppColors.gradientSuccess
                        : AppColors.gradientPrimary,
                  ),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: isMarking
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              isCompleted
                                  ? Icons.check_circle_rounded
                                  : Icons.check_rounded,
                              size: 16,
                              color: Colors.white,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              isCompleted ? 'Completed' : 'Mark Complete',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          // Next
          _NavButton(
            icon: Icons.arrow_forward_ios_rounded,
            label: 'Next',
            enabled: lesson.nextLessonId != null,
            onTap: () => context.pushReplacement(
              '/courses/${lesson.courseId}/lessons/${lesson.nextLessonId}',
            ),
          ),
        ],
      ),
    );
  }
}

class _NavButton extends StatelessWidget {
  const _NavButton({
    required this.icon,
    required this.label,
    required this.enabled,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool enabled;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: enabled ? onTap : null,
      child: Container(
        width: 64,
        height: 42,
        decoration: BoxDecoration(
          color: AppColors.bgTertiary,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: Icon(
          icon,
          size: 18,
          color: enabled ? AppColors.textPrimary : AppColors.textMuted,
        ),
      ),
    );
  }
}

class _LoadingScaffold extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: AppBar(
        backgroundColor: AppColors.bgSecondary,
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
      ),
      body: Shimmer.fromColors(
        baseColor: AppColors.bgTertiary,
        highlightColor: AppColors.bgSecondary,
        child: Column(
          children: [
            Container(height: 200, color: AppColors.bgTertiary),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                      height: 20, width: 200, color: AppColors.bgTertiary,),
                  const SizedBox(height: 8),
                  Container(
                      height: 14,
                      width: double.infinity,
                      color: AppColors.bgTertiary,),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
