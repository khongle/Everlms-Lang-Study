import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/home/presentation/widgets/explore_course_widget.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';

class OfferCourses extends StatelessWidget {
  const OfferCourses({required this.offeredCourses, super.key});
  final List<Course> offeredCourses;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TitleView(
          title: tl(context).offer_courses,
          isViewAllEnabled: false,
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        SizedBox(
          height: 197,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: offeredCourses.length,
            itemBuilder: (context, index) {
              return Padding(
                padding: EdgeInsets.only(
                  right: Dimensions.paddingSizeDefault,
                  left: index == 0 ? Dimensions.paddingSizeDefault : 0,
                ),
                child: ExploreCourseWidget(
                  course: offeredCourses.elementAt(index),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
