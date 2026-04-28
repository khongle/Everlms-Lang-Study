class PaymentIntent {
  const PaymentIntent({
    required this.clientSecret,
    this.customer,
    this.ephemeralKey,
  });

  factory PaymentIntent.fromJson(Map<String, dynamic> json) {
    return PaymentIntent(
      clientSecret: json['client_secret'] as String? ?? '',
      customer: json['customer'] as String?,
      ephemeralKey: json['ephemeralKey'] as String?,
    );
  }

  final String clientSecret;
  final String? customer;
  final String? ephemeralKey;
}
