import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/quiz/presentation/screens/item_quiz.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class QuizCourseScreen extends ConsumerStatefulWidget {
  const QuizCourseScreen({
    required this.chapters,
    required this.permission,
    super.key,
    this.course,
  });
  final List<Chapter>? chapters;
  final Course? course;
  final bool? permission;

  @override
  ConsumerState<QuizCourseScreen> createState() => _QuizCourseScreenState();
}

class _QuizCourseScreenState extends ConsumerState<QuizCourseScreen>
    with RouteAware {
  int? _expandedIndex;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void didPopNext() {
    final notifier = ref.read(quizProvider.notifier);
    notifier.getQuizDataByCourseId(widget.course?.id.toString() ?? '');
    for (final chapter in widget.course?.listChapters ?? []) {
      notifier.getQuizDataByChapterId(chapter.id.toString());
    }
  }

  @override
  void dispose() {
    super.dispose();
    routeObserver.unsubscribe(this);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeLarge,
        vertical: Dimensions.paddingSizeDefault,
      ),
      child: Builder(
        builder: (context) {
          final quizState = ref.watch(quizProvider).valueOrNull;
          if (quizState == null || quizState.isLoading) {
            return const LoadingIndicator();
          }
          return SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                customTitle(tl(context).quiz_in_course),
                quizUserCourse(quizState),
                customTitle(tl(context).quiz_in_chapter),
                quizUserChapter(quizState),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget customTitle(String title) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeDefault),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleMedium,
      ),
    );
  }

  Widget quizUserChapter(QuizState quizState) {
    final chapters = widget.chapters ?? [];
    final quizChapter = quizState.quizModelChapter;
    if (quizChapter.values.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      children: List.generate(chapters.length, (index) {
        final chapterTitle = chapters[index].title;
        final chapterId = chapters[index].id.toString();
        final quiz = quizChapter[chapterId]?.data;
        if (quiz == null) {
          return const SizedBox.shrink();
        }
        return Padding(
          padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
          child: Column(
            children: [
              Stack(
                children: [
                  Container(
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      border: Border.all(
                        color: Theme.of(context)
                            .textTheme
                            .bodyLarge!
                            .color!
                            .withValues(alpha: 0.6),
                      ),
                      color: Theme.of(context).scaffoldBackgroundColor,
                      borderRadius: const BorderRadius.all(
                        Radius.circular(Dimensions.radiusSmall),
                      ),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.only(
                        right: Dimensions.paddingSizeLarge,
                      ),
                      child: ListTile(
                        title: Text(
                          '${tl(context).chapter}: ${index + 1} $chapterTitle',
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      ),
                    ),
                  ),
                  ExpansionTile(
                    key: ValueKey(_expandedIndex == index),
                    title: const Text(''),
                    backgroundColor: Colors.transparent,
                    collapsedBackgroundColor: Colors.transparent,
                    shape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    collapsedShape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    childrenPadding: const EdgeInsets.only(
                      top: Dimensions.paddingSizeDefault,
                    ),
                    expandedCrossAxisAlignment: CrossAxisAlignment.start,
                    initiallyExpanded: _expandedIndex == index,
                    onExpansionChanged: (value) {
                      Future.delayed(const Duration(milliseconds: 500), () {
                        setState(() {
                          _expandedIndex = value ? index : null;
                        });
                      });
                    },
                    children: [
                      // quiz in chapter
                      if (quiz.isNotEmpty)
                        Column(
                          children: List.generate(
                            quiz.length,
                            (index) => ItemQuiz(
                              color: Colors.grey.shade200,
                              quiz: quiz[index],
                              permission: widget.permission ?? false,
                            ),
                          ),
                        )
                      else
                        Text(tl(context).this_chapter_has_no_quiz),
                      // Column(crossAxisAlignment: CrossAxisAlignment.start, children: List.generate(chapters[index].lessons?.length ?? 0, (indexLesson){
                      //   final lesson = chapters[index].lessons?[indexLesson];
                      //   return Padding(
                      //     padding: const EdgeInsets.symmetric(vertical:  8.0),
                      //     child: Text('Lesson ${indexLesson + 1}: ${lesson?.title}'),
                      //   );

                      // }),)
                    ],
                  ),
                ],
              ),
              // TextButton(
              //   onPressed: () {
              //     Navigator.of(context).push(MaterialPageRoute(builder: (_) => ChapterQuizWidget(chapter: chapters[index],), transition: Transition.downToUp);
              //   },
              //   style: TextButton.styleFrom(
              //     textStyle: robotoMedium,
              //     padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              //     shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              //     side: const BorderSide(color: Colors.black38),

              //     )),
              //   child: SizedBox(
              //       width: MediaQuery.of(context).size.width,
              //       child: Text('Chapter ${index + 1} $chapterTitle')),
              // ),
              // Row(
              //   children: [
              //     const Expanded(
              //         child: Divider(
              //       thickness: 0.5,
              //       indent: Dimensions.paddingSizeDefault,
              //       endIndent: Dimensions.paddingSizeDefault,
              //     )),
              //     Text(
              //       chapterTitle ?? '',
              //       style: robotoRegular,
              //     ),
              //     const Expanded(
              //         child: Divider(
              //       thickness: 0.5,
              //       indent: Dimensions.paddingSizeDefault,
              //       endIndent: Dimensions.paddingSizeDefault,
              //     )),
              //   ],
              // ),
              // Column(children: List.generate(quiz.length, (index) => ItemQuiz(quiz: quiz[index],)),),
              // ItemQuiz(quiz: quiz),
            ],
          ),
        );
      }),
    );
  }

  Widget quizUserCourse(QuizState quizState) {
    final quizCourse = quizState.quizModelCourse?.data ?? [];
    if (quizCourse.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: quizCourse
          .map(
            (quiz) => Padding(
              padding:
                  const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
              child: ItemQuiz(
                quiz: quiz,
                permission: widget.permission ?? false,
              ),
            ),
          )
          .toList(),
    );
  }
}
