import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:everlms/feature/lms_course_detail/data/repositories/lms_course_detail_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------

final lmsCourseDetailRepositoryProvider =
    Provider<LmsCourseDetailRepository>((ref) {
  return LmsCourseDetailRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// Course detail query
// ---------------------------------------------------------------------------

final AutoDisposeFutureProviderFamily<CourseDetailDto, int> courseDetailQueryProvider =
    FutureProvider.autoDispose.family<CourseDetailDto, int>((ref, courseId) {
  return ref.read(lmsCourseDetailRepositoryProvider).getCourseDetail(courseId);
});

// ---------------------------------------------------------------------------
// Wishlist
// ---------------------------------------------------------------------------

final AutoDisposeFutureProviderFamily<bool, int> wishlistStatusQueryProvider =
    FutureProvider.autoDispose.family<bool, int>((ref, courseId) {
  return ref
      .read(lmsCourseDetailRepositoryProvider)
      .getWishlistStatus(courseId);
});

class WishlistCommandNotifier extends AutoDisposeFamilyNotifier<bool, int> {
  @override
  bool build(int arg) => false; // initial — overridden on first query watch

  Future<void> toggle(int courseId) async {
    final prev = state;
    state = !prev; // optimistic
    try {
      final result = await ref
          .read(lmsCourseDetailRepositoryProvider)
          .toggleWishlist(courseId);
      state = result;
    } on Exception {
      state = prev; // revert on error
    }
  }
}

final AutoDisposeNotifierProviderFamily<WishlistCommandNotifier, bool, int> wishlistCommandProvider = NotifierProvider.autoDispose
    .family<WishlistCommandNotifier, bool, int>(WishlistCommandNotifier.new);

// ---------------------------------------------------------------------------
// Enroll command
// ---------------------------------------------------------------------------

enum EnrollStatus { idle, loading, success, error }

class EnrollCommandNotifier
    extends AutoDisposeFamilyNotifier<EnrollStatus, int> {
  @override
  EnrollStatus build(int arg) => EnrollStatus.idle;

  Future<void> enrollFree(int courseId) async {
    if (state == EnrollStatus.loading) return;
    state = EnrollStatus.loading;
    try {
      await ref.read(lmsCourseDetailRepositoryProvider).enrollFree(courseId);
      state = EnrollStatus.success;
      // Refresh course detail to reflect enrollment
      ref.invalidate(courseDetailQueryProvider(courseId));
    } on Exception {
      state = EnrollStatus.error;
    }
  }
}

final AutoDisposeNotifierProviderFamily<EnrollCommandNotifier, EnrollStatus, int> enrollCommandProvider = NotifierProvider.autoDispose
    .family<EnrollCommandNotifier, EnrollStatus, int>(
  EnrollCommandNotifier.new,
);
