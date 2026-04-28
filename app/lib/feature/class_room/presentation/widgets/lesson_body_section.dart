import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/quiz/presentation/screens/item_quiz.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class LessonBodySection extends ConsumerWidget {
  const LessonBodySection({required this.lesson, super.key});

  final Lesson lesson;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final quizState = ref.watch(quizProvider).valueOrNull;
    return ColoredBox(
      color: Theme.of(context).scaffoldBackgroundColor,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
            child: Html(
              data: lesson.body,
              style: {'p': Style(margin: Margins.zero)},
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
            ),
            child: Text(
              'Lesson: ${lesson.title}',
              style: robotoSemiBold.copyWith(
                fontSize: Dimensions.fontSizeExtraLarge,
              ),
            ),
          ),
          if (quizState?.quizModelLesson?.data.isNotEmpty ?? false)
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: ItemQuiz(quiz: quizState!.quizModelLesson?.data.first),
            ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
        ],
      ),
    );
  }
}
