import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

class PaymentMethod extends StatelessWidget {
  const PaymentMethod({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            tl(context).payment_get_way,
            style:
                robotoSemiBold.copyWith(fontSize: Dimensions.fontSizeDefault),
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          paymentCart(context, Images.bkashDemo, 'bKash'),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          paymentCart(context, Images.nagadDemo, 'Nagad'),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          paymentCart(context, Images.googlePayDemo, 'Google Pay'),
        ],
      ),
    );
  }

  Widget paymentCart(BuildContext context, String icon, String title) {
    return Column(
      children: [
        Container(
          decoration: BoxDecoration(
            border: Border.all(
              color: Theme.of(context)
                  .textTheme
                  .bodyLarge!
                  .color!
                  .withValues(alpha: 0.06),
            ),
            borderRadius: const BorderRadius.all(
              Radius.circular(Dimensions.radiusSmall),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    SvgPicture.asset(
                      icon,
                      height: 22,
                    ),
                    const SizedBox(
                      width: Dimensions.paddingSizeDefault,
                    ),
                    Text(
                      title,
                      style: robotoSemiBold.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).colorScheme.onSurface,
                      ),
                    ),
                  ],
                ),
                const Icon(
                  Icons.arrow_forward_ios,
                  size: Dimensions.radiusLarge,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
