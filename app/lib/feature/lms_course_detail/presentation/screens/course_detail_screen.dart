import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/lms_course_detail/data/models/course_detail_dto.dart';
import 'package:everlms/feature/lms_course_detail/presentation/providers/course_detail_providers.dart';
import 'package:everlms/feature/lms_course_detail/presentation/widgets/course_header_section.dart';
import 'package:everlms/feature/lms_course_detail/presentation/widgets/curriculum_tab.dart';
import 'package:everlms/feature/lms_course_detail/presentation/widgets/overview_tab.dart';
import 'package:everlms/feature/lms_course_detail/presentation/widgets/sticky_bottom_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

class CourseDetailScreen extends ConsumerWidget {
  const CourseDetailScreen({required this.courseId, super.key});

  final int courseId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final courseAsync = ref.watch(courseDetailQueryProvider(courseId));

    return courseAsync.when(
      loading: _LoadingScaffold.new,
      error: (e, _) => Scaffold(
        backgroundColor: AppColors.bgPrimary,
        appBar: AppBar(
          backgroundColor: AppColors.bgSecondary,
          iconTheme: const IconThemeData(color: AppColors.textPrimary),
        ),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline,
                  color: AppColors.accentDanger, size: 48,),
              const SizedBox(height: 12),
              const Text('Failed to load course.',
                  style: TextStyle(color: AppColors.textSecondary),),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () =>
                    ref.invalidate(courseDetailQueryProvider(courseId)),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
      data: (course) => _CourseDetailContent(course: course),
    );
  }
}

class _CourseDetailContent extends StatefulWidget {
  const _CourseDetailContent({required this.course});
  final CourseDetailDto course;

  @override
  State<_CourseDetailContent> createState() => _CourseDetailContentState();
}

class _CourseDetailContentState extends State<_CourseDetailContent>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _onLessonTap(LessonItemDto lesson) {
    context.push('/courses/${widget.course.id}/lessons/${lesson.id}');
  }

  @override
  Widget build(BuildContext context) {
    final course = widget.course;
    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: AppBar(
        backgroundColor: AppColors.bgSecondary,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
        title: Text(
          course.title,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: AppColors.textPrimary,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.accentPrimary,
          labelColor: AppColors.textPrimary,
          unselectedLabelColor: AppColors.textSecondary,
          tabs: const [
            Tab(text: 'Overview'),
            Tab(text: 'Curriculum'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Scrollable header + tab body
          Expanded(
            child: NestedScrollView(
              headerSliverBuilder: (context, _) => [
                SliverToBoxAdapter(
                  child: CourseHeaderSection(course: course),
                ),
              ],
              body: TabBarView(
                controller: _tabController,
                children: [
                  OverviewTab(course: course),
                  CurriculumTab(
                    chapters: course.chapters,
                    isEnrolled: course.isEnrolled,
                    onLessonTap: _onLessonTap,
                  ),
                ],
              ),
            ),
          ),
          // Sticky CTA bar
          StickyBottomBar(course: course),
        ],
      ),
    );
  }
}

class _LoadingScaffold extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: AppBar(
        backgroundColor: AppColors.bgSecondary,
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
      ),
      body: Shimmer.fromColors(
        baseColor: AppColors.bgTertiary,
        highlightColor: AppColors.bgSecondary,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 200,
              color: AppColors.bgTertiary,
            ),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                      height: 24,
                      width: double.infinity,
                      color: AppColors.bgTertiary,),
                  const SizedBox(height: 8),
                  Container(
                      height: 16, width: 200, color: AppColors.bgTertiary,),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
