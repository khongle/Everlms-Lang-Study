import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/home/data/models/explore.dart';
import 'package:everlms/feature/home/data/repositories/explore_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final exploreRepositoryProvider = Provider<ExploreRepository>((ref) {
  return ExploreRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class ExploreState {
  const ExploreState({this.explore});

  final Explore? explore;

  ExploreState copyWith({Explore? explore}) {
    return ExploreState(explore: explore ?? this.explore);
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class ExploreNotifier extends AsyncNotifier<ExploreState> {
  @override
  Future<ExploreState> build() async {
    final repo = ref.read(exploreRepositoryProvider);
    final explore = await repo.getExploreData();
    return ExploreState(explore: explore);
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(exploreRepositoryProvider);
      final explore = await repo.getExploreData();
      return ExploreState(explore: explore);
    });
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final exploreProvider =
    AsyncNotifierProvider<ExploreNotifier, ExploreState>(ExploreNotifier.new);
