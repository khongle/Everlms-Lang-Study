import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';
import 'package:lottie/lottie.dart';

class CompleteDialog extends StatelessWidget {
  const CompleteDialog({
    required this.title,
    super.key,
    this.icon,
    this.lottie,
    this.description,
  });
  final String? icon;
  final String? lottie;
  final String? title;
  final String? description;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      insetPadding: const EdgeInsets.all(30),
      clipBehavior: Clip.antiAliasWithSaveLayer,
      child: SizedBox(
        width: 500,
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (icon != null)
                Padding(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
                  child: SvgPicture.asset(
                    icon ?? '',
                    width: 50,
                    height: 50,
                    colorFilter: const ColorFilter.mode(
                      Colors.blue,
                      BlendMode.srcIn,
                    ),
                  ),
                )
              else
                lottie != null
                    ? Lottie.asset(lottie ?? '', repeat: false, height: 100)
                    : const SizedBox.shrink(),
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: Dimensions.paddingSizeLarge,
                ),
                child: Text(
                  title ?? '',
                  textAlign: TextAlign.center,
                  style: robotoMedium.copyWith(
                    color: Theme.of(context).primaryColor,
                    fontSize: Dimensions.fontSizeExtraLarge,
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                child: Text(
                  description ?? '',
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeLarge,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
              const SizedBox(height: Dimensions.paddingSizeLarge),
              TextButton(
                onPressed: () => context.pop(),
                style: TextButton.styleFrom(
                  backgroundColor:
                      Theme.of(context).disabledColor.withValues(alpha: 0.3),
                  minimumSize: Size(width, 45),
                  padding: EdgeInsets.zero,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  ),
                ),
                child: Text(
                  tl(context).continueAction,
                  textAlign: TextAlign.center,
                  style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeLarge,
                    color: Theme.of(context).colorScheme.onSurface,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
