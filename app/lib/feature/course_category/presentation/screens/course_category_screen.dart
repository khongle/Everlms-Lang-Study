import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CourseCategoryScreen extends StatelessWidget {
  const CourseCategoryScreen({super.key, this.categories, this.listCourse});
  final List<String>? categories;
  final List<Course>? listCourse;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).explore_by_course_collection,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault,
          vertical: Dimensions.paddingSizeLarge,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          spacing: Dimensions.paddingSizeExtraMoreLarge,
          children: (categories ?? []).map((c) => item(context, c)).toList(),
        ),
      ),
    );
  }

  Widget item(BuildContext context, String category) {
    return InkWell(
      onTap: () {
        context.push(
          AppRoutes.viewAllCoursesScreen,
          extra: {
            'title': category,
            'courseList': (listCourse ?? [])
                .where((e) => e.fieldCategory == category)
                .toList(),
          },
        );
      },
      highlightColor: Colors.transparent,
      child: Builder(
        builder: (context) {
          return Text(
            category,
            style: Theme.of(context)
                .textTheme
                .bodyMedium!
                .copyWith(fontSize: Dimensions.fontSizeLarge),
          );
        },
      ),
    );
  }
}
