import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/class_room/presentation/providers/better_video_player.dart';
import 'package:everlms/feature/class_room/presentation/widgets/video_player.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/features.dart';
import 'package:everlms/feature/course_details/presentation/providers/course_detail_provider.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_curriculum_not_purchase.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_features.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_overview.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_practice_quiz.dart';
import 'package:everlms/feature/course_details/presentation/widgets/tab_bar_widget.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/instructor.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer_animation/shimmer_animation.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';

class CourseDetailsNotPurchaseScreen extends ConsumerStatefulWidget {
  const CourseDetailsNotPurchaseScreen({this.courseArg, super.key});
  final dynamic courseArg;

  @override
  ConsumerState<CourseDetailsNotPurchaseScreen> createState() =>
      _CourseDetailsNotPurchaseScreenState();
}

class _CourseDetailsNotPurchaseScreenState
    extends ConsumerState<CourseDetailsNotPurchaseScreen>
    with TickerProviderStateMixin, RouteAware {
  late Course course;
  late String courseId;
  late TabController _tabBarController;
  YoutubePlayerController? _youtubePlayerController;
  late List<String> _tabLabel;
  bool _tabBarControllerInitialized = false;
  late final MyBetterVideoPlayerController _videoController;

  @override
  void initState() {
    super.initState();
    course = widget.courseArg as Course;
    courseId = course.id.toString();
    _videoController = MyBetterVideoPlayerController();

    // Load quiz data by course
    ref.read(quizProvider.notifier).getQuizDataByCourseId(courseId);

    // Load chapters, then check permissions and quiz by chapter
    ref
        .read(courseDetailProvider(course.id ?? 0).notifier)
        .loadChapters(course)
        .then((_) {
      final chapters = ref
              .read(courseDetailProvider(course.id ?? 0))
              .valueOrNull
              ?.chapters ??
          [];
      ref
          .read(courseDetailProvider(course.id ?? 0).notifier)
          .checkListLessonPermission(courseId);
      for (final chapter in chapters) {
        ref
            .read(quizProvider.notifier)
            .getQuizDataByChapterId(chapter.id.toString());
      }
    });

    // Check payment and permissions
    ref
        .read(courseDetailProvider(course.id ?? 0).notifier)
        .lmsCheckCoursePayment(courseId);
    ref
        .read(courseDetailProvider(course.id ?? 0).notifier)
        .checkCoursePermission(courseId);

    initCoursePreview();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _tabLabel = [
      tl(context).information,
      tl(context).curriculum,
      tl(context).quiz,
    ];
    if (!_tabBarControllerInitialized) {
      _tabBarController = TabController(length: _tabLabel.length, vsync: this);
      _tabBarControllerInitialized = true;
    }
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void didPopNext() {
    if (!(course.isFree ?? true)) {
      ref
          .read(courseDetailProvider(course.id ?? 0).notifier)
          .checkCoursePermission(courseId);
      ref
          .read(courseDetailProvider(course.id ?? 0).notifier)
          .lmsCheckCoursePayment(courseId);
    }
    super.didPopNext();
  }

  void initCoursePreview() {
    final type = course.fieldCoursePreview?.type;
    final url = course.fieldCoursePreview?.url;
    if (url != null) {
      if (type == 'remote video') {
        _youtubePlayerController = YoutubePlayerController(
          initialVideoId: YoutubePlayer.convertUrlToId(url) ?? '',
        );
      } else if (type == 'video') {
        _videoController.playVideo(url);
      }
    }
  }

  @override
  void dispose() {
    _videoController.stopPlayer().whenComplete(_videoController.dispose);
    routeObserver.unsubscribe(this);
    _youtubePlayerController?.dispose();
    _tabBarController.dispose();
    super.dispose();
  }

  void updateStartCourse() {
    ref
        .read(courseDetailProvider(course.id ?? 0).notifier)
        .checkStartCourse(courseId)
        .then((_) {
      course.isStart = ref
          .read(courseDetailProvider(course.id ?? 0))
          .valueOrNull
          ?.isStartedCourse;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_youtubePlayerController == null) {
      return mainUI(context);
    }
    return YoutubePlayerBuilder(
      player: YoutubePlayer(controller: _youtubePlayerController!),
      builder: (contextVideo, player) => mainUI(context, player: player),
    );
  }

  Scaffold mainUI(BuildContext context, {Widget? player}) {
    final courseState =
        ref.watch(courseDetailProvider(course.id ?? 0)).valueOrNull;
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).back,
        centerTitle: false,
        actions: const [],
      ),
      body: Column(
        children: [
          TabBarWidget(
            tabController: _tabBarController,
            listTabLabel: _tabLabel,
            onTap: null,
          ),
          Flexible(
            child: TabBarView(
              controller: _tabBarController,
              children: [
                information(context, player),
                curriculumNotPurchased(courseState),
                quizTab(courseState),
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: buttonBottom(courseState),
    );
  }

  Widget quizTab(CourseDetailState? courseState) {
    return QuizCourseScreen(
      course: course,
      chapters: courseState?.chapters,
      permission: courseState?.hasPermissionCourse,
    );
  }

  Widget curriculumNotPurchased(CourseDetailState? courseState) {
    if (courseState?.isLoadingChapter ?? false) {
      return const LoadingIndicator();
    }
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: SingleChildScrollView(
        child: CourseCurriculumNotPurchase(
          chapters: courseState?.chapters ?? [],
          courseId: courseId,
          course: course,
        ),
      ),
    );
  }

  Widget information(BuildContext context, Widget? player) {
    final instructorAsync =
        ref.watch(instructorByTeacherIdProvider(course.teacherId ?? ''));
    return SingleChildScrollView(
      child: Column(
        children: [
          CourseFeatures(
            feature: Features(
              courseName: course.title,
              rating: course.totalRating,
              totalLesson: course.totalLessons.toString(),
              totalQuiz: course.totalQuizzes.toString(),
              totalEnroll: course.fieldLearnerNumber,
              category: course.fieldCategory,
              updated: course.changed,
              language: course.fieldLanguage,
            ),
          ),
          CourseOverview(
            whatWillYouLearn: course.fieldWhatYouLearn,
            requirements: course.fieldRequirements,
            description: course.fieldBody,
          ),
          coursePreview(player),
          instructorAsync.when(
            loading: () => Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: instructorShimmer(),
            ),
            error: (_, __) => const SizedBox.shrink(),
            data: (instructorData) {
              if (instructorData == null) return const SizedBox.shrink();
              return Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: instructor(instructorData),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget coursePreview(Widget? player) {
    final type = course.fieldCoursePreview?.type;
    final url = course.fieldCoursePreview?.url;
    if (type == null || url == null) return const SizedBox.shrink();
    if (type == 'remote video' && _youtubePlayerController != null) {
      return player ?? Container();
    }
    if (type == 'video') {
      return CourseVideoPlayer(
        videoUrl: url,
        videoController: _videoController,
      );
    }
    if (type == 'image') {
      return CustomImage(image: course.fieldCoursePreview?.url);
    }
    return const SizedBox.shrink();
  }

  Widget instructor(Instructor? instructorData) {
    return InkWell(
      onTap: () =>
          context.push(AppRoutes.instructorDetails, extra: instructorData),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            tl(context).instructors,
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Row(
            children: [
              ClipOval(
                child: CustomImage(
                  image: instructorData?.image ?? '',
                  height: 80,
                  width: 80,
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Flexible(
                      child: Text(
                        instructorData?.name ?? '',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                    ),
                    Flexible(child: Text(instructorData?.expertise ?? '')),
                  ],
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeMint),
              const Icon(
                Icons.chevron_right,
                size: Dimensions.iconSizeExtraLarge,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget instructorShimmer() {
    return Row(
      children: [
        ClipOval(
          child: Shimmer(
            child:
                Container(height: 80, width: 80, color: Colors.grey.shade100),
          ),
        ),
        const SizedBox(width: Dimensions.paddingSizeSmall),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Shimmer(
                child: Container(
                  height: 20,
                  width: 200,
                  margin: const EdgeInsets.only(
                    bottom: Dimensions.paddingSizeSmall,
                  ),
                  color: Colors.grey.shade100,
                ),
              ),
              Shimmer(
                child: Container(
                  height: 20,
                  width: double.infinity,
                  color: Colors.grey.shade100,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget buttonBottom(CourseDetailState? courseState) {
    final isBought = courseState?.isBought;
    if (isBought == null) return const SizedBox.shrink();

    final userData = ref.read(userProfileProvider).valueOrNull?.userData;
    final isMembership = userData?.roles == AppConstants.membership;

    if ((course.isFree ?? false) || isBought || isMembership) {
      return CustomButton(
        onPressed: () async {
          context.replace(
            AppRoutes.getCourseDetailsScreenRoute(),
            extra: {'course': course},
          );
          Future.microtask(() async {
            await ref
                .read(courseDetailProvider(course.id ?? 0).notifier)
                .startCourse(courseId);
            await ref.read(myCourseProvider.notifier).loadMyCourses();
            await ref.read(homeProvider.notifier).refreshAllCourses();
            updateStartCourse();
          });
        },
        buttonText: tl(context).start_learning,
      );
    }

    return CustomButton(
      onPressed: () async {
        LoadingDialog.show(context);
        await ref.read(cartProvider.notifier).emptyCart();
        await ref.read(cartProvider.notifier).addToCart(course.id ?? 0);
        if (!mounted) return;
        LoadingDialog.hide(context);
        context.push(
          AppRoutes.getCartScreen(),
          extra: {'course': course},
        );
      },
      buttonText: 'Enroll',
    );
  }
}
