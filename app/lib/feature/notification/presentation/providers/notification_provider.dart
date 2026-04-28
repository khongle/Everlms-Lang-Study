import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/notification/data/models/notification_model.dart';
import 'package:everlms/feature/notification/data/repositories/notification_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final notificationRepositoryProvider = Provider<NotificationRepository>((ref) {
  return NotificationRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class NotificationState {
  const NotificationState({
    this.notificationList = const [],
  });

  final List<CustomNotification> notificationList;

  NotificationState copyWith({
    List<CustomNotification>? notificationList,
  }) {
    return NotificationState(
      notificationList: notificationList ?? this.notificationList,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class NotificationNotifier extends AsyncNotifier<NotificationState> {
  @override
  Future<NotificationState> build() async {
    final repo = ref.read(notificationRepositoryProvider);
    final list = await repo.getNotificationList();
    return NotificationState(notificationList: list);
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(notificationRepositoryProvider);
      final list = await repo.getNotificationList();
      return NotificationState(notificationList: list);
    });
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final notificationProvider =
    AsyncNotifierProvider<NotificationNotifier, NotificationState>(
  NotificationNotifier.new,
);
