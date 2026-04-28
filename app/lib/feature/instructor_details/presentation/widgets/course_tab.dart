import 'package:everlms/core/widgets/course_view_verticle.dart';
import 'package:everlms/core/widgets/paginated_list_view.dart';
import 'package:everlms/feature/instructor_details/presentation/providers/instructor_detail_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CourseTab extends ConsumerWidget {
  const CourseTab({required this.id, super.key});
  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final scrollController = ScrollController();
    final state = ref.watch(instructorDetailProvider(id));
    final courses = state.valueOrNull?.courseList ?? [];
    return PaginatedListView(
      scrollController: scrollController,
      totalSize: courses.length,
      offset: 1,
      onPaginate: (_) =>
          ref.read(instructorDetailProvider(id).notifier).loadMoreCourses(),
      itemView: ServiceViewVertical(
        service: courses,
      ),
    );
  }
}
