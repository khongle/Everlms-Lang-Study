import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/class_room/presentation/providers/classroom_provider.dart';
import 'package:everlms/feature/class_room/presentation/screens/learning_screen.dart';
import 'package:everlms/feature/class_room/presentation/widgets/classroom_button.dart';
import 'package:everlms/feature/course_details/presentation/providers/course_detail_provider.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LandingLearningScreen extends ConsumerStatefulWidget {
  const LandingLearningScreen({
    required this.indexChapter,
    required this.indexLesson,
    required this.listChapter,
    required this.isOnline,
    required this.fromPage,
    super.key,
  });
  final String? fromPage;
  final int indexChapter;
  final int indexLesson;
  final List<Chapter> listChapter;
  final bool isOnline;

  @override
  ConsumerState<LandingLearningScreen> createState() =>
      _LandingLearningScreenState();
}

class _LandingLearningScreenState extends ConsumerState<LandingLearningScreen> {
  late ValueNotifier indexChapter;
  final indexPage = ValueNotifier<int>(0);

  late List<Chapter> listChapter;
  final List<Lesson> _listLesson = [];
  final List<int> _lessonToChapter = [];

  late PageController pageController;
  final ValueNotifier<bool> isYoutubeFullscreen = ValueNotifier(false);

  @override
  void initState() {
    indexChapter = ValueNotifier<int>(widget.indexChapter);
    listChapter = widget.listChapter;
    _lessonMap();
    super.initState();
  }

  @override
  void dispose() {
    indexChapter.dispose();
    indexPage.dispose();
    pageController.dispose();
    super.dispose();
  }

  void _lessonMap() {
    var pageIndex = 0;
    for (var chapterIndex = 0;
        chapterIndex < listChapter.length;
        chapterIndex++) {
      final lessons = listChapter[chapterIndex].lessons ?? [];
      for (var lessonIndex = 0; lessonIndex < lessons.length; lessonIndex++) {
        final lesson = lessons[lessonIndex];
        _listLesson.add(lesson);
        if (chapterIndex == widget.indexChapter &&
            lessonIndex == widget.indexLesson) {
          indexPage.value = pageIndex;
        }
        _lessonToChapter.add(chapterIndex);
        pageIndex++;
      }
    }
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _activateLesson(indexPage.value, widget.fromPage ?? '');
    });
    pageController = PageController(initialPage: indexPage.value);
  }

  @override
  Widget build(BuildContext context) {
    ref.watch(classroomProvider);
    return ValueListenableBuilder(
      valueListenable: isYoutubeFullscreen,
      builder: (context, isFs, child) {
        return Scaffold(
          appBar: isFs ? null : appBarLearning(context),
          body: PageView.builder(
            physics: const BouncingScrollPhysics(
              parent: PageScrollPhysics(),
            ),
            allowImplicitScrolling: true,
            controller: pageController,
            onPageChanged: (index) {
              indexChapter.value = _lessonToChapter[index];
              indexPage.value = index;
              _activateLesson(index, widget.fromPage ?? '');
            },
            itemCount: _listLesson.length,
            itemBuilder: (context, index) {
              return LearningScreen(
                key: PageStorageKey(_listLesson[index].id),
                lesson: _listLesson[index],
                isOnline: widget.isOnline,
                fromPage: widget.fromPage,
                fullscreenNotifier: isYoutubeFullscreen,
              );
            },
          ),
        );
      },
    );
  }

  void _activateLesson(int index, String fromPage) {
    final lesson = _listLesson[index];

    if (!lesson.isCompleted && widget.fromPage == fromPage && widget.isOnline) {
      ref
          .read(courseDetailRepositoryProvider)
          .lmsUpdateStatusLesson(lessonId: lesson.id.toString());
      ref.read(myCourseProvider.notifier).loadMyCourses();
    }
  }

  AppBar appBarLearning(BuildContext context) {
    return AppBar(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      title: ValueListenableBuilder(
        valueListenable: indexChapter,
        builder: (context, index, _) => RichText(
          text: TextSpan(
            text: 'Chapter ${indexChapter.value + 1}\n',
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeLarge,
              color: Theme.of(context).colorScheme.onSurface,
            ),
            children: [
              TextSpan(
                text: '${widget.listChapter[index].title}',
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
            ],
          ),
        ),
      ),
      centerTitle: false,
      leading: IconButton(
        onPressed: () {
          context.pop();
          final isFs =
              ref.read(classroomProvider).valueOrNull?.isFullScreen ?? false;
          if (isFs) {
            ref.read(classroomProvider.notifier).toggleFullScreen();
          }
        },
        icon: const Icon(Icons.arrow_back),
      ),
      actions: [
        ValueListenableBuilder(
          valueListenable: indexPage,
          builder: (context, index, _) => lessonAppBarAction(
            context,
            ref,
            _listLesson[index],
            isOnline: widget.isOnline,
          ),
        ),
      ],
    );
  }
}
