import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/router/route_args.dart';
import 'package:everlms/feature/course_details/presentation/widgets/curriculum_base.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CourseCurriculum extends StatelessWidget {
  const CourseCurriculum({
    required this.chapters,
    super.key,
    this.title = 'course_curriculum',
  });
  final List<Chapter> chapters;
  final String title;
  @override
  Widget build(BuildContext context) {
    return CurriculumBase(
      showProgressChapter: true,
      listChapters: chapters,
      onTapLessonHasPermission: (currentIndexLesson, currentIndexChapter) {
        context.push(
          AppRoutes.getLandingLearning(
            AppRoutes.courseDetailsScreen,
            'true',
          ),
          extra: LandingLearningRouteArgs(
            indexLesson: currentIndexLesson,
            indexChapter: currentIndexChapter,
            listChapter: chapters,
          ),
        );
      },
    );
  }
}
