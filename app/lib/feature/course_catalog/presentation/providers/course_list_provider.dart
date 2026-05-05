import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/course_catalog/data/models/course_catalog_dto.dart';
import 'package:everlms/feature/course_catalog/data/repositories/course_catalog_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------

final courseCatalogRepositoryProvider =
    Provider<CourseCatalogRepository>((ref) {
  return CourseCatalogRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// Categories query provider
// ---------------------------------------------------------------------------

final FutureProvider<List<CategoryDto>> categoriesQueryProvider =
    FutureProvider<List<CategoryDto>>((ref) async {
  return ref.read(courseCatalogRepositoryProvider).getCategories();
});

// ---------------------------------------------------------------------------
// Filter params
// ---------------------------------------------------------------------------

class CourseFilterParams {
  const CourseFilterParams({
    this.search = '',
    this.categoryIds = const [],
    this.levelIds = const [],
    this.priceFilters = const [],
    this.sort = 'newest',
  });

  final String search;
  final List<int> categoryIds;
  final List<int> levelIds;
  final List<String> priceFilters;
  final String sort;

  CourseFilterParams copyWith({
    String? search,
    List<int>? categoryIds,
    List<int>? levelIds,
    List<String>? priceFilters,
    String? sort,
  }) {
    return CourseFilterParams(
      search: search ?? this.search,
      categoryIds: categoryIds ?? this.categoryIds,
      levelIds: levelIds ?? this.levelIds,
      priceFilters: priceFilters ?? this.priceFilters,
      sort: sort ?? this.sort,
    );
  }

  bool get isEmpty =>
      search.isEmpty &&
      categoryIds.isEmpty &&
      levelIds.isEmpty &&
      priceFilters.isEmpty;
}

// ---------------------------------------------------------------------------
// Course list state
// ---------------------------------------------------------------------------

class CourseListState {
  const CourseListState({
    this.items = const [],
    this.total = 0,
    this.page = 0,
    this.isInitialLoading = false,
    this.isLoadingMore = false,
    this.isRefreshing = false,
    this.endOfList = false,
    this.filter = const CourseFilterParams(),
    this.errorMessage,
  });

  final List<CourseListItemDto> items;
  final int total;
  final int page;
  final bool isInitialLoading;
  final bool isLoadingMore;
  final bool isRefreshing;
  final bool endOfList;
  final CourseFilterParams filter;
  final String? errorMessage;

  CourseListState copyWith({
    List<CourseListItemDto>? items,
    int? total,
    int? page,
    bool? isInitialLoading,
    bool? isLoadingMore,
    bool? isRefreshing,
    bool? endOfList,
    CourseFilterParams? filter,
    String? errorMessage,
  }) {
    return CourseListState(
      items: items ?? this.items,
      total: total ?? this.total,
      page: page ?? this.page,
      isInitialLoading: isInitialLoading ?? this.isInitialLoading,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      isRefreshing: isRefreshing ?? this.isRefreshing,
      endOfList: endOfList ?? this.endOfList,
      filter: filter ?? this.filter,
      errorMessage: errorMessage,
    );
  }
}

// ---------------------------------------------------------------------------
// Course list notifier
// ---------------------------------------------------------------------------

class CourseListNotifier extends Notifier<CourseListState> {
  // static const int _pageSize = 20;

  @override
  CourseListState build() {
    // Trigger initial load immediately after build.
    Future.microtask(() => _loadPage(0, isRefresh: true));
    return const CourseListState(isInitialLoading: true);
  }

  CourseCatalogRepository get _repo =>
      ref.read(courseCatalogRepositoryProvider);

  Future<void> _loadPage(int page, {bool isRefresh = false}) async {
    if (!isRefresh && state.isLoadingMore) return;

    final filter = state.filter;

    try {
      final response = await _repo.searchCourses(
        search: filter.search.isEmpty ? null : filter.search,
        categoryIds: filter.categoryIds.isEmpty ? null : filter.categoryIds,
        levelIds: filter.levelIds.isEmpty ? null : filter.levelIds,
        priceFilters: filter.priceFilters.isEmpty ? null : filter.priceFilters,
        sort: filter.sort,
        page: page,
      );

      final newItems = isRefresh || page == 0
          ? response.items
          : [...state.items, ...response.items];

      state = state.copyWith(
        items: newItems,
        total: response.total,
        page: page,
        isInitialLoading: false,
        isLoadingMore: false,
        isRefreshing: false,
        endOfList: !response.hasMore,
      );
    } on Exception catch (e) {
      state = state.copyWith(
        isInitialLoading: false,
        isLoadingMore: false,
        isRefreshing: false,
        errorMessage: e.toString(),
      );
    }
  }

  /// Loads the next page. Guards against double-loads.
  Future<void> loadMore() async {
    if (state.isLoadingMore || state.endOfList || state.isRefreshing) return;
    state = state.copyWith(isLoadingMore: true);
    await _loadPage(state.page + 1);
  }

  /// Pull-to-refresh: reloads from page 0.
  Future<void> refresh() async {
    if (state.isRefreshing) return;
    state = state.copyWith(isRefreshing: true, page: 0, endOfList: false);
    await _loadPage(0, isRefresh: true);
  }

  /// Updates search keyword (with 350ms debounce applied in UI).
  Future<void> updateSearch(String search) async {
    state = state.copyWith(
      filter: state.filter.copyWith(search: search),
      isInitialLoading: true,
      page: 0,
      endOfList: false,
      items: const [],
    );
    await _loadPage(0, isRefresh: true);
  }

  /// Updates filter params and reloads from page 0.
  Future<void> updateFilter(CourseFilterParams filter) async {
    state = state.copyWith(
      filter: filter,
      isInitialLoading: true,
      page: 0,
      endOfList: false,
      items: const [],
    );
    await _loadPage(0, isRefresh: true);
  }

  /// Resets all filters.
  Future<void> resetFilters() async {
    await updateFilter(const CourseFilterParams());
  }
}

final courseListProvider =
    NotifierProvider<CourseListNotifier, CourseListState>(
  CourseListNotifier.new,
);
