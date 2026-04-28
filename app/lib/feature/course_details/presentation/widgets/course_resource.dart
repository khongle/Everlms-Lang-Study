import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/resources.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

class CourseResource extends StatelessWidget {
  const CourseResource({required this.resource, super.key});
  final Resources resource;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Text(
            tl(context).course_resource,
            style:
                robotoSemiBold.copyWith(fontSize: Dimensions.fontSizeDefault),
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Wrap(
            spacing: 10,
            runSpacing: 10,
            runAlignment: WrapAlignment.center,
            children: [
              // if (resource.isDocAvailable == true)
              // featureCade(context, Images.pdfFormat, "PDF Format"),
              if (resource.isDocAvailable == true)
                featureCade(context, Images.document, 'Doc Format'),
              if (resource.isVideoAvailable == true)
                featureCade(context, Images.polygon, 'Video File'),
              if (resource.isAudioAvailable == true)
                featureCade(context, Images.audioFile, 'Audio File'),
            ],
          ),
        ),
      ],
    );
  }

  Widget featureCade(BuildContext context, String icon, String title) {
    return Container(
      padding: const EdgeInsets.only(left: 10, right: 10),
      width: MediaQuery.of(context).size.width / 2 - 20,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Theme.of(context)
            .textTheme
            .bodyLarge!
            .color!
            .withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: const BoxDecoration(
              //color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha: 0.06),
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(Dimensions.radiusSmall),
                bottomLeft: Radius.circular(Dimensions.radiusSmall),
              ),
            ),
            child: Center(
              child: SvgPicture.asset(icon),
            ),
          ),
          const SizedBox(
            width: Dimensions.paddingSizeSmall,
          ),
          Text(
            title,
            style: robotoRegular.copyWith(
              color: Theme.of(context)
                  .textTheme
                  .bodyLarge!
                  .color!
                  .withValues(alpha: 0.5),
              fontSize: Dimensions.fontSizeSmall,
            ),
          ),
        ],
      ),
    );
  }
}
