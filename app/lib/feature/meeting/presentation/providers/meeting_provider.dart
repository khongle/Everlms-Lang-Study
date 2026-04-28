import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/meeting/data/models/meeting_model.dart';
import 'package:everlms/feature/meeting/data/repositories/meeting_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final meetingRepositoryProvider = Provider<MeetingRepository>((ref) {
  return MeetingRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class MeetingState {
  const MeetingState({
    this.meetingList = const [],
    this.isLoading = false,
  });

  final List<MyMeeting> meetingList;
  final bool isLoading;

  MeetingState copyWith({
    List<MyMeeting>? meetingList,
    bool? isLoading,
  }) {
    return MeetingState(
      meetingList: meetingList ?? this.meetingList,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class MeetingNotifier extends AsyncNotifier<MeetingState> {
  @override
  Future<MeetingState> build() async {
    return const MeetingState();
  }

  MeetingRepository get _repo => ref.read(meetingRepositoryProvider);

  Future<void> getMeetingList() async {
    final current = state.valueOrNull ?? const MeetingState();
    state = AsyncData(current.copyWith(isLoading: true));

    final response = await _repo.getMeetingList();
    if (response != null && response.statusCode == 200) {
      final model =
          MeetingModel.fromJson(response.body as Map<String, dynamic>);
      state = AsyncData(
        current.copyWith(
          meetingList: model.data?.myMeeting ?? [],
          isLoading: false,
        ),
      );
    } else {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final meetingProvider = AsyncNotifierProvider<MeetingNotifier, MeetingState>(
  MeetingNotifier.new,
);
