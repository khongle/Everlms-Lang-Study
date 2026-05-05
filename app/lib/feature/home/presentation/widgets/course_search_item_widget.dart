import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/label_widget.dart';
import 'package:everlms/core/widgets/rating_bar.dart';
import 'package:everlms/feature/home/data/models/course_search_result.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

/// Widget that renders a [CourseSearchItem] (phase-01) as a horizontal card,
/// matching the look of the legacy [CourseWidget].
class CourseSearchItemWidget extends StatelessWidget {
  const CourseSearchItemWidget({required this.course, super.key});
  final CourseSearchItem course;

  @override
  Widget build(BuildContext context) {
    String? label;
    if (course.bestseller) {
      label = 'bestseller';
    } else if (course.isFree) {
      label = 'free';
    }

    return InkWell(
      onTap: () => context.push(
        AppRoutes.getCourseDetailsNotPurchaseScreenRoute(),
        extra: course.id,
      ),
      child: SizedBox(
        height: 110,
        child: Row(
          mainAxisSize: MainAxisSize.min,
          spacing: Dimensions.paddingSizeDefault,
          children: [
            // Thumbnail
            ClipRRect(
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              child: Stack(
                alignment: Alignment.topLeft,
                children: [
                  CustomImage(
                    image: course.thumbnailUrl,
                    height: 100,
                    width: 130,
                  ),
                  if (label != null)
                    LabelWidget(
                      label: label,
                      isCourseBestseller: course.bestseller,
                    ),
                ],
              ),
            ),
            // Info
            Flexible(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (course.category != null)
                    Text(
                      course.category!.name,
                      style: Theme.of(context).textTheme.labelSmall!.copyWith(
                            color: Theme.of(context).primaryColor,
                          ),
                    ),
                  Text(
                    course.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleSmall,
                  ),
                  const SizedBox(height: Dimensions.paddingSizeMint),
                  CustomRatingBar(
                    rating: course.rating,
                    size: 15,
                    ratingCount: course.ratingCount,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
