import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CartWidget extends ConsumerWidget {
  const CartWidget({
    required this.color,
    required this.size,
    super.key,
    this.fromRestaurant = false,
  });
  final Color color;
  final double size;
  final bool fromRestaurant;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cartList = ref.watch(cartProvider).valueOrNull?.cartList ?? [];
    return Stack(
      clipBehavior: Clip.none,
      children: [
        Image.asset(Images.cart, width: size, height: size, color: color),
        if (cartList.isNotEmpty)
          Positioned(
            top: -5,
            right: -5,
            child: Container(
              height: size < 20 ? 10 : size / 2,
              width: size < 20 ? 10 : size / 2,
              alignment: Alignment.center,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.red,
              ),
              child: Text(
                cartList.length.toString(),
                style: robotoRegular.copyWith(
                  fontSize: size < 20 ? size / 3 : size / 3.8,
                  color: Colors.white,
                ),
              ),
            ),
          )
        else
          const SizedBox(),
      ],
    );
  }
}
