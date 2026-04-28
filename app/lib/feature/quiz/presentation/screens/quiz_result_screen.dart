import 'dart:io';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/core/utils/lottie_files.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:everlms/feature/quiz/data/models/quiz_result.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/quiz/presentation/widgets/custom_tab_bar_header.dart';
import 'package:everlms/feature/quiz/presentation/widgets/quiz_certificate_section.dart';
import 'package:everlms/feature/quiz/presentation/widgets/quiz_question_review.dart';
import 'package:everlms/feature/quiz/presentation/widgets/quiz_result_info_section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:lottie/lottie.dart';

class QuizResultScreen extends ConsumerStatefulWidget {
  const QuizResultScreen({this.givenAnswerListArg, super.key});
  final List<GivenAnswer>? givenAnswerListArg;

  @override
  ConsumerState<QuizResultScreen> createState() => _QuizResultScreenState();
}

class _QuizResultScreenState extends ConsumerState<QuizResultScreen>
    with TickerProviderStateMixin, AutomaticKeepAliveClientMixin {
  List<GivenAnswer> givenAnswerList = [];
  List<Questions> questionsList = [];
  List<bool> ansList = [];
  QuizResult? quizResult;

  TabController? _tabController;

  String? fullPathCertificate;
  String fileNameCertificate = '';
  File? fileCertificate;
  bool isLoadingFile = true;

  int _indexTab = 0;

  @override
  void initState() {
    super.initState();
    givenAnswerList.clear();
    questionsList.clear();
    ansList.clear();
    if (widget.givenAnswerListArg != null) {
      givenAnswerList = widget.givenAnswerListArg!;
    }

    for (final ans in givenAnswerList) {
      questionsList.add(ans.question);
    }
    quizResult = ref.read(quizProvider).valueOrNull?.quizResult;
    downloadCertificate();
    final listCorrect = quizResult?.fieldQuestionsResponse ?? [];
    for (var i = 0; i < listCorrect.length; i++) {
      if (listCorrect[i].answer.isNotEmpty) {
        final answer = listCorrect[i].answer.first;
        final index = givenAnswerList[i]
            .question
            .option
            ?.indexWhere((e) => e.id == answer);
        if (index != null && index != -1) {
          final indexRight = givenAnswerList[i].question.correctAnswerIndex;
          if (index == indexRight?.first) {
            ansList.add(true);
          } else {
            ansList.add(false);
          }
        }
      } else {
        ansList.add(false);
      }
    }
    _tabController = TabController(length: 2, vsync: this);
  }

  Future<void> downloadCertificate() async {
    final url = quizResult?.urlDownload ?? '';
    if (url.isEmpty) {
      isLoadingFile = false;
      setState(() {});
      return;
    }
    final baseUrl = ref.read(apiClientProvider).appBaseUrl ?? '';
    fullPathCertificate = '$baseUrl$url';
    fileNameCertificate = 'Certificate';
    fileCertificate =
        await downloadPDF(fullPathCertificate ?? '', fileNameCertificate);

    isLoadingFile = false;
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final quizState = ref.watch(quizProvider).valueOrNull;
    final isResultLoading = quizState?.isResultLoading ?? false;
    final dataQuiz = quizState?.dataQuiz;
    return Scaffold(
      appBar: PreferredSize(
        preferredSize: const Size(double.infinity, 60),
        child: isResultLoading || dataQuiz?.quiz == null
            ? AppBar()
            : CustomAppBar(
                title: dataQuiz!.quiz!.name,
                isBackButtonExist: false,
              ),
      ),
      body: isResultLoading ? const LoadingIndicator() : _mainUI(context),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.fromLTRB(
          Dimensions.paddingSizeLarge,
          0,
          Dimensions.paddingSizeLarge,
          Dimensions.paddingSizeDefault,
        ),
        child: Row(
          spacing: Dimensions.paddingSizeDefault,
          children: [
            if (fullPathCertificate != null)
              Flexible(
                child: CustomButton(
                  buttonText: tl(context).download,
                  icon: Icons.file_download_outlined,
                  margin: EdgeInsets.zero,
                  onPressed: () async {
                    LoadingDialog.show(context);
                    await downloadAndSaveFile(
                      fullPathCertificate ?? '',
                      fileNameCertificate,
                    );
                    if (!context.mounted) return;
                    LoadingDialog.hide(context);
                  },
                ),
              ),
            Flexible(
              child: CustomButton(
                icon: Icons.arrow_forward,
                margin: EdgeInsets.zero,
                isRightIcon: true,
                onPressed: () {
                  context.pop();
                  context.pop();
                },
                buttonText: tl(context).continue_course,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _mainUI(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        Column(
          children: [
            const QuizResultInfoSection(),
            Padding(
              padding: const EdgeInsets.symmetric(
                vertical: Dimensions.paddingSizeDefault,
              ),
              child: SizedBox(
                height: 35,
                child: CustomTabBarHeader(
                  controller: _tabController,
                  scrollable: false,
                  onTap: (value) => setState(() => _indexTab = value),
                  listTabs: const [
                    Tab(text: 'Review Question'),
                    Tab(text: 'Your Certificate'),
                  ],
                ),
              ),
            ),
            Expanded(
              child: IndexedStack(
                index: _indexTab,
                children: [
                  ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    shrinkWrap: true,
                    children: [
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeSmall,
                        ),
                        child: QuizQuestionReview(
                          questionsList: questionsList,
                          givenAnswerList: givenAnswerList,
                          ansList: ansList,
                        ),
                      ),
                    ],
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeSmall,
                    ),
                    child: QuizCertificateSection(
                      isLoadingFile: isLoadingFile,
                      urlDownload: quizResult?.urlDownload,
                      fileCertificate: fileCertificate,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        if (quizResult?.fieldResult == 'Passed' ||
            quizResult?.fieldResult == 'Đậu')
          Lottie.asset(LottieFiles.congratulation, repeat: false),
      ],
    );
  }

  @override
  bool get wantKeepAlive => true;
}
