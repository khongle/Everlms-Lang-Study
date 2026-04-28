import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CategoryCourseBar extends ConsumerWidget {
  const CategoryCourseBar({
    super.key,
    this.useWrap = false,
    this.maxLength = 0,
  });
  final bool useWrap;
  final int maxLength;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final homeState = ref.watch(homeProvider).valueOrNull;
    if (homeState == null) return const SizedBox();
    final listCategory = homeState.categoryCourses;
    final limit = (maxLength > 0 && maxLength <= listCategory.length)
        ? maxLength
        : listCategory.length;
    final list = listCategory.take(limit).toList();

    if (useWrap) {
      list.sort((a, b) => a.length.compareTo(b.length));
      return Wrap(
        spacing: Dimensions.paddingSizeRadius,
        children: List.generate(list.length, (index) {
          return categoryItem(
            context,
            list[index],
            homeState,
          );
        }),
      );
    }
    return SizedBox(
      height: 40,
      child: ListView.separated(
        separatorBuilder: (context, index) => const SizedBox(
          width: Dimensions.paddingSizeRadius,
        ),
        shrinkWrap: true,
        scrollDirection: Axis.horizontal,
        itemCount: list.length,
        itemBuilder: (context, index) {
          final item = list[index];
          return categoryItem(context, item, homeState);
        },
      ),
    );
  }

  Widget categoryItem(
    BuildContext context,
    String category,
    HomeState homeState,
  ) {
    return TextButton(
      // key: UniqueKey(),
      onPressed: () {
        context.push(
          AppRoutes.viewAllCoursesScreen,
          extra: {
            'title': category,
            'courseList': homeState.allCourses
                .where((e) => e.fieldCategory == category)
                .toList(),
          },
        );
      },
      style: TextButton.styleFrom(
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault,
        ),
        shape: RoundedRectangleBorder(
          side: BorderSide(color: Colors.grey.shade500),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        ),
      ),
      child: Text(
        category,
        style: Theme.of(context).textTheme.titleSmall,
      ),
    );
  }
}
