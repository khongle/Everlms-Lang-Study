import 'dart:convert';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/feature/cheek_out/presentation/providers/payment_provider.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:paypal_payment/paypal_payment.dart';

class PayPalPayment extends ConsumerStatefulWidget {
  const PayPalPayment({required this.title, super.key});
  final String title;

  @override
  ConsumerState<PayPalPayment> createState() => _PayPalPaymentState();
}

class _PayPalPaymentState extends ConsumerState<PayPalPayment> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
      ),
      body: Center(
        child: Column(
          children: [
            TextButton(
              onPressed: () => {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => PaypalOrderPayment(
                      amount: '10',
                      currencyCode: 'USD',
                      clientId: "${dotenv.env['PAYPAL_CLIENT_ID']}",
                      secretKey: "${dotenv.env['PAYPAL_SECRET_KEY']}",
                      returnURL:
                          'https://language.dev.weebpal.com/payment/success',
                      cancelURL:
                          'https://language.dev.weebpal.com/payment/cancel',
                      note: 'Contact us for any questions on your order.',
                      onSuccess: (Map params) async {
                        if (kDebugMode) {
                          debugPrint(jsonEncode(params));
                        }
                      },
                      onError: (error) {
                        if (kDebugMode) {
                          debugPrint('onError: $error');
                        }
                      },
                      onCancel: (params) {
                        if (kDebugMode) {
                          debugPrint('cancelled: $params');
                        }
                      },
                    ),
                  ),
                ),
              },
              child: Text(tl(context).make_payment),
            ),
            TextButton(
              onPressed: initPaymentSheet,
              child: Text(tl(context).make_payment),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> initPaymentSheet() async {
    try {
      final intent =
          await ref.read(paymentProvider.notifier).createStripePaymentIntent(
                amount: 2000,
                currency: 'usd',
                secretKey: dotenv.env['STRIPE_SECRET_KEY'] ?? '',
              );

      await Stripe.instance
          .initPaymentSheet(
        paymentSheetParameters: SetupPaymentSheetParameters(
          merchantDisplayName: 'Flutter Stripe Store Demo',
          paymentIntentClientSecret: intent.clientSecret,
          customerEphemeralKeySecret: intent.ephemeralKey,
          customerId: intent.customer,
          style: ThemeMode.dark,
        ),
      )
          .then((value) async {
        final result = await Stripe.instance.presentPaymentSheet();
        debugPrint(result.toString());
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(tl(context).error_occurred(e.toString()))),
      );
    }
  }
}
