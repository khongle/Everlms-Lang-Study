import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/feature/quiz/data/models/data.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class QuizDetailScreen extends ConsumerWidget {
  const QuizDetailScreen({this.quiz, super.key});
  final DataQuiz? quiz;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).back,
        centerTitle: false,
      ),
      body: Padding(
        padding: const EdgeInsets.all(15),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              quiz?.quiz?.name ?? '',
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeOverLarge,
              ),
            ),
            const SizedBox(height: 15),
            Padding(
              padding: const EdgeInsets.only(left: 10),
              child: Text(
                '• ${tl(context).question_count}: ${quiz?.questions.length}',
                style: robotoRegular.copyWith(
                  fontSize: 14,
                  color: Theme.of(context)
                      .textTheme
                      .titleMedium!
                      .color!
                      .withValues(alpha: 0.6),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(left: 10),
              child: Text(
                '• Time limit (minutes): ${quiz?.quiz?.duration}',
                style: robotoRegular.copyWith(
                  fontSize: 14,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.6),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(left: 10),
              child: Text(
                '• Threshold (%): ${quiz?.quiz?.fieldThreshold}',
                style: robotoRegular.copyWith(
                  fontSize: 14,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.6),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(left: 10),
              child: Text(
                '• Take quiz number: ${quiz?.quiz?.takeQuizNumber}',
                style: robotoRegular.copyWith(
                  fontSize: 14,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.6),
                ),
              ),
            ),
            const Spacer(),
          ],
        ),
      ),
      bottomNavigationBar: CustomButton(
        buttonText: tl(context).start_quiz,
        onPressed: () async {
          final notifier = ref.read(quizProvider.notifier);
          notifier.setDataQuiz(quiz);
          notifier.setDuration(
            int.tryParse(quiz?.quiz?.duration ?? '0') ?? 0,
          );
          final value = await notifier.startQuiz(
            timeMinutesLimit: int.tryParse(quiz?.quiz?.duration ?? '0') ?? 0,
            quizId: quiz?.quiz?.id ?? '',
            takeQuizFrom: quiz?.quiz?.takeQuizFrom ?? '',
          );
          if (!context.mounted) return;
          if (value) {
            context.push(AppRoutes.quizStartPage, extra: quiz);
            return;
          } else {
            final givenAnswerList = notifier.getListAnswer();
            LoadingDialog.show(context);
            await notifier.submitQuiz(
              startTime: DateTime.now().millisecondsSinceEpoch,
              endTime: DateTime.now().millisecondsSinceEpoch,
              quizId: quiz?.quiz?.id ?? '',
              takeQuizFrom: quiz?.quiz?.takeQuizFrom ?? '',
              listAnswer: givenAnswerList ?? [],
            );
            if (!context.mounted) return;
            LoadingDialog.hide(context);
            context.replace(
              AppRoutes.quizResultScreen,
              extra: givenAnswerList,
            );
          }
        },
      ),
    );
  }
}
