import 'package:everlms/feature/root/data/models/common/section.dart';

/// Typed argument wrappers for GoRouter `state.extra`.
///
/// Each class corresponds to one route that needs to pass complex objects.
/// Usage when navigating:
///   context.push(RouteNames.quizStartPage, extra: QuizRouteArgs(quiz: quiz));
/// Usage in screen:
///   final args = state.extra as QuizRouteArgs;

class LearningRouteArgs {
  const LearningRouteArgs({
    required this.lesson,
    this.fromPage,
    this.isOnline = true,
  });
  final dynamic lesson;
  final String? fromPage;
  final bool isOnline;
}

class LandingLearningRouteArgs {
  const LandingLearningRouteArgs({
    required this.indexLesson,
    required this.indexChapter,
    required this.listChapter,
    this.fromPage,
    this.isOnline = true,
  });
  final int indexLesson;
  final int indexChapter;
  final List<Chapter> listChapter;
  final String? fromPage;
  final bool isOnline;
}

/// Generic single-object wrapper (for routes passing one typed model).
class SingleObjectRouteArgs<T> {
  const SingleObjectRouteArgs({required this.value});
  final T value;
}
