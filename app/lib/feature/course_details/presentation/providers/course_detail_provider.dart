import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/course_details/data/models/mobile_course_detail.dart';
import 'package:everlms/feature/course_details/data/models/user_review/review.dart';
import 'package:everlms/feature/course_details/data/repositories/course_detail_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final courseDetailRepositoryProvider = Provider<CourseDetailRepository>((ref) {
  return CourseDetailRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CourseDetailState {
  const CourseDetailState({
    this.course,
    this.chapters = const [],
    this.reviews = const [],
    this.isLoadingChapter = false,
    this.isLoadingReviews = false,
    this.hasMoreReviews = true,
    this.reviewPage = 1,
    this.hasPermissionCourse,
    this.hasPermissionLesson,
    this.isBought,
    this.isStartedCourse,
    this.rating = 0.0,
    // phase-01
    this.mobileCourseDetail,
    this.isWishlisted = false,
    this.isLoadingCourseDetail = false,
    this.isEnrolling = false,
  });

  final Course? course;
  final List<Chapter> chapters;
  final List<Review> reviews;
  final bool isLoadingChapter;
  final bool isLoadingReviews;
  final bool hasMoreReviews;
  final int reviewPage;
  final bool? hasPermissionCourse;
  final bool? hasPermissionLesson;
  final bool? isBought;
  final bool? isStartedCourse;
  final double rating;

  // phase-01 fields
  final MobileCourseDetail? mobileCourseDetail;
  final bool isWishlisted;
  final bool isLoadingCourseDetail;
  final bool isEnrolling;

  CourseDetailState copyWith({
    Course? course,
    List<Chapter>? chapters,
    List<Review>? reviews,
    bool? isLoadingChapter,
    bool? isLoadingReviews,
    bool? hasMoreReviews,
    int? reviewPage,
    bool? hasPermissionCourse,
    bool? hasPermissionLesson,
    bool? isBought,
    bool? isStartedCourse,
    double? rating,
    MobileCourseDetail? mobileCourseDetail,
    bool? isWishlisted,
    bool? isLoadingCourseDetail,
    bool? isEnrolling,
  }) {
    return CourseDetailState(
      course: course ?? this.course,
      chapters: chapters ?? this.chapters,
      reviews: reviews ?? this.reviews,
      isLoadingChapter: isLoadingChapter ?? this.isLoadingChapter,
      isLoadingReviews: isLoadingReviews ?? this.isLoadingReviews,
      hasMoreReviews: hasMoreReviews ?? this.hasMoreReviews,
      reviewPage: reviewPage ?? this.reviewPage,
      hasPermissionCourse: hasPermissionCourse ?? this.hasPermissionCourse,
      hasPermissionLesson: hasPermissionLesson ?? this.hasPermissionLesson,
      isBought: isBought ?? this.isBought,
      isStartedCourse: isStartedCourse ?? this.isStartedCourse,
      rating: rating ?? this.rating,
      mobileCourseDetail: mobileCourseDetail ?? this.mobileCourseDetail,
      isWishlisted: isWishlisted ?? this.isWishlisted,
      isLoadingCourseDetail:
          isLoadingCourseDetail ?? this.isLoadingCourseDetail,
      isEnrolling: isEnrolling ?? this.isEnrolling,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier (Family — keyed by course ID)
// ---------------------------------------------------------------------------
class CourseDetailNotifier extends FamilyAsyncNotifier<CourseDetailState, int> {
  bool _looksLikeChapter(Map<dynamic, dynamic> map) {
    return map.containsKey('field_lesson') ||
        map.containsKey('lms_chapter_id') ||
        map.containsKey('lesson_count') ||
        map.containsKey('progress');
  }

  void _collectChapterMaps(dynamic rawData, List<Map<String, dynamic>> output) {
    if (rawData is Map) {
      if (_looksLikeChapter(rawData)) {
        output.add(Map<String, dynamic>.from(rawData));
        return;
      }
      for (final value in rawData.values) {
        _collectChapterMaps(value, output);
      }
      return;
    }

    if (rawData is List) {
      for (final item in rawData) {
        _collectChapterMaps(item, output);
      }
    }
  }

  Chapter? _parseSingleChapter(Map<String, dynamic> chapterMap) {
    try {
      return Chapter.fromJson(chapterMap);
    } on Object {
      try {
        return Chapter.fromJsonOffline(chapterMap);
      } on Object {
        return null;
      }
    }
  }

  List<Chapter> _parseChaptersFromResponse(dynamic rawData) {
    final rawChapters = <Map<String, dynamic>>[];
    _collectChapterMaps(rawData, rawChapters);
    final chapters = <Chapter>[];
    for (final chapterMap in rawChapters) {
      final chapter = _parseSingleChapter(chapterMap);
      if (chapter != null) {
        chapters.add(chapter);
      }
    }
    return chapters;
  }

  @override
  Future<CourseDetailState> build(int arg) async {
    return const CourseDetailState();
  }

  Future<void> loadChapters(Course course) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(
      current.copyWith(
        course: course,
        isLoadingChapter: true,
      ),
    );

    final repo = ref.read(courseDetailRepositoryProvider);
    final response = await repo.getListChapter('${course.id}');

    var chapters = <Chapter>[];
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      chapters = _parseChaptersFromResponse(data);
    }

    if (chapters.isEmpty) {
      final fallback = await repo.getUserChapter('${course.id ?? 0}');
      if (fallback != null && fallback.statusCode == 200) {
        chapters = _parseChaptersFromResponse(fallback.body['data']);
      }
    }

    final latest = state.valueOrNull ?? const CourseDetailState();

    state = AsyncData(
      latest.copyWith(
        course: course,
        chapters: chapters,
        isLoadingChapter: false,
      ),
    );
  }

  Future<void> checkCoursePermission(String courseId) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    final response = await repo.lmsCheckPermission(courseId: courseId);
    final hasPermission =
        response?.body?['result'] == true || response?.body?['data'] == true;
    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(latest.copyWith(hasPermissionCourse: hasPermission));
  }

  Future<void> loadReviews(int courseId) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(current.copyWith(isLoadingReviews: true));

    final repo = ref.read(courseDetailRepositoryProvider);
    final response = await repo.getReviews(courseId, 1);

    final reviews = <Review>[];
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      if (data is List) {
        reviews.addAll(data.map((e) => Review.fromJson(e)));
      }
    }

    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(
      latest.copyWith(
        reviews: reviews,
        reviewPage: 2,
        isLoadingReviews: false,
      ),
    );
  }

  Future<void> postReview({
    required int courseId,
    required String review,
    required double rating,
  }) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    await repo.postReview(id: courseId, review: review, rating: rating);
    await loadReviews(courseId);
  }

  void updateRating(double value) {
    final current = state.valueOrNull;
    if (current == null) return;
    state = AsyncData(current.copyWith(rating: value));
  }

  Future<void> lmsUpdateStatusLesson(String lessonId) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    await repo.lmsUpdateStatusLesson(lessonId: lessonId);
  }

  Future<void> lmsCheckCoursePayment(String productId) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    final response = await repo.lmsCheckCoursePayment(productId: productId);
    final bought = (response?.body['course_permission'] ?? false) as bool;
    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(latest.copyWith(isBought: bought));
  }

  Future<void> checkStartCourse(String courseId) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    final res = await repo.lmsUserCourseCheck(courseId);
    final isStarted = res?.body['data'] is! List;
    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(latest.copyWith(isStartedCourse: isStarted));
  }

  Future<void> startCourse(String productId) async {
    final repo = ref.read(courseDetailRepositoryProvider);
    await repo.startCourse(productId);
  }

  Future<void> checkListLessonPermission(String courseId) async {
    final current = state.valueOrNull;
    if (current == null) return;
    final repo = ref.read(courseDetailRepositoryProvider);
    for (final chapter in current.chapters) {
      for (final lesson in chapter.lessons ?? []) {
        final res = await repo.lmsCheckPermission(
          lessonId: lesson.id.toString(),
          courseId: courseId,
        );
        lesson.hasPermission = res?.body['result'] ?? false;
      }
    }
    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(latest.copyWith(chapters: List.from(current.chapters)));
  }

  Future<void> getUserChapter(Course course) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(
      current.copyWith(course: course, isLoadingChapter: true),
    );

    final repo = ref.read(courseDetailRepositoryProvider);
    final response = await repo.getUserChapter('${course.id ?? 0}');

    var chapters = <Chapter>[];
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      chapters = _parseChaptersFromResponse(data);
    }

    if (chapters.isEmpty) {
      final fallback = await repo.getListChapter('${course.id ?? 0}');
      if (fallback != null && fallback.statusCode == 200) {
        chapters = _parseChaptersFromResponse(fallback.body['data']);
      }
    }

    for (final chapter in chapters) {
      for (final lesson in chapter.lessons ?? []) {
        lesson.whatWillYouLearn = course.fieldWhatYouLearn ?? '';
        lesson.description = course.fieldBody ?? '';
      }
    }

    final updatedCourse = course..listChapters = chapters;
    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(
      latest.copyWith(
        course: updatedCourse,
        chapters: chapters,
        isLoadingChapter: false,
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Mobile API — phase-01
  // ---------------------------------------------------------------------------

  /// Load full course detail from GET /api/v1/courses/:id.
  Future<void> loadCourseDetail(int courseId) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(current.copyWith(isLoadingCourseDetail: true));

    final repo = ref.read(courseDetailRepositoryProvider);
    final detail = await repo.getCourseDetail('$courseId');

    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(
      latest.copyWith(
        mobileCourseDetail: detail,
        isWishlisted: detail?.isWishlisted ?? latest.isWishlisted,
        isLoadingCourseDetail: false,
      ),
    );
  }

  /// Enroll current user in a free course.
  Future<bool> enrollFree(int courseId) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(current.copyWith(isEnrolling: true));

    final repo = ref.read(courseDetailRepositoryProvider);
    final success = await repo.enrollFree('$courseId');

    final latest = state.valueOrNull ?? const CourseDetailState();
    state = AsyncData(latest.copyWith(isEnrolling: false));

    if (success) {
      // Refresh detail so enrollment status is up to date.
      await loadCourseDetail(courseId);
    }
    return success;
  }

  /// Toggle wishlist for a course; optimistically updates local state.
  Future<void> toggleWishlist(int courseId) async {
    final current = state.valueOrNull ?? const CourseDetailState();
    final optimistic = !current.isWishlisted;
    state = AsyncData(current.copyWith(isWishlisted: optimistic));

    final repo = ref.read(courseDetailRepositoryProvider);
    final result = await repo.toggleWishlist(courseId);

    final latest = state.valueOrNull ?? const CourseDetailState();
    // If server returns a definitive value, use it; otherwise keep optimistic.
    state = AsyncData(
      latest.copyWith(isWishlisted: result ?? optimistic),
    );
  }

  void calculatorProgressCourse() {
    final current = state.valueOrNull;
    if (current == null) return;
    final chapters = current.chapters;
    var totalLesson = 0;
    var completeLesson = 0;
    for (final chapter in chapters) {
      totalLesson += chapter.lessons?.length ?? 0;
      completeLesson +=
          chapter.lessons?.where((e) => e.isCompleted).length ?? 0;
    }
    if (totalLesson != 0) {
      final progress = completeLesson / totalLesson;
      final updatedCourse = current.course?..progress = progress;
      state = AsyncData(current.copyWith(course: updatedCourse));
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final courseDetailProvider =
    AsyncNotifierProviderFamily<CourseDetailNotifier, CourseDetailState, int>(
  CourseDetailNotifier.new,
);
