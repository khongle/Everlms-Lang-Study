import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/membership/data/models/membership_model.dart';
import 'package:everlms/feature/membership/data/repositories/membership_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final membershipRepositoryProvider = Provider<MembershipRepository>((ref) {
  return MembershipRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class MembershipState {
  const MembershipState({this.memberships = const []});

  final List<MembershipModel> memberships;

  MembershipState copyWith({List<MembershipModel>? memberships}) {
    return MembershipState(memberships: memberships ?? this.memberships);
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class MembershipNotifier extends AsyncNotifier<MembershipState> {
  @override
  Future<MembershipState> build() async {
    final repo = ref.read(membershipRepositoryProvider);
    final list = await repo.getMembershipList();
    return MembershipState(memberships: list);
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(membershipRepositoryProvider);
      final list = await repo.getMembershipList();
      return MembershipState(memberships: list);
    });
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final membershipProvider =
    AsyncNotifierProvider<MembershipNotifier, MembershipState>(
  MembershipNotifier.new,
);
