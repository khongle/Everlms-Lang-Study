import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/lesson_view/data/models/lesson_detail_dto.dart';
import 'package:everlms/feature/lesson_view/data/repositories/lesson_repository.dart';
import 'package:everlms/feature/lms_course_detail/presentation/providers/course_detail_providers.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------

final lessonRepositoryProvider = Provider<LessonRepository>(
  (ref) => LessonRepository(ref.watch(apiClientProvider)),
);

// ---------------------------------------------------------------------------
// Lesson detail query
// ---------------------------------------------------------------------------

typedef LessonKey = ({int courseId, int lessonId});

final AutoDisposeFutureProviderFamily<LessonDetailDto, LessonKey> lessonDetailQueryProvider = FutureProvider.autoDispose
    .family<LessonDetailDto, LessonKey>((ref, key) async {
  return ref
      .read(lessonRepositoryProvider)
      .getLessonDetail(key.courseId, key.lessonId);
});

// ---------------------------------------------------------------------------
// Mark complete command
// ---------------------------------------------------------------------------

enum MarkCompleteStatus { idle, loading, success, error }

class MarkCompleteNotifier
    extends AutoDisposeFamilyNotifier<MarkCompleteStatus, LessonKey> {
  @override
  MarkCompleteStatus build(LessonKey arg) => MarkCompleteStatus.idle;

  Future<void> markComplete() async {
    if (state == MarkCompleteStatus.loading) return;
    state = MarkCompleteStatus.loading;
    try {
      await ref
          .read(lessonRepositoryProvider)
          .markComplete(arg.courseId, arg.lessonId);
      state = MarkCompleteStatus.success;
      // Invalidate lesson and course detail to reflect progress changes
      ref.invalidate(lessonDetailQueryProvider(arg));
      ref.invalidate(courseDetailQueryProvider(arg.courseId));
    } on Exception {
      state = MarkCompleteStatus.error;
    }
  }
}

final AutoDisposeNotifierProviderFamily<MarkCompleteNotifier, MarkCompleteStatus, LessonKey> markCompleteProvider = NotifierProvider.autoDispose
    .family<MarkCompleteNotifier, MarkCompleteStatus, LessonKey>(
  MarkCompleteNotifier.new,
);

// ---------------------------------------------------------------------------
// Notes
// ---------------------------------------------------------------------------

class LessonNoteNotifier
    extends AutoDisposeFamilyNotifier<LessonNoteDto?, int> {
  @override
  LessonNoteDto? build(int arg) {
    Future.microtask(_load);
    return null;
  }

  Future<void> _load() async {
    final note = await ref.read(lessonRepositoryProvider).getNote(arg);
    state = note;
  }

  Future<void> save(String content, int timestampSeconds) async {
    await ref
        .read(lessonRepositoryProvider)
        .saveNote(arg, content, timestampSeconds);
    await _load();
  }
}

final AutoDisposeNotifierProviderFamily<LessonNoteNotifier, LessonNoteDto?, int> lessonNoteProvider = NotifierProvider.autoDispose
    .family<LessonNoteNotifier, LessonNoteDto?, int>(
  LessonNoteNotifier.new,
);
