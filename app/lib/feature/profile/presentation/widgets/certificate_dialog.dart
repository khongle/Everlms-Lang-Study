import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/profile/presentation/providers/certificate_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:url_launcher/url_launcher.dart';

class CertificateDialog extends ConsumerWidget {
  const CertificateDialog({required this.course, super.key});
  final Course course;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SimpleDialog(
      // <-- SEE HERE
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.all(
          Radius.circular(6),
        ),
      ),
      children: <Widget>[
        Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: CustomImage(
            image: course.thumbnail,
            fit: BoxFit.contain,
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Text(
            course.title!,
            style: robotoMedium.copyWith(
              color: Theme.of(context).colorScheme.onSurface,
              fontSize: Dimensions.fontSizeDefault,
            ),
          ),
        ),
        const SizedBox(height: 10),
        const SizedBox(height: Dimensions.paddingSizeExtraLarge),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Row(
            children: [
              Expanded(
                child: Container(
                  height: 32,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: Theme.of(context).primaryColor,
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  ),
                  child: InkWell(
                    onTap: () async {
                      final downloadUrl = await ref
                          .read(certificateProvider.notifier)
                          .getCertificateDownloadUrl(course.id.toString());
                      if (downloadUrl == null) return;
                      try {
                        await launchUrl(
                          Uri.parse(downloadUrl),
                          mode: LaunchMode.externalApplication,
                        );
                      } on Exception {
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            backgroundColor: Colors.red,
                            content: Text(tl(context).cant_open_link),
                            duration: const Duration(seconds: 3),
                            margin: const EdgeInsets.all(10),
                            behavior: SnackBarBehavior.floating,
                            dismissDirection: DismissDirection.horizontal,
                          ),
                        );
                      }
                    },
                    child: Text(
                      tl(context).download,
                      style: robotoRegular.copyWith(
                        color: Theme.of(context).primaryColorLight,
                        fontSize: Dimensions.fontSizeSmall,
                      ),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: Padding(
                  padding:
                      const EdgeInsets.only(left: Dimensions.cartWidgetSize),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      SvgPicture.asset(
                        Images.facebook,
                        height: 15,
                      ),
                      SvgPicture.asset(Images.twitter, height: 15),
                      SvgPicture.asset(Images.linkedin, height: 15),
                      SvgPicture.asset(Images.instagram, height: 15),
                      SvgPicture.asset(Images.messenger, height: 15),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
