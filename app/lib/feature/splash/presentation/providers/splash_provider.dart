import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/splash/data/models/config_model.dart';
import 'package:everlms/feature/splash/data/repositories/splash_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final splashRepositoryProvider = Provider<SplashRepository>((ref) {
  return SplashRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class SplashState {
  const SplashState({
    this.configModel,
    this.hasConnection = true,
    this.isLoading = false,
    this.isSplashSeen = false,
  });

  final ConfigModel? configModel;
  final bool hasConnection;
  final bool isLoading;
  final bool isSplashSeen;

  SplashState copyWith({
    ConfigModel? configModel,
    bool? hasConnection,
    bool? isLoading,
    bool? isSplashSeen,
  }) {
    return SplashState(
      configModel: configModel ?? this.configModel,
      hasConnection: hasConnection ?? this.hasConnection,
      isLoading: isLoading ?? this.isLoading,
      isSplashSeen: isSplashSeen ?? this.isSplashSeen,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class SplashNotifier extends AsyncNotifier<SplashState> {
  @override
  Future<SplashState> build() async {
    final repo = ref.read(splashRepositoryProvider);
    await repo.initSharedData();
    final seen = repo.isSplashSeen();
    return SplashState(isSplashSeen: seen);
  }

  Future<void> saveSplashSeen(bool value) async {
    final repo = ref.read(splashRepositoryProvider);
    await repo.setSplashSeen(value);
    final current = state.valueOrNull;
    if (current != null) {
      state = AsyncData(current.copyWith(isSplashSeen: value));
    }
  }

  Future<bool> subscribeMail(String email) async {
    final current = state.valueOrNull;
    if (current == null) return false;
    state = AsyncData(current.copyWith(isLoading: true));
    final repo = ref.read(splashRepositoryProvider);
    final response = await repo.subscribeEmail(email);
    state = AsyncData(current.copyWith(isLoading: false));
    return response.statusCode == 200;
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final splashProvider =
    AsyncNotifierProvider<SplashNotifier, SplashState>(SplashNotifier.new);
