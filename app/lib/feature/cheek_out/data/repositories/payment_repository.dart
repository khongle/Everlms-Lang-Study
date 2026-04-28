import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/feature/cheek_out/data/models/payment_intent.dart';

abstract class PaymentRepository {
  Future<PaymentIntent> createStripePaymentIntent({
    required int amount,
    required String currency,
    required String secretKey,
  });
}

class PaymentRepositoryImpl implements PaymentRepository {
  PaymentRepositoryImpl(this._dio);

  final Dio _dio;

  @override
  Future<PaymentIntent> createStripePaymentIntent({
    required int amount,
    required String currency,
    required String secretKey,
  }) async {
    try {
      final response = await _dio.post(
        'https://api.stripe.com/v1/payment_intents',
        data: {
          'amount': amount.toString(),
          'currency': currency,
          'automatic_payment_methods[enabled]': 'true',
        },
        options: Options(
          contentType: Headers.formUrlEncodedContentType,
          headers: {
            'Authorization':
                'Basic ${base64Encode(utf8.encode('$secretKey:'))}',
          },
        ),
      );
      return PaymentIntent.fromJson(
        response.data as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      throw ApiException(
        statusCode: e.response?.statusCode ?? 0,
        message: e.message ?? 'Stripe payment intent failed',
      );
    }
  }
}
