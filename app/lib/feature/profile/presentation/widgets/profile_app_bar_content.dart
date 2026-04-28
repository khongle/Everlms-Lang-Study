import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/profile/data/models/user_model/user_data.dart';
import 'package:flutter/material.dart';

class ProfileAppBarContent extends StatelessWidget
    implements PreferredSizeWidget {
  const ProfileAppBarContent({
    required this.userData,
    super.key,
  });
  final UserData userData;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: ColoredBox(
        color: Theme.of(context).primaryColor,
        child: Column(
          children: [
            const SizedBox(
              height: Dimensions.paddingSizeLarge,
            ),
            // ClipRRect(
            //     borderRadius: const BorderRadius.all(
            //         Radius.circular(Dimensions.radiusExtraMoreLarge)),
            //     child: CustomImage(
            //       image: userData.image ?? '',
            //       height: 70,
            //       width: 70,
            //     )),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
            Text(
              userData.name ?? '',
              style: robotoSemiBold.copyWith(
                color: Theme.of(context).primaryColorLight,
                fontSize: Dimensions.fontSizeDefault,
              ),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeExtraSmall,
            ),
            // Text(
            //   userData.email ?? userData.phone ?? '',
            //   style: robotoRegular.copyWith(
            //       color: Theme.of(context).primaryColorLight,
            //       fontSize: Dimensions.fontSizeSmall),
            // ),
            const SizedBox(height: Dimensions.paddingSizeRadius),
            // SizedBox(
            //   width: width / 1.4,
            //   child: Text(
            //     userData.address ?? '',
            //     textAlign: TextAlign.center,
            //     style: robotoRegular.copyWith(
            //         color: Theme.of(context).primaryColorLight,
            //         fontSize: Dimensions.fontSizeSmall),
            //   ),
            // ),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
          ],
        ),
      ),
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(226);
}
