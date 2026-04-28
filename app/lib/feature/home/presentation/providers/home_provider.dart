import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/enum.dart';
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
  });

  final List<Course> allCourses;
  final List<Course> myCourses;
  final Set<String> categoryCourses;
  final bool isLoadingCourses;
  final bool isLoadingMyCourses;

  HomeState copyWith({
    List<Course>? allCourses,
    List<Course>? myCourses,
    Set<String>? categoryCourses,
    bool? isLoadingCourses,
    bool? isLoadingMyCourses,
  }) {
    return HomeState(
      allCourses: allCourses ?? this.allCourses,
      myCourses: myCourses ?? this.myCourses,
      categoryCourses: categoryCourses ?? this.categoryCourses,
      isLoadingCourses: isLoadingCourses ?? this.isLoadingCourses,
      isLoadingMyCourses: isLoadingMyCourses ?? this.isLoadingMyCourses,
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
    final uid = repo.getUserUid();
    if (uid.isEmpty) return const HomeState();

    final courses = await repo.getCourseNotPurchase(uid);
    final categories = <String>{};
    for (final c in courses) {
      if (c.fieldCategory != null && c.fieldCategory!.isNotEmpty) {
        categories.add(c.fieldCategory!);
      }
    }
    return HomeState(
      allCourses: courses,
      categoryCourses: categories,
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
    final uid = repo.getUserUid();
    if (uid.isEmpty) return;

    final courses = await repo.getCourseNotPurchase(uid);
    final categories = <String>{};
    for (final c in courses) {
      if (c.fieldCategory != null && c.fieldCategory!.isNotEmpty) {
        categories.add(c.fieldCategory!);
      }
    }
    final current = state.valueOrNull ?? const HomeState();
    state = AsyncData(
      current.copyWith(
        allCourses: courses,
        categoryCourses: categories,
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
