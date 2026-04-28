import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/main.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Override at app startup via `ProviderScope.overrides`.
final sharedPreferencesProvider = Provider<SharedPreferences>(
  (_) => throw UnimplementedError(
    'sharedPreferencesProvider must be overridden in ProviderScope',
  ),
);

/// Dio-based [ApiClient] — depends on [sharedPreferencesProvider].
final apiClientProvider = Provider<ApiClient>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ApiClient(
    appBaseUrl: Config.baseUrl,
    sharedPreferences: prefs,
    auth: basicAuthWeb,
  );
});
