import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class FeaturedCourse extends StatelessWidget {
  const FeaturedCourse({required this.courseList, super.key});
  final List<Course> courseList;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TitleView(
          title: tl(context).featured_courses,
          isViewAllEnabled: courseList.length > 3,
          onViewAllClicked: () {
            context.push(
              AppRoutes.viewAllCoursesScreen,
              extra: {
                'title': tl(context).featured_courses,
                'courseList': courseList,
              },
            );
          },
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        ListView.builder(
          physics: const NeverScrollableScrollPhysics(),
          itemCount: courseList.length > 3 ? 3 : courseList.length,
          shrinkWrap: true,
          itemBuilder: (context, index) {
            return CourseWidget(course: courseList.elementAt(index));
          },
        ),
      ],
    );
  }
}

class FeaturedCoursesWidget extends StatelessWidget {
  const FeaturedCoursesWidget({required this.course, super.key});
  final Course course;
  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => context.push(
        AppRoutes.getCourseDetailsNotPurchaseScreenRoute(),
        extra: course,
      ),
      child: Column(
        spacing: Dimensions.paddingSizeDefault,
        children: [
          // ClipRRect(
          //   borderRadius: const BorderRadius.only(
          //     topLeft: Radius.circular(6),
          //     bottomLeft: Radius.circular(6),
          //   ),
          //   child: Image.network(
          //     course.fieldImage ?? "",
          //     width: 120,
          //     height: 120,
          //     fit: BoxFit.fitWidth,
          //   ),
          // ),
          CustomImage(
            image: course.fieldImage ?? '',
            height: 100,
            width: MediaQuery.of(context).size.width,
          ),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  course.title ?? '',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: robotoMedium.copyWith(
                    color: Theme.of(context).colorScheme.onSurface,
                    fontSize: Dimensions.fontSizeDefault,
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    vertical: Dimensions.paddingSizeExtraSmall,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Flexible(
                        child: Text(
                          course.instructor?.name ?? '',
                          style: Theme.of(context).textTheme.labelSmall,
                        ),
                      ),
                      Flexible(
                        child: Text(
                          course.fieldLevel ?? '',
                          style:
                              Theme.of(context).textTheme.labelSmall!.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: Theme.of(context).primaryColor,
                                  ),
                        ),
                      ),
                    ],
                  ),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Text(
                    //   calculateCoursePrice(course),
                    //   style: robotoRegular.copyWith(
                    //       color: context.isDarkMode
                    //           ? Colors.green
                    //           : Theme.of(context).primaryColor,
                    //       fontSize: Dimensions.fontSizeSmall),
                    // ),
                    Row(
                      children: [
                        SvgPicture.asset(Images.timer),
                        const SizedBox(
                          width: Dimensions.paddingSizeExtraSmall,
                        ),
                        if (course.fieldTotalHours != null)
                          Text(
                            '${course.fieldTotalHours} hours',
                            style: Theme.of(context).textTheme.labelSmall,
                          )
                        else
                          const SizedBox.shrink(),
                      ],
                    ),
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
