import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/home/data/models/explore.dart';
import 'package:everlms/feature/home/presentation/widgets/explore_course_widget.dart';
import 'package:flutter/material.dart';

class SuggestedCourses extends StatelessWidget {
  const SuggestedCourses({required this.explore, super.key});
  final Explore explore;

  @override
  Widget build(BuildContext context) {
    final courses = explore.data!.suggestedCourses!;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Text(
            tl(context).suggested_courses,
            style:
                robotoSemiBold.copyWith(fontSize: Dimensions.fontSizeDefault),
          ),
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        SizedBox(
          height: 197,
          child: ListView.builder(
            physics: const BouncingScrollPhysics(),
            scrollDirection: Axis.horizontal,
            itemCount: courses.length,
            itemBuilder: (context, index) {
              return Padding(
                padding: EdgeInsets.only(
                  right: Dimensions.paddingSizeDefault,
                  left: index == 0 ? Dimensions.paddingSizeDefault : 0,
                ),
                child: ExploreCourseWidget(
                  course: courses.elementAt(index),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
