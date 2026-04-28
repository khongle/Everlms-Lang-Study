import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class SearchBody extends StatelessWidget {
  const SearchBody({required this.courseList, super.key});
  final List<Course> courseList;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (courseList.isNotEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
              vertical: Dimensions.paddingSizeSmall,
            ),
            child: Text(
              "Search for result in ${courseList.length} ${courseList.length > 1 ? 'courses' : 'course'}",
            ),
          ),
        //WishListCourse()
        searchResultUi(context),
      ],
    );
  }

  Widget searchResultUi(BuildContext context) => Expanded(
        child: ListView.separated(
          itemCount: courseList.length,
          shrinkWrap: true,
          separatorBuilder: (context, index) => const Divider(
            color: Colors.black26,
            height: 0.1,
          ),
          itemBuilder: (context, index) {
            return searchResulWidget(context, courseList[index]);
          },
        ),
      );

  Widget searchResulWidget(BuildContext context, Course course) {
    // double width = MediaQuery.of(context).size.width;
    return InkWell(
      onTap: () => context.push(
        AppRoutes.courseDetailsScreenNotPurchase,
        extra: course,
      ),
      child: CourseWidget(
        course: course,
      ),
    );
  }

  Padding courseWidget(Course course, BuildContext context, double width) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Flexible(
            child: ClipRRect(
              borderRadius: const BorderRadius.all(
                Radius.circular(Dimensions.radiusSmall),
              ),
              child: CustomImage(
                image: course.fieldImage,
                height: 100,
                fit: BoxFit.contain,
              ),
            ),
          ),
          const SizedBox(
            width: Dimensions.paddingSizeSmall,
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  course.fieldCategory ?? '',
                  style: robotoBold.copyWith(
                    color: Theme.of(context).primaryColor,
                    fontSize: Dimensions.fontSizeSmall,
                  ),
                ),
                SizedBox(
                  width: width / 1.8,
                  child: Text(
                    course.title ?? '',
                    style: robotoMedium.copyWith(
                      color: Theme.of(context).colorScheme.onSurface,
                      fontSize: Dimensions.fontSizeLarge,
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    vertical: Dimensions.paddingSizeExtraSmall,
                  ),
                  child: Row(
                    children: [
                      Text(course.totalRating ?? ''),
                      const SizedBox(
                        width: Dimensions.paddingSizeSmall,
                      ),
                      SvgPicture.asset(Images.starFill),
                      const Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeSmall,
                        ),
                        child: Text('•'),
                      ),
                      Text(
                        '${course.fieldTotalHours} ${tl(context).hours}',
                        style: robotoRegular.copyWith(
                          color: Theme.of(context)
                              .textTheme
                              .bodyLarge!
                              .color!
                              .withValues(alpha: 0.6),
                          fontSize: Dimensions.fontSizeExtraSmall,
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeSmall,
                        ),
                        child: Text('•'),
                      ),
                      // Row(
                      //   children: [
                      //     SvgPicture.asset(Images.profileSmall),
                      //     const SizedBox(
                      //       width: Dimensions.paddingSizeMint,
                      //     ),
                      //     Text(
                      //       "${course.totalEnrolls} ${tl(context).enroll}",
                      //       style: robotoRegular.copyWith(
                      //           color: Theme.of(context)
                      //               .textTheme
                      //               .bodyLarge!
                      //               .color!
                      //               .withValues(alpha: 0.6),
                      //           fontSize: Dimensions.fontSizeExtraSmall),
                      //     )
                      //   ],
                      // ),
                      Text(
                        course.fieldLevel ?? '',
                        style: robotoRegular.copyWith(
                          color: Theme.of(context)
                              .textTheme
                              .bodyLarge!
                              .color!
                              .withValues(alpha: 0.6),
                          fontSize: Dimensions.fontSizeExtraSmall,
                        ),
                      ),
                    ],
                  ),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    if (course.isFree ?? false)
                      Text(
                        //"\$27.00",
                        // calculateCoursePrice(course),
                        tl(context).free,
                        style: robotoRegular.copyWith(
                          color: Theme.of(context).primaryColor,
                          fontSize: Dimensions.fontSizeSmall,
                        ),
                      )
                    else
                      const SizedBox.shrink(),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
