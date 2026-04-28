import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/dashboard/data/models/dashboard_dto.dart';
import 'package:everlms/feature/dashboard/data/repositories/dashboard_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return DashboardRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// Dashboard query provider
// ---------------------------------------------------------------------------

/// Fetches the authenticated user's dashboard summary.
/// Invalidated on pull-to-refresh or after enrollment.
final AutoDisposeFutureProvider<DashboardDto> dashboardQueryProvider =
    FutureProvider.autoDispose<DashboardDto>((ref) async {
  return ref.read(dashboardRepositoryProvider).getDashboard();
});

// ---------------------------------------------------------------------------
// Featured courses provider
// ---------------------------------------------------------------------------

/// Fetches featured courses for the horizontal scroll strip.
/// Reuses the course search endpoint with `featured=1`.
final AutoDisposeFutureProvider<List<Map<String, dynamic>>> featuredCoursesQueryProvider =
    FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  try {
    final response = await apiClient.getData(
      'api/courses/search?featured=1&limit=6',
    );
    if (response.statusCode == 200) {
      final body = response.body as Map<String, dynamic>;
      final items = body['items'] as List? ?? [];
      return items.whereType<Map<String, dynamic>>().toList();
    }
  } catch (_) {}
  return [];
});
