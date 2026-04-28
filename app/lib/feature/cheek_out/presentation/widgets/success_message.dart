import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class SuccessMessage extends StatelessWidget {
  const SuccessMessage({super.key});

  @override
  Widget build(BuildContext context) {
    return SimpleDialog(
      // <-- SEE HERE
      alignment: Alignment.center,
      children: <Widget>[
        const SizedBox(
          height: Dimensions.paddingSizeLarge,
        ),
        Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: CircleAvatar(
            radius: 35,
            backgroundColor: Theme.of(context).primaryColor,
            child: SvgPicture.asset(Images.successful),
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeSmall),
        Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeDefault,
              ),
              child: Text(
                'Successful',
                style: robotoSemiBold.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.customAppBarSize,
              ),
              child: Text(
                'Congratulations, you have completed your Purchase!',
                textAlign: TextAlign.center,
                style: robotoRegular.copyWith(
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.5),
                  fontSize: Dimensions.fontSizeSmall,
                ),
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
              child: CustomButton(
                onPressed: () {
                  context.push(AppRoutes.learningScreen);
                },
                buttonText: tl(context).go_to_playlist,
              ),
            ),
          ],
        ),
      ],
    );
  }
}
