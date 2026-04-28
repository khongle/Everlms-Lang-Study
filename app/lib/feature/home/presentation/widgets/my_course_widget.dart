import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/my_course.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class MyCourseWidget extends StatelessWidget {
  const MyCourseWidget({super.key, this.myCourseList, this.callback});
  final List<MyCourse>? myCourseList;
  final Function()? callback;

  @override
  Widget build(BuildContext context) {
    return myCourseList != null
        ? Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TitleView(
                title: tl(context).my_courses,
                onViewAllClicked: callback,
              ),
              const SizedBox(
                height: Dimensions.paddingSizeDefault,
              ),
              SizedBox(
                height: 112,
                child: ListView.builder(
                  itemCount: myCourseList?.length,
                  scrollDirection: Axis.horizontal,
                  itemBuilder: (context, index) {
                    final myCourse = myCourseList![index];
                    return myCourseItem(index, context, myCourse);
                  },
                ),
              ),
            ],
          )
        : const SizedBox();
  }

  Widget myCourseItem(int index, BuildContext context, MyCourse myCourse) {
    return Padding(
      padding: EdgeInsets.only(
        right: Dimensions.paddingSizeDefault,
        left: index == 0 ? Dimensions.paddingSizeDefault : 0,
      ),
      child: InkWell(
        onTap: () => context.push(
          AppRoutes.getLearningScreen(
            'courseAccess',
            'true',
          ),
        ),
        radius: 6,
        child: Container(
          width: MediaQuery.of(context).size.width / 1.3,
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
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Column(
              children: [
                Row(
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.all(Radius.circular(6)),
                      child: CustomImage(
                        width: 60,
                        height: 60,
                        image: myCourse.thumbnail ?? '',
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
                            myCourse.title ?? '',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeDefault,
                            ),
                          ),
                          const SizedBox(
                            height: Dimensions.paddingSizeExtraSmall,
                          ),
                          Text(
                            '${myCourse.completedLessons} Lesson / ${myCourse.totalLessons} Lesson',
                            style: robotoRegular.copyWith(
                              fontSize: Dimensions.fontSizeExtraSmall,
                              color: Theme.of(context)
                                  .textTheme
                                  .bodyLarge!
                                  .color!
                                  .withValues(alpha: .5),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    '${myCourse.completedPercentage}',
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                ClipRRect(
                  borderRadius: const BorderRadius.all(Radius.circular(10)),
                  child: LinearProgressIndicator(
                    value: calculatePercentage(
                      myCourse.completedPercentage ?? '0%',
                    ),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      Theme.of(context).primaryColor.withValues(alpha: .5),
                    ),
                    backgroundColor:
                        Theme.of(context).primaryColor.withValues(alpha: .1),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  double calculatePercentage(String percentage) {
    return double.parse(percentage.replaceAll('%', '')) / 100;
  }
}
