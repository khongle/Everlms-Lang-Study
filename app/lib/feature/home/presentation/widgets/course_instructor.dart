import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/instructor_old.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CourseInstructor extends StatelessWidget {
  const CourseInstructor({
    required this.instructors,
    required this.title,
    super.key,
  });
  final String title;
  final List<InstructorOld> instructors;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TitleView(title: title, isViewAllEnabled: false),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        SizedBox(
          height: 70,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: instructors.length,
            itemBuilder: (context, index) {
              final instructor = instructors.elementAt(index);
              return Container(
                width: 255,
                margin: const EdgeInsets.only(
                  left: Dimensions.paddingSizeDefault,
                ),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  border: Border.all(
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge!
                        .color!
                        .withValues(alpha: 0.06),
                  ),
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                ),
                child: InkWell(
                  onTap: () {
                    context.push(
                      AppRoutes.getInstructorDetails(),
                      extra: instructor.id,
                    );
                  },
                  child: Row(
                    children: [
                      const SizedBox(
                        width: Dimensions.paddingSizeSmall,
                      ),
                      ClipRRect(
                        borderRadius: const BorderRadius.all(
                          Radius.circular(Dimensions.radiusSmall),
                        ),
                        child: CustomImage(
                          image: instructor.image,
                          height: 50,
                          width: 50,
                        ),
                      ),
                      const SizedBox(
                        width: Dimensions.paddingSizeSmall,
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 170,
                            child: Text(
                              instructor.name ?? '',
                              overflow: TextOverflow.ellipsis,
                              style: robotoMedium.copyWith(
                                color: Theme.of(context)
                                    .textTheme
                                    .bodyLarge!
                                    .color,
                                fontSize: Dimensions.fontSizeDefault,
                              ),
                              maxLines: 1,
                            ),
                          ),
                          Text(
                            instructor.instructor ?? '',
                            style: robotoRegular.copyWith(
                              color: Theme.of(context).colorScheme.primary,
                              fontSize: Dimensions.fontSizeExtraSmall,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
