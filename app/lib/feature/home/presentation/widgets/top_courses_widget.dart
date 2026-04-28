import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class TopCourseWidget extends StatelessWidget {
  const TopCourseWidget({required this.list, super.key});
  final List<Course> list;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(
          height: Dimensions.paddingSizeSmall,
        ),
        TitleView(
          title: tl(context).top_courses,
          isViewAllEnabled: list.length > 3,
          onViewAllClicked: () => context.push(
            AppRoutes.viewAllCoursesScreen,
            extra: {
              'title': tl(context).top_courses,
              'courseList': list,
            },
          ),
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        ListView.builder(
          physics: const NeverScrollableScrollPhysics(),
          itemCount: list.length > 3 ? 3 : list.length,
          shrinkWrap: true,
          itemBuilder: (context, index) {
            return CourseWidget(
              course: list.elementAt(index),
            );
          },
        ),
      ],
    );
  }
}
