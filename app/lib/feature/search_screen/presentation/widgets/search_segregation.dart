import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/featured_courses_widget.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/home/presentation/widgets/category_course_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class SearchSegregation extends ConsumerWidget {
  const SearchSegregation({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Top Category',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        const CategoryCourseBar(useWrap: true, maxLength: 5),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        _listCourses(context, ref),
      ],
    );
  }

  Widget _listCourses(BuildContext context, WidgetRef ref) {
    final homeState = ref.watch(homeProvider).valueOrNull;
    final list = homeState?.allCourses
            .where((e) => e.isFeature == true)
            .toList() ??
        [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Feature courses',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        GridView.count(
          padding: const EdgeInsets.symmetric(
            vertical: Dimensions.paddingSizeDefault,
          ),
          childAspectRatio: 0.8,
          crossAxisSpacing: 25,
          mainAxisSpacing: 10,
          crossAxisCount: 2,
          primary: false,
          physics: const NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          children: list
              .map((course) => FeaturedCoursesWidget(course: course))
              .toList(),
        ),
      ],
    );
  }
}
