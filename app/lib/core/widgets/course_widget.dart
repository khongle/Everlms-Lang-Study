import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/label_widget.dart';
import 'package:everlms/core/widgets/rating_bar.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class CourseWidget extends StatefulWidget {
  const CourseWidget({
    super.key,
    this.course,
    this.routingFrom = 'home',
    this.axis = Axis.horizontal,
  });
  final Course? course;
  final String? routingFrom;
  final Axis axis;

  @override
  State<CourseWidget> createState() => _CourseWidgetState();
}

class _CourseWidgetState extends State<CourseWidget> with RouteAware {
  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () async {
        context.push(
          AppRoutes.getCourseDetailsNotPurchaseScreenRoute(),
          extra: widget.course,
        );
      },
      child: widget.axis == Axis.vertical
          ? SizedBox(
              width: 150,
              child: Column(
                children: [
                  thumbnailCourse(widget.course, false),
                  informationCourse(context),
                ],
              ),
            )
          : SizedBox(
              height: ResponsiveHelper.isTab(context) ? 150 : 110,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                spacing: Dimensions.paddingSizeDefault,
                children: [
                  thumbnailCourse(widget.course, true),
                  Flexible(child: informationCourse(context)),
                ],
              ),
            ),
    );
  }

  ClipRRect thumbnailCourse(Course? course, bool isHorizontal) {
    final imagePath = course?.fieldImage;
    String? label;
    if (course != null) {
      if (course.isBestseller ?? false) {
        label = 'bestseller';
      } else if (course.isFree ?? false) {
        label = tl(context).free;
      }
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Stack(
        alignment: Alignment.topLeft,
        children: [
          CustomImage(
            image: imagePath,
            height: 100,
            width: isHorizontal ? 130 : 150,
          ),
          if (label != null)
            LabelWidget(
              label: label,
              isCourseBestseller: course?.isBestseller ?? false,
            )
          else
            Container(),
        ],
      ),
    );
  }

  Widget informationCourse(BuildContext context) {
    final totalRating = double.parse(widget.course?.totalRating ?? '0.0');
    final totalHours = widget.course?.fieldTotalHours;
    final totalLessons = widget.course?.totalLessons;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          widget.course?.fieldCategory ?? '',
          style: Theme.of(context)
              .textTheme
              .labelSmall!
              .copyWith(color: Theme.of(context).primaryColor),
        ),
        Text(
          widget.course?.title ?? '',
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
          textAlign: TextAlign.start,
          style: Theme.of(context).textTheme.titleSmall,
        ),
        Padding(
          padding:
              const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeMint),
          child: Row(
            spacing: Dimensions.paddingSizeSmall,
            children: [
              if (totalHours != null)
                labelInformation(Images.timer, '$totalHours hours')
              else
                const SizedBox.shrink(),
              if (totalLessons != null)
                labelInformation(Images.playSmall, '$totalLessons Lessons')
              else
                const SizedBox.shrink(),
            ],
          ),
        ),
        const SizedBox(
          width: Dimensions.paddingSizeSmall,
        ),
        CustomRatingBar(
          rating: totalRating,
          size: 15,
          ratingCount: totalRating.toInt(),
        ),
      ],
    );
  }

  Widget labelInformation(String icon, String? label) {
    if (label == null) return const SizedBox.shrink();
    return Row(
      children: [
        SvgPicture.asset(
          icon,
        ),
        const SizedBox(
          width: Dimensions.paddingSizeExtraSmall,
        ),
        Text(
          label,
          style: Theme.of(context).textTheme.labelSmall,
        ),
      ],
    );
  }
}
