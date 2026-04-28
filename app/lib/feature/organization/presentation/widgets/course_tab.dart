import 'package:everlms/core/widgets/course_view_verticle.dart';
import 'package:everlms/core/widgets/paginated_list_view.dart';
import 'package:everlms/feature/organization/presentation/providers/organization_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CourseTab extends ConsumerWidget {
  const CourseTab({required this.orgId, super.key});
  final int orgId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(organizationProvider(orgId)).valueOrNull;
    if (state == null) return const SizedBox.shrink();

    final scrollController = ScrollController();
    return PaginatedListView(
      scrollController: scrollController,
      totalSize: 10,
      offset: 1,
      onPaginate: (_) =>
          ref.read(organizationProvider(orgId).notifier).loadMoreCourses(),
      itemView: ServiceViewVertical(service: state.courseList),
    );
  }
}
