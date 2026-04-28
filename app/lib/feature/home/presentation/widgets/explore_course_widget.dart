import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/presentation/utils/course_price_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ExploreCourseWidget extends StatelessWidget {
  const ExploreCourseWidget({required this.course, super.key});
  final Course course;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        context.push(AppRoutes.courseDetailsScreen, extra: course);
      },
      child: Container(
        width: 155,
        height: 197,
        decoration: BoxDecoration(
          border: Border.all(
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: 0.06),
          ),
          borderRadius: const BorderRadius.all(Radius.circular(6)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(6),
                  topRight: Radius.circular(6),
                ),
                child: Image.network(
                  course.thumbnail ?? '',
                  fit: BoxFit.fitWidth,
                  width: MediaQuery.of(context).size.width,
                ),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            course.title ?? '',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.left,
                            style: robotoMedium.copyWith(
                              color: Theme.of(context).colorScheme.onSurface,
                              fontSize: Dimensions.fontSizeSemiSmall,
                            ),
                          ),
                          Expanded(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        SvgPicture.asset(Images.playSmall),
                                        const SizedBox(
                                          width: Dimensions.paddingSizeMint,
                                        ),
                                        Text(
                                          '${course.totalLessons ?? 0} Lessons',
                                          style: robotoRegular.copyWith(
                                            fontSize:
                                                Dimensions.fontSizeExtraSmall,
                                          ),
                                        ),
                                      ],
                                    ),
                                    Row(
                                      children: [
                                        SvgPicture.asset(Images.profileSmall),
                                        const SizedBox(width: 2),
                                        Text(
                                          '${course.totalLessons ?? 0}',
                                          style: robotoRegular.copyWith(
                                            fontSize:
                                                Dimensions.fontSizeExtraSmall,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),

                                //price section
                                const SizedBox(
                                  height: Dimensions.paddingSizeSmall,
                                ),
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      coursePriceText(course),
                                      style: robotoRegular.copyWith(
                                        color: Theme.of(context).primaryColor,
                                        fontWeight: FontWeight.w600,
                                        fontSize: Dimensions.fontSizeSmall,
                                      ),
                                    ),
                                    Row(
                                      children: [
                                        SvgPicture.asset(Images.starFill),
                                        const SizedBox(
                                          width:
                                              Dimensions.paddingSizeExtraSmall,
                                        ),
                                        Text(
                                          course.totalRating ?? '0.0',
                                          style: robotoRegular.copyWith(
                                            fontSize:
                                                Dimensions.fontSizeExtraSmall,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Expanded(
                    //   child: Row(
                    //     mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    //     children: [
                    //       Text(
                    //         calculateCoursePrice(course),
                    //         style: robotoRegular.copyWith(
                    //             color: Theme.of(context).brightness == Brightness.dark
                    //                 ? Colors.green
                    //                 : Theme.of(context).primaryColor,
                    //             fontWeight: FontWeight.w600,
                    //             fontSize: Dimensions.fontSizeSmall),
                    //       ),
                    //       Row(
                    //         children: [
                    //           SvgPicture.asset(Images.starFill),
                    //           const SizedBox(
                    //             width: Dimensions.paddingSizeExtraSmall,
                    //           ),
                    //           Text(
                    //             course.totalRating ?? "0.0",
                    //             style: robotoRegular.copyWith(
                    //                 fontSize: Dimensions.fontSizeExtraSmall),
                    //           ),
                    //         ],
                    //       )
                    //     ],
                    //   ),
                    // ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
