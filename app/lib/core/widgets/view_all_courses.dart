import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';

class ViewAllCourses extends StatelessWidget {
  const ViewAllCourses({
    required this.title,
    required this.courseList,
    super.key,
  });
  final String title;
  final List<Course> courseList;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: title,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(
          vertical: Dimensions.paddingSizeDefault,
          horizontal: Dimensions.paddingSizeSmall,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ListView.builder(
              physics: const NeverScrollableScrollPhysics(),
              itemCount: courseList.length,
              shrinkWrap: true,
              itemBuilder: (context, index) {
                return CourseWidget(course: courseList.elementAt(index));
              },
            ),
          ],
        ),
      ),
    );
  }
}
