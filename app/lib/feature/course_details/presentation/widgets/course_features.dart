import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/widgets/rating_bar.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/features.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class CourseFeatures extends StatelessWidget {
  const CourseFeatures({required this.feature, super.key});
  final Features feature;

  @override
  Widget build(BuildContext context) {
    return courseFeature(context);
  }

  Widget courseFeature(BuildContext context) {
    final rating = double.parse(feature.rating ?? '0');
    return Container(
      width: MediaQuery.of(context).size.width,
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      color: Theme.of(context).brightness == Brightness.dark
          ? Colors.grey.shade400
          : Colors.black,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        spacing: Dimensions.paddingSizeSmall,
        children: [
          Text(
            feature.courseName ?? '',
            style: Theme.of(context)
                .textTheme
                .titleLarge!
                .copyWith(color: Theme.of(context).colorScheme.surface),
          ),
          const SizedBox(
            height: Dimensions.paddingSizeSmall,
          ),
          CustomRatingBar(
            rating: rating,
            ratingCount: rating.truncate(),
          ),
          textFeature('Taught in ${feature.language}'),
          textFeature('Course Collection: ${feature.category}'),
          textFeature('Enroller: ${feature.totalEnroll} Learners'),
          textFeature(
            'Last Updated: ${DateFormat('d-M-y').format(epochToDateTime(feature.updated ?? ''))}',
          ),
        ],
      ),
    );
  }

  Widget textFeature(String title) {
    return Builder(
      builder: (context) {
        return Text(
          title,
          style: Theme.of(context)
              .textTheme
              .titleSmall!
              .copyWith(color: Theme.of(context).colorScheme.surface),
        );
      },
    );
  }
}
