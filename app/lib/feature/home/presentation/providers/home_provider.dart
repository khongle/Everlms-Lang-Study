import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/enum.dart';
import 'package:everlms/feature/home/data/models/course_search_result.dart';
import 'package:everlms/feature/home/data/models/dashboard_summary.dart';
import 'package:everlms/feature/home/data/repositories/home_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final homeRepositoryProvider = Provider<HomeRepository>((ref) {
  return HomeRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// Highlight group data
// ---------------------------------------------------------------------------
class HighlightGroup {
  const HighlightGroup({
    required this.title,
    required this.courses,
    this.axis = Axis.vertical,
  });
  final String title;
  final List<Course> courses;
  final Axis axis;
}

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class HomeState {
  const HomeState({
    this.allCourses = const [],
    this.myCourses = const [],
    this.categoryCourses = const {},
    this.isLoadingCourses = false,
    this.isLoadingMyCourses = false,
    // phase-01
    this.dashboardSummary,
    this.featuredCourses = const [],
    this.isLoadingDashboard = false,
  });

  final List<Course> allCourses;
  final List<Course> myCourses;
  final Set<String> categoryCourses;
  final bool isLoadingCourses;
  final bool isLoadingMyCourses;

  // phase-01 fields
  final DashboardSummary? dashboardSummary;
  final List<CourseSearchItem> featuredCourses;
  final bool isLoadingDashboard;

  HomeState copyWith({
    List<Course>? allCourses,
    List<Course>? myCourses,
    Set<String>? categoryCourses,
    bool? isLoadingCourses,
    bool? isLoadingMyCourses,
    DashboardSummary? dashboardSummary,
    List<CourseSearchItem>? featuredCourses,
    bool? isLoadingDashboard,
  }) {
    return HomeState(
      allCourses: allCourses ?? this.allCourses,
      myCourses: myCourses ?? this.myCourses,
      categoryCourses: categoryCourses ?? this.categoryCourses,
      isLoadingCourses: isLoadingCourses ?? this.isLoadingCourses,
      isLoadingMyCourses: isLoadingMyCourses ?? this.isLoadingMyCourses,
      dashboardSummary: dashboardSummary ?? this.dashboardSummary,
      featuredCourses: featuredCourses ?? this.featuredCourses,
      isLoadingDashboard: isLoadingDashboard ?? this.isLoadingDashboard,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class HomeNotifier extends AsyncNotifier<HomeState> {
  @override
  Future<HomeState> build() async {
    final repo = ref.read(homeRepositoryProvider);

    // Load phase-01 data in parallel. Legacy getCourseNotPurchase is NOT called
    // here because the old lms-* endpoints return 401 when a Bearer token is
    // present. Use refreshAllCourses() only when explicitly needed.
    final results = await Future.wait([
      repo.getDashboard(),
      repo.searchCourses(featured: true),
    ]);

    final dashboard = results[0] as DashboardSummary?;
    final searchResult = results[1]! as CourseSearchResult;

    return HomeState(
      dashboardSummary: dashboard,
      featuredCourses: searchResult.items,
    );
  }

  Future<void> loadDashboard() async {
    final current = state.valueOrNull ?? const HomeState();
    state = AsyncData(current.copyWith(isLoadingDashboard: true));

    final repo = ref.read(homeRepositoryProvider);
    final dashboard = await repo.getDashboard();
    state = AsyncData(
      current.copyWith(
        dashboardSummary: dashboard,
        isLoadingDashboard: false,
      ),
    );
  }

  Future<void> loadMyCourses() async {
    final current = state.valueOrNull ?? const HomeState();
    state = AsyncData(current.copyWith(isLoadingMyCourses: true));

    final repo = ref.read(homeRepositoryProvider);
    final uid = repo.getUserUid();
    if (uid.isEmpty) {
      state = AsyncData(current.copyWith(isLoadingMyCourses: false));
      return;
    }

    final courses = await repo.getCourseAccess(uid);
    state = AsyncData(
      current.copyWith(
        myCourses: courses,
        isLoadingMyCourses: false,
      ),
    );
  }

  Future<void> refreshAllCourses() async {
    final repo = ref.read(homeRepositoryProvider);

    final results = await Future.wait([
      repo.getDashboard(),
      repo.searchCourses(featured: true),
    ]);

    final dashboard = results[0] as DashboardSummary?;
    final searchResult = results[1]! as CourseSearchResult;

    final current = state.valueOrNull ?? const HomeState();
    state = AsyncData(
      current.copyWith(
        dashboardSummary: dashboard,
        featuredCourses: searchResult.items,
      ),
    );
  }

  List<HighlightGroup> getHighlightGroups() {
    final allCourses = state.valueOrNull?.allCourses ?? [];
    final highlightRules = <(HighlightCourse, bool Function(Course), Axis)>[
      (HighlightCourse.featured, (c) => c.isFeature == true, Axis.vertical),
      (
        HighlightCourse.bestseller,
        (c) => c.isBestseller == true,
        Axis.horizontal,
      ),
      (
        HighlightCourse.learnIn2h,
        (c) => (double.parse(c.fieldTotalHours ?? '0.0')) == 2.0,
        Axis.vertical,
      ),
      (HighlightCourse.free, (c) => c.isFree == true, Axis.horizontal),
    ];

    final otherCourses = List<Course>.from(allCourses);
    final groups = <HighlightGroup>[];

    for (final (highlight, filter, axis) in highlightRules) {
      final list = allCourses.where(filter).toList();
      otherCourses.removeWhere(filter);
      if (list.isNotEmpty) {
        groups.add(
          HighlightGroup(
            title: highlight.name,
            courses: list,
            axis: axis,
          ),
        );
      }
    }

    if (otherCourses.isNotEmpty) {
      groups.add(
        HighlightGroup(
          title: 'Other course',
          courses: otherCourses,
        ),
      );
    }

    return groups;
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final homeProvider =
    AsyncNotifierProvider<HomeNotifier, HomeState>(HomeNotifier.new);
