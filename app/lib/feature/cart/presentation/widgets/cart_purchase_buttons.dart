import 'dart:io';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/cart/data/models/calculations.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CartPurchaseButtons extends ConsumerWidget {
  const CartPurchaseButtons({
    required this.onPayPal,
    required this.onStripe,
    required this.onInAppPurchase,
    required this.onRestorePurchase,
    super.key,
  });

  final void Function(Calculations?, List<Course>) onPayPal;
  final void Function(Calculations?, List<Course>) onStripe;
  final VoidCallback onInAppPurchase;
  final VoidCallback onRestorePurchase;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cartState = ref.watch(cartProvider).valueOrNull ?? const CartState();
    if (cartState.isLoading) return const LoadingIndicator();
    if (cartState.cartList.isEmpty) return const SizedBox();

    return SizedBox(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          if (Platform.isAndroid)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CustomButton(
                  onPressed: () => onPayPal(
                    cartState.calculations,
                    cartState.listCourse,
                  ),
                  buttonText: 'PayPal Payment',
                ),
                CustomButton(
                  onPressed: () => onStripe(
                    cartState.calculations,
                    cartState.listCourse,
                  ),
                  buttonText: 'Credit Payment',
                ),
              ],
            )
          else
            Column(
              children: [
                CustomButton(
                  onPressed: onInAppPurchase,
                  margin: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeLarge,
                  ),
                  fontSize: Dimensions.fontSizeExtraLarge,
                  buttonText: 'Enroll',
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    vertical: Dimensions.paddingSizeDefault,
                  ),
                  child: TextButton(
                    onPressed: onRestorePurchase,
                    child: Text(tl(context).restore_purchase),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }
}
