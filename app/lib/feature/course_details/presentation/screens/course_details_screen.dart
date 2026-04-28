import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/course_details/presentation/providers/course_detail_provider.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_curriculum.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_overview.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_practice_quiz.dart';
import 'package:everlms/feature/course_details/presentation/widgets/tab_bar_widget.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';

class CourseDetailsScreen extends ConsumerStatefulWidget {
  const CourseDetailsScreen({this.courseArg, super.key});
  final Course? courseArg;

  @override
  ConsumerState<CourseDetailsScreen> createState() =>
      _CourseDetailsScreenState();
}

class _CourseDetailsScreenState extends ConsumerState<CourseDetailsScreen>
    with RouteAware, TickerProviderStateMixin, AutomaticKeepAliveClientMixin {
  Course? course;
  bool isSelect = false;
  YoutubePlayerController? _youtubePlayerController;
  TabController? _tabController;
  late List<String> tabLabel;
  bool _tabControllerInitialized = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    tabLabel = [
      tl(context).overview,
      tl(context).curriculum,
      tl(context).quiz,
    ];
    if (!_tabControllerInitialized) {
      _tabController =
          TabController(length: tabLabel.length, initialIndex: 1, vsync: this);
      _tabControllerInitialized = true;
    }
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void initState() {
    super.initState();
    course = widget.courseArg;
    if (course != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        final detailNotifier =
            ref.read(courseDetailProvider(course!.id!).notifier);
        final quizNotifier = ref.read(quizProvider.notifier);

        quizNotifier.getListQuizDataByLessonId(course);
        detailNotifier.getUserChapter(course!).then((_) {
          final chapters = ref
                  .read(courseDetailProvider(course!.id!))
                  .valueOrNull
                  ?.chapters ??
              [];
          quizNotifier.getQuizDataByChapterIds(chapters);
          detailNotifier.calculatorProgressCourse();
        });
        detailNotifier.checkCoursePermission(course?.id.toString() ?? '');
        quizNotifier.getQuizDataByCourseId(course?.id.toString() ?? '');
      });
    }
  }

  @override
  void didPopNext() {
    if (course != null) {
      ref
          .read(quizProvider.notifier)
          .getQuizDataByCourseId(course?.id.toString() ?? '');
      if (course?.progress != 0 && course?.isStart == false) {
        ref.read(myCourseProvider.notifier).loadMyCourses();
        ref
            .read(courseDetailProvider(course?.id ?? 0).notifier)
            .startCourse(course?.id.toString() ?? '');
      }
    }
  }

  @override
  void dispose() {
    routeObserver.unsubscribe(this);
    _youtubePlayerController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return Scaffold(
      appBar: CustomAppBar(
        title: course?.title ?? '',
        onBackPressed: () {
          if (course != null) {
            final detailState =
                ref.read(courseDetailProvider(course!.id!)).valueOrNull;
            context.pop({course?.id: course?.progress});
            ref.read(myCourseProvider.notifier).updateProgress(
                  detailState?.course?.progress ?? 0,
                  (course?.id ?? 0).toString(),
                );
          }
        },
        actions: const [],
      ),
      body: Column(
        children: [
          TabBarWidget(
            tabController: _tabController,
            listTabLabel: tabLabel,
            onTap: null,
          ),
          Expanded(
            child: Builder(
              builder: (context) {
                final detailState =
                    ref.watch(courseDetailProvider(course!.id!)).valueOrNull;
                return TabBarView(
                  controller: _tabController,
                  children: [
                    courseOverviewWidget(detailState),
                    curriculumTab(course!),
                    quizTab(detailState),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget courseOverviewWidget(CourseDetailState? detailState) {
    return CourseOverview(
      whatWillYouLearn: detailState?.course?.fieldBody,
      description: detailState?.course?.fieldBody,
    );
  }

  Widget quizTab(CourseDetailState? detailState) {
    return QuizCourseScreen(
      chapters: course?.listChapters,
      permission: detailState?.hasPermissionCourse,
      course: course,
    );
  }

  Widget curriculumTab(
    Course course,
  ) {
    return Builder(
      builder: (context) {
        final detailState =
            ref.watch(courseDetailProvider(course.id!)).valueOrNull;
        if (detailState?.isLoadingChapter ?? false) {
          return const LoadingIndicator();
        }

        return SingleChildScrollView(
          child: Builder(
            builder: (context) {
              final progress = course.progress;
              return Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (progress != null && progress > 0.0)
                    progressBarCourse(progress, context),
                  if (detailState?.chapters.isNotEmpty ?? false)
                    Padding(
                      padding:
                          const EdgeInsets.all(Dimensions.paddingSizeDefault),
                      child: CourseCurriculum(
                        chapters: detailState?.chapters ?? [],
                      ),
                    ),
                ],
              );
            },
          ),
        );
      },
    );
  }

  Widget progressBarCourse(double progress, BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Row(
        children: [
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(
                right: Dimensions.paddingSizeDefault,
              ),
              child: LinearProgressIndicator(
                value: progress,
                minHeight: 10,
                semanticsLabel: 'progress courses',
                backgroundColor: Theme.of(context).highlightColor,
                borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              ),
            ),
          ),
          Text(
            '${(progress * 100).toInt()}%',
            style: Theme.of(context).textTheme.labelLarge,
          ),
        ],
      ),
    );
  }
}
