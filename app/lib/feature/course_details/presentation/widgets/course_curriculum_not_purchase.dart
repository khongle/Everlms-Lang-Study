import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/router/route_args.dart';
import 'package:everlms/feature/course_details/presentation/widgets/curriculum_base.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CourseCurriculumNotPurchase extends StatefulWidget {
  const CourseCurriculumNotPurchase({
    required this.chapters,
    required this.courseId,
    super.key,
    this.course,
  });
  final List<Chapter> chapters;
  final String courseId;
  final Course? course;
  @override
  State<CourseCurriculumNotPurchase> createState() =>
      _CourseCurriculumNotPurchase();
}

class _CourseCurriculumNotPurchase extends State<CourseCurriculumNotPurchase>
    with RouteAware {
  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void dispose() {
    super.dispose();
    routeObserver.unsubscribe(this);
  }

  @override
  Widget build(BuildContext context) {
    return CurriculumBase(
      showProgressChapter: false,
      listChapters: widget.chapters,
      onTapLesson: (lesson) async {
        if (lesson.hasPermission) {
          await context.push(
            AppRoutes.getLearningScreen(
              AppRoutes.courseDetailsScreenNotPurchase,
              'true',
            ),
            extra: LearningRouteArgs(
              lesson: lesson,
              fromPage: AppRoutes.courseDetailsScreenNotPurchase,
            ),
          );
        }
      },
    );
  }
}
