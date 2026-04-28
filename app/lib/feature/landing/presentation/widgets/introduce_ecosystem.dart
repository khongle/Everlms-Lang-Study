import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/select_domain.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:url_launcher/url_launcher.dart';

class IntroduceEcosystem extends StatelessWidget {
  const IntroduceEcosystem({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              SizedBox(
                width: 32,
                height: 32,
                child: IconButton.filled(
                  onPressed: () => Navigator.pop(context),
                  style: IconButton.styleFrom(
                    padding: EdgeInsets.zero,
                    backgroundColor: Theme.of(context)
                        .colorScheme
                        .onSurface
                        .withValues(alpha: 0.03),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  icon: const Icon(
                    Icons.clear,
                    size: Dimensions.iconSizeLarge,
                  ),
                ),
              ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.symmetric(
              vertical: Dimensions.paddingSizeDefault,
            ),
            child: SizedBox(
              height: 245,
              child: SvgPicture.asset(
                Images.everLms,
              ),
            ),
          ),
          Text(
            'EverLMS – A Complete, Flexible, and Scalable Learning Management System',
            textAlign: TextAlign.start,
            style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge),
          ),
          const SizedBox(
            height: Dimensions.paddingSizeDefault,
          ),
          const Text(
              'EverLMS is a robust and fully customizable Learning Management System (LMS)'
              ' built on the Drupal platform. It empowers educational institutions,'
              ' training organizations, businesses, and independent educators to deliver dynamic, engaging, '
              'and structured online learning experiences.'),
          const SizedBox(
            height: Dimensions.paddingSizeLarge,
          ),
          Flexible(
            child: textButtonCustom(
              'Experience the app with your system',
              () {
                Navigator.pop(context);
                showDialog(
                  context: context,
                  builder: (context) => const SelectDomain(),
                );
              },
              bgColor: Colors.teal,
              textColor: Theme.of(context).colorScheme.surface,
            ),
          ),
          const SizedBox(
            height: Dimensions.paddingSizeDefault,
          ),
          Flexible(
            child: textButtonCustom(
              'Learn more',
              () => launchUrl(Uri.parse('https://everlms.com')),
              textColor: Theme.of(context).colorScheme.onSurface,
            ),
          ),
          const SizedBox(
            height: Dimensions.paddingSizeLarge,
          ),
        ],
      ),
    );
  }

  Widget textButtonCustom(
    String label,
    VoidCallback? onTap, {
    Color bgColor = Colors.transparent,
    Color? textColor,
  }) {
    return Container(
      margin:
          const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusExtraLarge),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          TextButton(
            onPressed: onTap,
            style: TextButton.styleFrom(
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
            child: Text(
              label,
              style: robotoSemiBold.copyWith(color: textColor),
            ),
          ),
        ],
      ),
    );
  }
}
