import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/instructor_details/presentation/providers/instructor_detail_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class FollowMessage extends ConsumerWidget {
  const FollowMessage({required this.id, super.key});
  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(instructorDetailProvider(id)).valueOrNull;
    if (state == null) return const SizedBox.shrink();
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (!state.isFollowing) followButton(context, ref, state),
        if (state.isFollowing) unfollowButton(context, ref, state),
        const SizedBox(width: Dimensions.paddingSizeDefault),
      ],
    );
  }

  Container messageButton(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    return Container(
      width: 110,
      height: 32,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        border: Border.all(color: Theme.of(context).primaryColor),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          SvgPicture.asset(Images.message),
          const SizedBox(width: Dimensions.paddingSizeSmall),
          Text(
            tl(context).message,
            style: robotoMedium.copyWith(
              color: isDarkMode ? Colors.green : Theme.of(context).primaryColor,
              fontSize: Dimensions.fontSizeSmall,
            ),
          ),
        ],
      ),
    );
  }

  InkWell followButton(
    BuildContext context,
    WidgetRef ref,
    InstructorDetailState state,
  ) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    return InkWell(
      onTap: () =>
          ref.read(instructorDetailProvider(id).notifier).followUnfollow(),
      radius: Dimensions.radiusLarge,
      child: Container(
        width: 110,
        height: 32,
        decoration: BoxDecoration(
          color: isDarkMode ? Colors.green : Theme.of(context).primaryColor,
          borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (state.followingInProgress)
              const SizedBox(
                height: 20,
                width: 20,
                child: LoadingIndicator(color: Colors.white, strokeWidth: 1),
              )
            else
              SvgPicture.asset(Images.follow),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            Text(
              tl(context).follow,
              style: robotoMedium.copyWith(
                color: Theme.of(context).primaryColorLight,
                fontSize: Dimensions.fontSizeSmall,
              ),
            ),
          ],
        ),
      ),
    );
  }

  InkWell unfollowButton(
    BuildContext context,
    WidgetRef ref,
    InstructorDetailState state,
  ) {
    return InkWell(
      onTap: () =>
          ref.read(instructorDetailProvider(id).notifier).followUnfollow(),
      radius: Dimensions.radiusLarge,
      child: Container(
        width: 110,
        height: 32,
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor,
          borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (state.followingInProgress)
              const SizedBox(
                height: 20,
                width: 20,
                child: LoadingIndicator(color: Colors.white, strokeWidth: 1),
              )
            else
              SvgPicture.asset(Images.unfollow),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            Text(
              tl(context).unfollow,
              style: robotoMedium.copyWith(
                color: Theme.of(context).primaryColorLight,
                fontSize: Dimensions.fontSizeSmall,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
