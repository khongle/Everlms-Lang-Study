import 'dart:async';

import 'package:everlms/feature/dashboard/presentation/providers/dashboard_query_provider.dart';
import 'package:everlms/feature/notification/presentation/providers/notification_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Polls the unread notification count every 2 minutes.
/// Reuses the existing [notificationRepositoryProvider].
final StreamProvider<int> notificationBadgeProvider = StreamProvider<int>((ref) async* {
  final repo = ref.read(notificationRepositoryProvider);

  // Emit immediately, then every 2 minutes.
  yield await repo.getUnreadCount();
  await for (final _ in Stream.periodic(const Duration(minutes: 2))) {
    yield await repo.getUnreadCount();
  }
});

/// Invalidates badge and dashboard when a notification action occurs.
void invalidateNotificationProviders(Ref ref) {
  ref.invalidate(notificationBadgeProvider);
  ref.invalidate(dashboardQueryProvider);
}
