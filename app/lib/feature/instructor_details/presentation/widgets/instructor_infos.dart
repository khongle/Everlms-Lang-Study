import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_profile_data.dart';
import 'package:flutter/material.dart';

class InstructorInfo extends StatelessWidget {
  const InstructorInfo({required this.data, super.key});
  final InstructorProfileData data;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          infoCard(context, '${data.totalCourse}', tl(context).course),
          infoCard(context, '${data.totalStudent}', tl(context).student),
          infoCard(context, '${data.followers}', tl(context).follower),
          Container(
            height: 70,
            width: 75,
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.06),
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                SizedBox(
                  height: 30,
                  width: 30,
                  child: Image.network(
                    //Images.logo,
                    data.organization!.logo!,
                    errorBuilder: (context, error, stackTrace) =>
                        Image.asset(Images.placeholder),
                  ),
                ),
                const SizedBox(
                  height: 5,
                ),
                Text(
                  data.organization!.name ?? '',
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
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
          ),
        ],
      ),
    );
  }

  Widget infoCard(BuildContext context, String total, String title) {
    return Container(
      height: 70,
      width: 75,
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            total,
            style: robotoMedium.copyWith(
              color: Theme.of(context).brightness == Brightness.dark
                  ? Colors.green
                  : Theme.of(context).primaryColor,
              fontSize: Dimensions.fontSizeDefault,
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
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
