import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/data.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ButtonSection extends ConsumerWidget {
  const ButtonSection({required this.data, super.key});
  final CourseDetailData data;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(Dimensions.radiusDefault),
          topRight: Radius.circular(Dimensions.radiusDefault),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.shade300,
            spreadRadius: 1,
            blurRadius: 2,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      //height: 122,
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                if (data.contactNo!.isEmpty)
                  const SizedBox()
                else
                  Row(
                    children: [
                      Text(
                        '${tl(context).contact}:',
                        style: robotoRegular.copyWith(
                          color: Theme.of(context).colorScheme.onSurface,
                          fontSize: Dimensions.fontSizeSmall,
                        ),
                      ),
                      const SizedBox(
                        width: Dimensions.paddingSizeExtraSmall,
                      ),
                      SvgPicture.asset(
                        Images.phone,
                        colorFilter: ColorFilter.mode(
                          Theme.of(context).primaryColor,
                          BlendMode.srcIn,
                        ),
                      ),
                      const SizedBox(
                        width: Dimensions.paddingSizeExtraSmall,
                      ),
                      Text(
                        data.contactNo!,
                        style: robotoRegular.copyWith(
                          color: Theme.of(context).primaryColor,
                          fontSize: Dimensions.fontSizeSmall,
                        ),
                      ),
                    ],
                  ),
                Row(
                  children: [
                    if (data.isDiscounted == true)
                      Text(
                        '\$${data.discountedPrice}',
                        style: robotoSemiBold.copyWith(
                          color: Theme.of(context).colorScheme.onSurface,
                          fontSize: Dimensions.fontSizeLarge,
                        ),
                      ),
                    const SizedBox(width: Dimensions.paddingSizeRadius),
                    Text(
                      data.price == 'Free' ? 'Free' : '${data.price}',
                      style: robotoRegular.copyWith(
                        color: Theme.of(context).colorScheme.onSurface,
                        fontSize: Dimensions.fontSizeLarge,
                        fontWeight: FontWeight.bold,
                        decoration: data.isDiscounted == true
                            ? TextDecoration.lineThrough
                            : TextDecoration.none,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),
            // button
            Builder(
              builder: (context) {
                final cartState = ref.watch(cartProvider).valueOrNull;
                final isLoading = cartState?.isLoading ?? false;
                final containsCourse = cartState != null &&
                    ref
                        .read(cartProvider.notifier)
                        .isCartListContainCourseId(data.id!);
                return isLoading
                    ? const LoadingIndicator()
                    : CustomButton(
                        onPressed: () {
                          if (containsCourse) {
                            context.push(AppRoutes.getCartScreen());
                          } else {
                            ref.read(cartProvider.notifier).addToCart(data.id!);
                          }
                        },
                        buttonText: containsCourse
                            ? tl(context).view_cart
                            : tl(context).add_to_cart,
                      );
              },
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
          ],
        ),
      ),
    );
  }
}
