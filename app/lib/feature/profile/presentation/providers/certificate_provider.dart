import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/profile/data/models/account/certificate_model.dart';
import 'package:everlms/feature/profile/data/repositories/certificate_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final certificateRepositoryProvider = Provider<CertificateRepository>((ref) {
  return CertificateRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CertificateState {
  const CertificateState({
    this.certificates = const [],
  });

  final List<Certificate> certificates;

  CertificateState copyWith({
    List<Certificate>? certificates,
  }) {
    return CertificateState(
      certificates: certificates ?? this.certificates,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class CertificateNotifier extends AsyncNotifier<CertificateState> {
  @override
  Future<CertificateState> build() async {
    final repo = ref.read(certificateRepositoryProvider);
    final certs = await repo.getCertificateList();
    return CertificateState(certificates: certs);
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(certificateRepositoryProvider);
      final certs = await repo.getCertificateList();
      return CertificateState(certificates: certs);
    });
  }

  Future<String?> getCertificateDownloadUrl(String certificateId) async {
    final prefs = ref.read(sharedPreferencesProvider);
    final token = prefs.getString(AppConstants.token);
    if (token == null || token.isEmpty) {
      return null;
    }
    return '${Config.baseUrl}${AppConstants.certificateDownload}/$certificateId?token=$token';
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final certificateProvider =
    AsyncNotifierProvider<CertificateNotifier, CertificateState>(
  CertificateNotifier.new,
);

// ---------------------------------------------------------------------------
// User certificates (flat list — same data, convenience FutureProvider)
// ---------------------------------------------------------------------------
final userCertificatesProvider = FutureProvider<List<Certificate>>((ref) async {
  return ref.watch(certificateProvider.future).then((s) => s.certificates);
});
