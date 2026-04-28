import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/select_domain.dart';
import 'package:flutter/material.dart';

Future<void> showSnackBarSuggest(
  BuildContext context,
  AnimationController controller,
) async {
  if (context.mounted) {
    await Future.delayed(const Duration(seconds: 10), () {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          animation: CurvedAnimation(
            parent: controller,
            curve: Curves.easeIn,
            reverseCurve: Curves.linear,
          ),
          backgroundColor: AppColors.info.withValues(alpha: 0.15),
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeSmall,
            vertical: Dimensions.paddingSizeLarge,
          ),
          margin: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
          behavior: SnackBarBehavior.floating,
          content: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Flexible(
                child: Text(
                  'Experience the app with your data',
                  style: robotoSemiBold.copyWith(color: Colors.black),
                ),
              ),
              Row(
                children: [
                  InkWell(
                    onTap: () {
                      showDialog(
                        context: context,
                        builder: (context) => const SelectDomain(),
                      );
                      ScaffoldMessenger.of(context).hideCurrentSnackBar();
                    },
                    child: Text(
                      'Set up',
                      style: robotoBold.copyWith(
                        color: Theme.of(context).primaryColor,
                      ),
                    ),
                  ),
                  const SizedBox(width: Dimensions.paddingSizeExtraLarge),
                  InkWell(
                    onTap: () =>
                        ScaffoldMessenger.of(context).hideCurrentSnackBar(),
                    child: Text(
                      'Later',
                      style: robotoRegular.copyWith(
                        color: Theme.of(context).primaryColor,
                      ),
                    ),
                  ),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                ],
              ),
            ],
          ),
          duration: const Duration(days: 1),
        ),
      );
    });
  }
}
