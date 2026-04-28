import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/onboarding/data/models/on_board_model.dart';
import 'package:everlms/feature/onboarding/data/repositories/on_board_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final onBoardRepositoryProvider = Provider<OnBoardRepository>((ref) {
  return OnBoardRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class OnBoardState {
  const OnBoardState({
    this.onBoardList = const [],
    this.pageIndex = 0,
  });

  final List<OnBoardData> onBoardList;
  final int pageIndex;

  OnBoardState copyWith({
    List<OnBoardData>? onBoardList,
    int? pageIndex,
  }) {
    return OnBoardState(
      onBoardList: onBoardList ?? this.onBoardList,
      pageIndex: pageIndex ?? this.pageIndex,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class OnBoardNotifier extends AsyncNotifier<OnBoardState> {
  @override
  Future<OnBoardState> build() async {
    final repo = ref.read(onBoardRepositoryProvider);
    final list = await repo.getOnBoardList();
    return OnBoardState(onBoardList: list);
  }

  void onPageChanged(int index) {
    final current = state.valueOrNull;
    if (current != null) {
      state = AsyncData(current.copyWith(pageIndex: index));
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final onBoardProvider = AsyncNotifierProvider<OnBoardNotifier, OnBoardState>(
  OnBoardNotifier.new,
);
