import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:shimmer_animation/shimmer_animation.dart';

class CourseShimmer extends StatelessWidget {
  const CourseShimmer({
    required this.isEnabled,
    required this.hasDivider,
    super.key,
  });
  final bool? isEnabled;
  final bool? hasDivider;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        color: Theme.of(context).highlightColor.withValues(alpha: 0.3),
      ),
      child: Shimmer(
        duration: const Duration(seconds: 2),
        enabled: isEnabled!,
        child: Column(
          children: [
            Container(
              height: 100,
              width: double.maxFinite,
              color: Colors.grey[400],
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            Container(
              height: 10,
              width: double.maxFinite,
              color: Colors.grey[Theme.of(context).brightness == Brightness.dark ? 600 : 400],
              margin: const EdgeInsets.only(
                right: Dimensions.paddingSizeLarge,
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            Container(
              height: 10,
              width: double.maxFinite,
              color: Colors.grey[Theme.of(context).brightness == Brightness.dark ? 600 : 400],
              margin: const EdgeInsets.only(
                right: Dimensions.paddingSizeLarge,
              ),
            ),
            // const RatingBar(rating: 0, size: 12, ratingCount: 0),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            // Row(
            //     mainAxisAlignment: MainAxisAlignment.spaceBetween,
            //     children: [
            //       Container(
            //           height: 15,
            //           width: 50,
            //           color: Colors.grey[context.isDarkMode ? 600 : 400]),
            //       const SizedBox(
            //           width: Dimensions.paddingSizeExtraSmall),
            //       Container(
            //           height: 15,
            //           width: 50,
            //           color: Colors.grey[context.isDarkMode ? 600 : 400]),
            //     ]),
          ],
        ),
      ),
    );
  }
}
