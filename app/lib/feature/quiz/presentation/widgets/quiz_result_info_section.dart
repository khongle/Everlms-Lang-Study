import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/quiz/data/models/quiz.dart';
import 'package:everlms/feature/quiz/data/models/quiz_result.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';

class QuizResultInfoSection extends ConsumerWidget {
  const QuizResultInfoSection({super.key});

  bool _isUserPassed(Quiz quiz, QuizResult? quizResult) {
    final score = double.tryParse(quizResult?.fieldScore.trim() ?? '') ?? 0.0;
    final threshold = double.tryParse(quiz.fieldThreshold?.trim() ?? '') ?? 0.0;
    return score >= threshold;
  }

  String _resultText(Quiz quiz, QuizResult? quizResult) =>
      _isUserPassed(quiz, quizResult)
          ? 'Congratulation ! you have passed the quiz exam'
          : 'Sorry, You have failed the quiz exam';

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final quizState = ref.watch(quizProvider).valueOrNull;
    final dataQuiz = quizState?.dataQuiz;
    final quizResult = quizState?.quizResult;
    if (dataQuiz?.quiz == null) return const SizedBox.shrink();
    final quiz = dataQuiz!.quiz!;

    return Container(
      height: 100,
      width: MediaQuery.of(context).size.width,
      color: Theme.of(context).primaryColor.withValues(alpha: 0.06),
      child: Stack(
        alignment: Alignment.topRight,
        children: [
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                _resultText(quiz, quizResult),
                style: robotoRegular.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                ),
              ),
              const SizedBox(height: Dimensions.paddingSizeSmall),
              _headerUI(quiz, quizResult, context),
            ],
          ),
          SizedBox(
            height: 148,
            width: 100,
            child: SvgPicture.asset(Images.quizBackRound),
          ),
        ],
      ),
    );
  }

  Row _headerUI(Quiz quiz, QuizResult? quizResult, BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _scoreCard(
          title: quizResult?.fieldScore ?? '0',
          subtitle: tl(context).your_score,
          context: context,
        ),
        const SizedBox(width: Dimensions.paddingSizeSmall),
        _scoreCard(
          title: quizResult?.fieldResult ?? '',
          subtitle: tl(context).your_result,
          context: context,
        ),
      ],
    );
  }

  Widget _scoreCard({
    required String title,
    required String subtitle,
    required BuildContext context,
  }) {
    return Container(
      width: 100,
      height: 55,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(6),
        color: Theme.of(context).cardColor,
      ),
      child: Padding(
        padding: const EdgeInsets.all(8),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            FittedBox(
              child: Text(
                title,
                style: robotoSemiBold.copyWith(
                  color: Theme.of(context).primaryColor,
                  fontSize: Dimensions.fontSizeDefault,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            FittedBox(
              child: Text(
                subtitle,
                style: robotoRegular.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: Dimensions.fontSizeSmall,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
