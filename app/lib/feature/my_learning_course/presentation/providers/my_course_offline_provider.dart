import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/my_learning_course/data/repositories/my_course_offline_repository.dart';
import 'package:everlms/feature/root/data/models/common/my_course_offline.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final myCourseOfflineRepositoryProvider =
    Provider<MyCourseOfflineRepository>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return MyCourseOfflineRepositoryImpl(prefs);
});

// ---------------------------------------------------------------------------
// myCourseOfflineProvider — downloaded JSON courses (MyCourseOfflineScreen)
// ---------------------------------------------------------------------------

final myCourseOfflineProvider =
    AsyncNotifierProvider<MyCourseOfflineNotifier, List<MyCourseOffline>>(
  MyCourseOfflineNotifier.new,
);

class MyCourseOfflineNotifier extends AsyncNotifier<List<MyCourseOffline>> {
  @override
  Future<List<MyCourseOffline>> build() => _load();

  Future<List<MyCourseOffline>> _load() =>
      ref.read(myCourseOfflineRepositoryProvider).getDownloadedCourseList();

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(_load);
  }
}

// ---------------------------------------------------------------------------
// scormCourseListProvider — SCORM courses (DownloadedCourseList widget)
// ---------------------------------------------------------------------------

final scormCourseListProvider =
    AsyncNotifierProvider<ScormCourseListNotifier, List<MyCourseOffline>>(
  ScormCourseListNotifier.new,
);

class ScormCourseListNotifier extends AsyncNotifier<List<MyCourseOffline>> {
  @override
  Future<List<MyCourseOffline>> build() => _load();

  Future<List<MyCourseOffline>> _load() =>
      ref.read(myCourseOfflineRepositoryProvider).getScormCourseList();

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(_load);
  }
}
