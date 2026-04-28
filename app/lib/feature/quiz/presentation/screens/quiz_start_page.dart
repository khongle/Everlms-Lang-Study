import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_default_dialog.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/quiz/data/models/data.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:everlms/feature/quiz/data/models/quiz.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/quiz/presentation/widgets/labeled_checkbox.dart';
import 'package:everlms/feature/quiz/presentation/widgets/labeled_radio_button.dart';
import 'package:everlms/feature/quiz/presentation/widgets/short_question_widget.dart';
import 'package:everlms/feature/quiz/presentation/widgets/time_line.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class QuizStartPage extends ConsumerStatefulWidget {
  const QuizStartPage({super.key});

  @override
  ConsumerState<QuizStartPage> createState() => QuizStartPageState();
}

class QuizStartPageState extends ConsumerState<QuizStartPage> {
  List<GivenAnswer> givenAnswerList = [];
  late Timer _timer;
  late int _remainingSeconds;
  bool isFirstTime = true;
  bool _isLoading = true;

  int currentIndex = 0;

  @override
  void initState() {
    super.initState();
    final quizState = ref.read(quizProvider).valueOrNull;
    final minutes = int.parse(quizState?.dataQuiz?.quiz?.duration ?? '0');
    final startTime = quizState?.startTime;
    final currentTime = DateTime.now().millisecondsSinceEpoch;
    _remainingSeconds = minutes * 60 -
        (startTime != null ? (currentTime - startTime) ~/ 1000 : 0);
    final notifier = ref.read(quizProvider.notifier);
    final savedAnswers = notifier.getListAnswer();
    if (savedAnswers != null) {
      isFirstTime = false;
      givenAnswerList = savedAnswers;
    }
    _isLoading = false;
    setState(() {});

    startTimer();
  }

  @override
  void dispose() {
    _timer.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final quizState = ref.watch(quizProvider).valueOrNull;
    final dataQuiz = quizState?.dataQuiz;
    final quiz = dataQuiz?.quiz;
    return Scaffold(
      appBar: PreferredSize(
        preferredSize: Size(MediaQuery.of(context).size.width, 60),
        child: _isLoading || quizState?.quizModelCourse == null || quiz == null
            ? AppBar()
            : appBar(context, quiz),
      ),
      body: _isLoading || dataQuiz == null || dataQuiz.quiz == null
          ? const LoadingIndicator()
          : mainUI(context, dataQuiz),
    );
  }

  void startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) async {
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
        });
      } else {
        _timer.cancel();
        final quizState = ref.read(quizProvider).valueOrNull;
        final quiz = quizState?.dataQuiz;
        final notifier = ref.read(quizProvider.notifier);
        LoadingDialog.show(context);
        await notifier.submitQuiz(
          startTime: DateTime.now().millisecondsSinceEpoch,
          endTime: DateTime.now().millisecondsSinceEpoch,
          quizId: quiz?.quiz?.id ?? '',
          takeQuizFrom: quiz?.quiz?.takeQuizFrom ?? '',
          listAnswer: givenAnswerList,
        );
        if (!mounted) return;
        LoadingDialog.hide(context);
        context.replace(
          AppRoutes.quizResultScreen,
          extra: givenAnswerList,
        );
      }
    });
  }

  String formatTime(int seconds) {
    final minutes = seconds ~/ 60;
    final remainingSeconds = seconds % 60;
    return '${minutes.toString().padLeft(2, '0')} m : ${remainingSeconds.toString().padLeft(2, '0')} s';
  }

  Widget appBar(BuildContext context, Quiz quiz) {
    return CustomAppBar(
      title: quiz.name,
      centerTitle: false,
      onBackPressed: () {
        customDialog(
          title: 'Exit quiz',
          middleTitle: "Your answer won't be saved unless you submit the quiz",
          textConfirm: 'Continue quiz',
          textCancel: 'Exit',
          onCancel: () {
            ref.read(quizProvider.notifier).clearDataQuizLocal();
            context.pop();
            context.pop();
          },
          onConfirm: () {},
          context: context,
        );
      },
      actions: [
        Container(
          alignment: Alignment.center,
          child: Text(
            formatTime(_remainingSeconds),
            style: robotoSemiBold.copyWith(
              color: Theme.of(context).colorScheme.onSurface,
              fontSize: Dimensions.fontSizeDefault,
            ),
          ),
        ),
        const SizedBox(width: Dimensions.paddingSizeDefault),
      ],
    );
  }

  List<Widget> indicatorList(List<Questions> questionList) {
    return List.generate(
      questionList.length,
      (index) => CircleAvatar(
        backgroundColor: Theme.of(context).primaryColor.withValues(alpha: 0.06),
        child: Text(
          '${index + 1}',
          style: robotoRegular.copyWith(color: Theme.of(context).primaryColor),
        ),
      ),
    );
  }

  Widget questionItemMCQ(Questions question, int index) => SizedBox(
        width: 290,
        child: LabeledCheckbox(
          question: question,
          isCorrectAnsSelected: (value) {
            // GivenAnswer tracking handled below
          },
          selectedIndexes: (value) {
            final givenAnswer = givenAnswerList[index];
            givenAnswer.selectedIndexes = value;
            givenAnswerList[index] = givenAnswer;
            ref.read(quizProvider.notifier).setListAnswer(givenAnswerList);
          },
          resultIndexes: givenAnswerList[index].selectedIndexes,
        ),
      );

  Widget questionItemDefault(Questions question, int index) {
    return LabeledRadioButton(
      indexQuestion: index,
      question: question,
      isCorrectAnsSelected: (value) {
        final answer = GivenAnswer(
          question: question,
          isCorrect: value,
          selectedIndexes: [-1],
        );
        givenAnswerList[index] = answer;
      },
      selectedIndex: (value) {
        givenAnswerList[index].selectedIndexes = value;
        ref.read(quizProvider.notifier).setListAnswer(givenAnswerList);
      },
      // selectedIndex: (int? value) {
      resultIndex: givenAnswerList[index].selectedIndexes,
    );
  }

  Widget shortQuestion(Questions question, int index) => ShortQuestionWidget(
        question: question,
        onTextChanged: (value) {
          final answer = GivenAnswer(
            question: question,
            isCorrect: true,
            selectedIndexes: [-1],
          );
          givenAnswerList[index] = answer;
        },
      );

  List<Widget> questionListWidgets(List<Questions> list) => List.generate(
        list.length,
        (index) =>
            list.elementAt(index).questionType == QuestionType.defaultQuestion
                ? questionItemDefault(list.elementAt(index), index)
                : list.elementAt(index).questionType == QuestionType.mcq
                    ? questionItemMCQ(list.elementAt(index), index)
                    : list.elementAt(index).questionType ==
                            QuestionType.shortQuestion
                        ? shortQuestion(list.elementAt(index), index)
                        : const SizedBox(),
      );

  Widget mainUI(BuildContext context, DataQuiz quizModel) {
    final quiz = quizModel.quiz!;
    final questionList = quizModel.questions;
    if (isFirstTime) {
      isFirstTime = false;
      givenAnswerList.clear();
      for (final question in questionList) {
        givenAnswerList.add(
          GivenAnswer(
            question: question,
            isCorrect: false,
            selectedIndexes: [-1],
            shortQuestionAns: '',
          ),
        );
      }
      ref.read(quizProvider.notifier).setListAnswer(givenAnswerList);
    }
    return Column(
      children: [
        if (questionList.isNotEmpty)
          const SizedBox(
            height: Dimensions.paddingSizeDefault,
          ),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeExtraLarge,
          ),
          child: Row(
            children: [
              Flexible(
                child: LinearProgressIndicator(
                  value: (givenAnswerList
                          .where(
                            (e) =>
                                e.selectedIndexes
                                        .fold<int>(0, (a, b) => a + b) >=
                                    0 &&
                                e.selectedIndexes.isNotEmpty,
                          )
                          .length) /
                      questionList.length,
                  minHeight: Dimensions.paddingSizeSmall,
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  color: Theme.of(context).primaryColor,
                  backgroundColor:
                      Theme.of(context).primaryColor.withValues(alpha: 0.2),
                ),
              ),
              const SizedBox(
                width: Dimensions.paddingSizeDefault,
              ),
              Text(
                '${givenAnswerList.where((e) => e.selectedIndexes.fold<int>(0, (a, b) => a + b) >= 0 && e.selectedIndexes.isNotEmpty).length}/${questionList.length}',
                style: robotoBold,
              ),
            ],
          ),
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        Expanded(
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            shrinkWrap: true,
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: Dimensions.paddingSizeDefault,
                ),
                child: Column(
                  children: [
                    Timeline(
                      lineColor: Theme.of(context)
                          .colorScheme
                          .primary
                          .withValues(alpha: 0.06),
                      indicators: indicatorList(questionList),
                      children: questionListWidgets(questionList),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        CustomButton(
          onPressed: () async {
            final notifier = ref.read(quizProvider.notifier);
            final emptyAnswer = notifier.emptyAnswerChecker(givenAnswerList);
            if (emptyAnswer) {
              customDialog(
                title: 'Submit',
                middleTitle: 'You have not complete quiz yet\nSubmit anyway?',
                textConfirm: 'Submit',
                onConfirm: () async {
                  LoadingDialog.show(context);
                  await notifier.submitQuiz(
                    startTime: DateTime.now().millisecondsSinceEpoch,
                    endTime: DateTime.now().millisecondsSinceEpoch,
                    quizId: quiz.id ?? '',
                    takeQuizFrom: quiz.takeQuizFrom ?? '',
                    listAnswer: givenAnswerList,
                  );
                  if (!context.mounted) return;
                  LoadingDialog.hide(context);
                  context.replace(
                    AppRoutes.quizResultScreen,
                    extra: givenAnswerList,
                  );
                },
                context: context,
              );
            } else {
              customDialog(
                title: 'Submit',
                middleTitle: 'Are you sure you want to submit?',
                textConfirm: 'Submit',
                onConfirm: () async {
                  LoadingDialog.show(context);
                  await notifier.submitQuiz(
                    startTime: DateTime.now().millisecondsSinceEpoch,
                    endTime: DateTime.now().millisecondsSinceEpoch,
                    quizId: quiz.id ?? '',
                    takeQuizFrom: quiz.takeQuizFrom ?? '',
                    listAnswer: givenAnswerList,
                  );
                  if (!context.mounted) return;
                  LoadingDialog.hide(context);
                  context.replace(
                    AppRoutes.quizResultScreen,
                    extra: givenAnswerList,
                  );
                },
                context: context,
              );
            }
          },
          buttonText: tl(context).submit,
        ),
      ],
    );
  }
}
