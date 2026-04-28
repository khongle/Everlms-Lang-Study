import 'package:dio/dio.dart';
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/cheek_out/data/models/payment_intent.dart';
import 'package:everlms/feature/cheek_out/data/repositories/payment_repository.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final paymentRepositoryProvider = Provider<PaymentRepository>((ref) {
  return PaymentRepositoryImpl(Dio());
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class PaymentState {
  const PaymentState({
    this.isLoading = true,
    this.isPaymentSuccess = false,
    this.progress = 0,
    this.url = '',
    this.paymentIntent,
  });

  final bool isLoading;
  final bool isPaymentSuccess;
  final double progress;
  final String url;
  final PaymentIntent? paymentIntent;

  PaymentState copyWith({
    bool? isLoading,
    bool? isPaymentSuccess,
    double? progress,
    String? url,
    PaymentIntent? paymentIntent,
  }) {
    return PaymentState(
      isLoading: isLoading ?? this.isLoading,
      isPaymentSuccess: isPaymentSuccess ?? this.isPaymentSuccess,
      progress: progress ?? this.progress,
      url: url ?? this.url,
      paymentIntent: paymentIntent ?? this.paymentIntent,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class PaymentNotifier extends Notifier<PaymentState> {
  @override
  PaymentState build() {
    return const PaymentState();
  }

  URLRequest makePaymentRequest() {
    final prefs = ref.read(sharedPreferencesProvider);
    final langCode = prefs.getString(AppConstants.localizationKey) ??
        AppConstants.languages[0].languageCode;
    final apiClient = ref.read(apiClientProvider);

    return URLRequest(
      url: WebUri('${Config.baseUrl}user/make-payment?lang=$langCode'),
      headers: {
        'Authorization': 'Bearer ${apiClient.token}',
        'Content-Type': 'application/json',
      },
    );
  }

  Uri successUri() {
    return Uri.parse('${Config.baseUrl}/complete-order');
  }

  Future<PaymentIntent> createStripePaymentIntent({
    required int amount,
    required String currency,
    required String secretKey,
  }) async {
    final repo = ref.read(paymentRepositoryProvider);
    final intent = await repo.createStripePaymentIntent(
      amount: amount,
      currency: currency,
      secretKey: secretKey,
    );
    state = state.copyWith(paymentIntent: intent);
    return intent;
  }

  void updateProgress(double value) {
    state = state.copyWith(progress: value);
  }

  void updateLoading(bool value) {
    state = state.copyWith(isLoading: value);
  }

  void updatePaymentSuccess(bool value) {
    state = state.copyWith(isPaymentSuccess: value);
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final paymentProvider = NotifierProvider<PaymentNotifier, PaymentState>(
  PaymentNotifier.new,
);
