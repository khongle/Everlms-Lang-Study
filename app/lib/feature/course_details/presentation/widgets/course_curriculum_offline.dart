import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/router/route_args.dart';
import 'package:everlms/feature/course_details/presentation/widgets/curriculum_base.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class CourseCurriculumOffline extends StatelessWidget {
  const CourseCurriculumOffline({
    required this.chapters,
    super.key,
  });
  final List<Chapter> chapters;

  @override
  Widget build(BuildContext context) {
    return CurriculumBase(
      showProgressChapter: false,
      listChapters: chapters,
      onTapLessonHasPermission: (currentIndexLesson, currentIndexChapter) {
        context.push(
          AppRoutes.getLandingLearning(
            AppRoutes.courseDetailsOfflineScreen,
            'false',
          ),
          extra: LandingLearningRouteArgs(
            indexLesson: currentIndexLesson,
            indexChapter: currentIndexChapter,
            listChapter: chapters,
            isOnline: false,
          ),
        );
      },
    );
  }
}
