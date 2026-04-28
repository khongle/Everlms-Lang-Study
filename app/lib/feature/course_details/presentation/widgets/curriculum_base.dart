import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/quiz/data/models/quiz.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';

class CurriculumBase extends StatelessWidget {
  const CurriculumBase({
    required this.listChapters,
    required this.showProgressChapter,
    super.key,
    this.onTapLesson,
    this.onTapLessonHasPermission,
  });
  final List<Chapter>? listChapters;
  final Function(Lesson lesson)? onTapLesson;
  final bool showProgressChapter;
  final Function(int indexChapter, int indexLesson)? onTapLessonHasPermission;

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      shrinkWrap: true,
      itemCount: listChapters?.length,
      physics: const NeverScrollableScrollPhysics(),
      itemBuilder: (context, index) {
        return Padding(
          padding: const EdgeInsets.only(
            bottom: Dimensions.paddingSizeDefault,
          ),
          child: curriculumItem(context, listChapters![index], index),
        );
      },
    );
  }

  Widget curriculumItem(
    BuildContext context,
    Chapter chapter,
    int indexChapter,
  ) {
    final listLesson = chapter.lessons ?? [];
    return Container(
      alignment: Alignment.center,
      decoration: BoxDecoration(
        border: Border.all(
          color: Theme.of(context)
              .textTheme
              .bodyLarge!
              .color!
              .withValues(alpha: 0.06),
        ),
        color: Theme.of(context).scaffoldBackgroundColor,
        borderRadius: const BorderRadius.all(
          Radius.circular(Dimensions.radiusSmall),
        ),
      ),
      child: ExpansionTile(
        expansionAnimationStyle: const AnimationStyle(
          curve: Curves.easeInOut,
          reverseCurve: Curves.easeInOut,
          duration: Duration(milliseconds: 300),
          reverseDuration: Duration(milliseconds: 300),
        ),
        shape: RoundedRectangleBorder(
          side: BorderSide(
            color: Theme.of(context).primaryColor,
          ),
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
        collapsedShape: RoundedRectangleBorder(
          side: BorderSide(
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: 0.6),
          ),
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
        childrenPadding: EdgeInsets.zero,
        title: Text(
          "${tl(context).chapter} ${indexChapter + 1}: ${chapter.title ?? ''}",
          style: Theme.of(context).textTheme.bodyLarge,
        ),
        subtitle: showProgressChapter
            ? Text(
                '${chapter.lessons?.where((e) => e.isCompleted).length} / ${chapter.totalLesson} ${tl(context).complete}',
                style: Theme.of(context).textTheme.bodyMedium,
              )
            : null,
        initiallyExpanded: indexChapter == 0,
        children: [
          // Divider(
          //     height: 1,
          //     color: Theme.of(context)
          //         .textTheme
          //         .bodyLarge!
          //         .color!
          //         .withValues(alpha: 0.06)),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 15),
            child: listLesson.isNotEmpty
                ? ListView.builder(
                    shrinkWrap: true,
                    itemCount: listLesson.length,
                    physics: const NeverScrollableScrollPhysics(),
                    itemBuilder: (context, index) {
                      return _item(
                        context,
                        lesson: listLesson[index],
                        listLesson: listLesson,
                        currentIndexChapter: indexChapter,
                        currentIndexLesson: index,
                        sectionID: chapter.id.toString(),
                      );
                    },
                  )
                : const SizedBox.shrink(),
          ),
          // Padding(
          //   padding: const EdgeInsets.symmetric(horizontal: 15.0),
          //   child: chapter.lessons != null || chapter.quizes != null
          //       ? ListView.builder(
          //           shrinkWrap: true,
          //           itemCount: chapter.lessons?.length == null
          //               ? 0
          //               : ((chapter.lessons?.length ?? 0) +
          //                   (chapter.quizes?.length ?? 0)),
          //           itemBuilder: (context, index) {
          //             return index >= chapter.lessons!.length
          //                 ? quizButton(
          //                     chapter.quizes![index - chapter.lessons!.length],
          //                     context)
          //                 : _item(context, chapter.lessons![index],
          //                     chapter.id.toString());
          //           },
          //         )
          //       : const SizedBox(),
          // ),
        ],
      ),
    );
  }

  Widget quizButton(Quiz quiz, BuildContext context) => CustomButton(
        onPressed: () {
          // return Navigator.of(context).push(MaterialPageRoute(
          //     builder: (context) => QuizDetailScreen(quiz: quiz),
          //   ));
        },
        height: 25,
        buttonText: 'Quiz',
      );

  // new item course widget
  Widget _item(
    BuildContext context, {
    required Lesson lesson,
    required List<Lesson> listLesson,
    required int currentIndexChapter,
    required int currentIndexLesson,
    required String sectionID,
  }) {
    return ListTile(
      splashColor: Colors.transparent,
      onTap: () {
        if (onTapLesson != null) {
          onTapLesson?.call(lesson);
        } else if (onTapLessonHasPermission != null) {
          onTapLessonHasPermission?.call(
            currentIndexLesson,
            currentIndexChapter,
          );
        }
      },
      minTileHeight: 40,
      leading: SvgPicture.asset(
        [LessonType.embeddedVideo.value, LessonType.uploadVideo.value]
                .contains(lesson.type)
            ? Images.polygon
            : [LessonType.meet.value, LessonType.zoom.value]
                    .contains(lesson.type)
                ? Images.microPhone
                : [
                    LessonType.ggSlides.value,
                    LessonType.scorm.value,
                    LessonType.microsoftDocs.value,
                    LessonType.pdf.value,
                    LessonType.h5p.value,
                  ].contains(lesson.type)
                    ? Images.document
                    : Images.polygon,
        colorFilter: ColorFilter.mode(
          Theme.of(context).brightness == Brightness.dark
              ? Colors.grey
              : Theme.of(context).primaryColor,
          BlendMode.srcIn,
        ),
      ),
      horizontalTitleGap: 0,
      contentPadding: EdgeInsets.zero,
      title: Text(
        lesson.title,
        overflow: TextOverflow.ellipsis,
        softWrap: false,
        maxLines: 2,
        style: Theme.of(context).textTheme.bodyMedium,
      ),
      trailing: lesson.isCompleted
          ? Text(
              'Done',
              style: Theme.of(context).textTheme.labelSmall!.copyWith(
                    color: Theme.of(context).primaryColor,
                    fontWeight: FontWeight.bold,
                  ),
            )
          : null,
    );
  }
}
