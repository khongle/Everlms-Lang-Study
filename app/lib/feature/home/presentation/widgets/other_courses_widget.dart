import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';

class OtherCoursesWidget extends StatelessWidget {
  const OtherCoursesWidget({required this.list, super.key});
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
          title: tl(context).other_courses,
          isViewAllEnabled: false,
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        ListView.builder(
          physics: const NeverScrollableScrollPhysics(),
          itemCount: list.length,
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
