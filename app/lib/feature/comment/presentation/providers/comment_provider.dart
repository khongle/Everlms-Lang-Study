import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/comment/data/models/comment_model.dart';
import 'package:everlms/feature/comment/data/repositories/comment_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final commentRepositoryProvider = Provider<CommentRepository>((ref) {
  return CommentRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CommentState {
  const CommentState({
    this.comments = const [],
    this.lessonComments = const [],
    this.isLoading = false,
  });

  final List<CommentModel> comments;
  final List<CommentModel> lessonComments;
  final bool isLoading;

  CommentState copyWith({
    List<CommentModel>? comments,
    List<CommentModel>? lessonComments,
    bool? isLoading,
  }) {
    return CommentState(
      comments: comments ?? this.comments,
      lessonComments: lessonComments ?? this.lessonComments,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class CommentNotifier extends AsyncNotifier<CommentState> {
  @override
  Future<CommentState> build() async {
    return const CommentState();
  }

  CommentRepository get _repo => ref.read(commentRepositoryProvider);

  Future<void> getCommentLesson(String lessonId) async {
    final current = state.valueOrNull ?? const CommentState();
    state = AsyncData(current.copyWith(isLoading: true));

    final comments = await _repo.getCommentLesson(lessonId);
    state = AsyncData(
      current.copyWith(
        lessonComments: comments,
        isLoading: false,
      ),
    );
  }

  Future<void> getComment({
    required String entityId,
    required String entityType,
    required String commentType,
  }) async {
    final current = state.valueOrNull ?? const CommentState();
    state = AsyncData(current.copyWith(isLoading: true));

    final comments = await _repo.getComment(
      entityId: entityId,
      entityType: entityType,
      commentType: commentType,
    );
    state = AsyncData(
      current.copyWith(
        comments: comments,
        isLoading: false,
      ),
    );
  }

  Future<bool> createComment({
    required String commentBody,
    required String entityId,
    required String entityType,
    required String commentType,
  }) async {
    final response = await _repo.createComment(
      commentBody: commentBody,
      entityId: entityId,
      entityType: entityType,
      commentType: commentType,
    );
    if (response != null && response.statusCode == 200) {
      await getComment(
        entityId: entityId,
        entityType: entityType,
        commentType: commentType,
      );
      return true;
    }
    return false;
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final commentProvider = AsyncNotifierProvider<CommentNotifier, CommentState>(
  CommentNotifier.new,
);
