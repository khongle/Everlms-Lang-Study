import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:everlms/feature/quiz/presentation/widgets/labeled_checkbox.dart';
import 'package:everlms/feature/quiz/presentation/widgets/labeled_radio_button.dart';
import 'package:everlms/feature/quiz/presentation/widgets/short_question_widget.dart';
import 'package:everlms/feature/quiz/presentation/widgets/time_line.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';

class QuizQuestionReview extends StatelessWidget {
  const QuizQuestionReview({
    required this.questionsList,
    required this.givenAnswerList,
    required this.ansList,
    super.key,
  });

  final List<Questions> questionsList;
  final List<GivenAnswer> givenAnswerList;
  final List<bool> ansList;

  @override
  Widget build(BuildContext context) {
    return Timeline(
      lineColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.06),
      indicators: _indicatorList(context),
      children: _questionWidgets(),
    );
  }

  List<Widget> _indicatorList(BuildContext context) {
    return List.generate(
      ansList.length,
      (index) => questionsList[index].questionType == QuestionType.shortQuestion
          ? _indicatorItem(true, context)
          : _indicatorItem(ansList[index], context),
    );
  }

  Widget _indicatorItem(bool isCorrect, BuildContext context) => isCorrect
      ? CircleAvatar(
          backgroundColor: AppColors.success,
          child: SvgPicture.asset(
            Images.successful,
            height: 11,
            colorFilter: ColorFilter.mode(
              Theme.of(context).colorScheme.surface,
              BlendMode.srcIn,
            ),
          ),
        )
      : CircleAvatar(
          backgroundColor: Colors.red,
          child: SvgPicture.asset(
            Images.cancel,
            height: 11,
            colorFilter: ColorFilter.mode(
              Theme.of(context).colorScheme.surface,
              BlendMode.srcIn,
            ),
          ),
        );

  List<Widget> _questionWidgets() => List.generate(
        questionsList.length,
        (index) {
          final q = questionsList[index];
          if (q.questionType == QuestionType.defaultQuestion) {
            return SizedBox(
              width: 290,
              child: LabeledRadioButton(
                indexQuestion: index,
                question: q,
                isCorrectAnsSelected: (value) {},
                isClickable: false,
                indexRight: givenAnswerList[index].question.correctAnswerIndex,
                resultIndex: givenAnswerList[index].selectedIndexes,
              ),
            );
          }
          if (q.questionType == QuestionType.mcq) {
            return SizedBox(
              width: 290,
              child: LabeledCheckbox(
                question: q,
                isCorrectAnsSelected: (value) {},
                isClickable: false,
                resultIndexes: givenAnswerList[index].selectedIndexes,
              ),
            );
          }
          if (q.questionType == QuestionType.shortQuestion) {
            return ShortQuestionWidget(
              isDisabled: true,
              question: q,
            );
          }
          return const SizedBox.shrink();
        },
      );
}
