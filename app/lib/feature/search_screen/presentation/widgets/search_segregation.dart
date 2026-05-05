import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/home/presentation/widgets/category_course_bar.dart';
import 'package:everlms/feature/home/presentation/widgets/course_search_item_widget.dart';
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
    final list = homeState?.featuredCourses ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Feature courses',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        ListView.separated(
          physics: const NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          itemCount: list.length,
          separatorBuilder: (_, __) =>
              const SizedBox(height: Dimensions.paddingSizeSmall),
          itemBuilder: (_, i) => CourseSearchItemWidget(course: list[i]),
        ),
      ],
    );
  }
}
