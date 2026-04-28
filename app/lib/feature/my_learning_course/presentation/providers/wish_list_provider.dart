import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/my_learning_course/data/repositories/wish_list_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final wishListRepositoryProvider = Provider<WishListRepository>((ref) {
  return WishListRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class WishListState {
  const WishListState({
    this.courses = const [],
    this.currentPage = 1,
    this.isLoadingMore = false,
  });

  final List<Course> courses;
  final int currentPage;
  final bool isLoadingMore;

  WishListState copyWith({
    List<Course>? courses,
    int? currentPage,
    bool? isLoadingMore,
  }) {
    return WishListState(
      courses: courses ?? this.courses,
      currentPage: currentPage ?? this.currentPage,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class WishListNotifier extends AsyncNotifier<WishListState> {
  @override
  Future<WishListState> build() async {
    final repo = ref.read(wishListRepositoryProvider);
    final courses = await repo.getWishList(1);
    return WishListState(
      courses: courses,
      currentPage: courses.isNotEmpty ? 2 : 1,
    );
  }

  Future<void> loadMore() async {
    final current = state.valueOrNull;
    if (current == null || current.isLoadingMore) return;

    state = AsyncData(current.copyWith(isLoadingMore: true));
    final repo = ref.read(wishListRepositoryProvider);
    final newCourses = await repo.getWishList(current.currentPage);
    state = AsyncData(current.copyWith(
      courses: [...current.courses, ...newCourses],
      currentPage:
          newCourses.isNotEmpty ? current.currentPage + 1 : current.currentPage,
      isLoadingMore: false,
    ),);
  }

  Future<void> addToWishList(int id, String type) async {
    await ref.read(wishListRepositoryProvider).addOrRemoveWishList(id, type);
  }

  Future<void> removeFromWishList(int id, String type) async {
    final message = await ref
        .read(wishListRepositoryProvider)
        .addOrRemoveWishList(id, type);
    if (message != null) {
      // Refresh the list
      state = const AsyncLoading();
      state = await AsyncValue.guard(() async {
        final repo = ref.read(wishListRepositoryProvider);
        final courses = await repo.getWishList(1);
        return WishListState(
          courses: courses,
          currentPage: courses.isNotEmpty ? 2 : 1,
        );
      });
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final wishListProvider = AsyncNotifierProvider<WishListNotifier, WishListState>(
    WishListNotifier.new,);
