import 'dart:convert';

import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/feature/quiz/data/models/data.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:everlms/feature/quiz/data/models/quiz_model.dart';
import 'package:everlms/feature/quiz/data/models/quiz_result.dart';
import 'package:everlms/feature/quiz/data/repositories/quiz_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final quizRepositoryProvider = Provider<QuizRepository>((ref) {
  return QuizRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class QuizState {
  const QuizState({
    this.quizModelCourse,
    this.quizModelChapter = const {},
    this.quizModelLesson,
    this.listQuizModelLesson = const [],
    this.dataQuiz,
    this.quizResult,
    this.startTime,
    this.duration,
    this.isLoading = false,
    this.isQuizLoading = false,
    this.isResultLoading = false,
  });

  final QuizModel? quizModelCourse;
  final Map<String, QuizModel> quizModelChapter;
  final QuizModel? quizModelLesson;
  final List<QuizModel?> listQuizModelLesson;
  final DataQuiz? dataQuiz;
  final QuizResult? quizResult;
  final int? startTime;
  final int? duration;
  final bool isLoading;
  final bool isQuizLoading;
  final bool isResultLoading;

  QuizState copyWith({
    QuizModel? quizModelCourse,
    Map<String, QuizModel>? quizModelChapter,
    QuizModel? quizModelLesson,
    List<QuizModel?>? listQuizModelLesson,
    DataQuiz? dataQuiz,
    QuizResult? quizResult,
    int? startTime,
    int? duration,
    bool? isLoading,
    bool? isQuizLoading,
    bool? isResultLoading,
  }) {
    return QuizState(
      quizModelCourse: quizModelCourse ?? this.quizModelCourse,
      quizModelChapter: quizModelChapter ?? this.quizModelChapter,
      quizModelLesson: quizModelLesson ?? this.quizModelLesson,
      listQuizModelLesson: listQuizModelLesson ?? this.listQuizModelLesson,
      dataQuiz: dataQuiz ?? this.dataQuiz,
      quizResult: quizResult ?? this.quizResult,
      startTime: startTime ?? this.startTime,
      duration: duration ?? this.duration,
      isLoading: isLoading ?? this.isLoading,
      isQuizLoading: isQuizLoading ?? this.isQuizLoading,
      isResultLoading: isResultLoading ?? this.isResultLoading,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class QuizNotifier extends AsyncNotifier<QuizState> {
  @override
  Future<QuizState> build() async {
    return const QuizState();
  }

  QuizRepository get _repo => ref.read(quizRepositoryProvider);
  SharedPreferences get _prefs => ref.read(sharedPreferencesProvider);

  Future<void> getQuizDataByCourseId(String courseId) async {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(isLoading: true));
    final response = await _repo.getQuizDataByCourseId(courseId);
    QuizModel? model;
    if (response != null && response.statusCode == 200) {
      model = QuizModel.fromJson(response.body as Map<String, dynamic>);
    }
    final latest = state.valueOrNull ?? const QuizState();
    state = AsyncData(
      latest.copyWith(
        quizModelCourse: model,
        isLoading: false,
      ),
    );
  }

  Future<void> getQuizDataByChapterId(String chapterId) async {
    final current = state.valueOrNull ?? const QuizState();
    final response = await _repo.getQuizDataByChapterId(chapterId);
    if (response != null && response.statusCode == 200) {
      final model = QuizModel.fromJson(response.body as Map<String, dynamic>);
      final latest = state.valueOrNull ?? current;
      final updated = Map<String, QuizModel>.from(latest.quizModelChapter);
      updated[chapterId] = model;
      state = AsyncData(latest.copyWith(quizModelChapter: updated));
    }
  }

  Future<void> getQuizDataByLessonId(String lessonId) async {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(isQuizLoading: true));
    final response = await _repo.getQuizDataByLessonId(lessonId);
    QuizModel? model;
    if (response != null && response.statusCode == 200) {
      model = QuizModel.fromJson(response.body as Map<String, dynamic>);
    }
    final latest = state.valueOrNull ?? const QuizState();
    state = AsyncData(
      latest.copyWith(
        quizModelLesson: model,
        isQuizLoading: false,
      ),
    );
  }

  Future<void> setListAnswer(List<GivenAnswer> answers) async {
    final encoded = jsonEncode(answers.map((e) => e.toJson()).toList());
    await _prefs.setString('listAnswer', encoded);
  }

  List<GivenAnswer>? getListAnswer() {
    final data = _prefs.getString('listAnswer');
    if (data == null) return null;
    final list = (jsonDecode(data) as List)
        .map((e) => GivenAnswer.fromJson(e as Map<String, dynamic>))
        .toList();
    return list;
  }

  Future<bool> startQuiz({
    required int timeMinutesLimit,
    required String quizId,
    required String takeQuizFrom,
  }) async {
    final current = state.valueOrNull ?? const QuizState();
    final startedAt = await _checkQuizStarted(quizId);
    if (startedAt == null) {
      return false;
    }

    final currentTime = DateTime.now().millisecondsSinceEpoch;
    final difference = currentTime - startedAt;
    if (difference > timeMinutesLimit * 60 * 1000) {
      // Time expired — auto-submit stored answers
      final listAnswerLocal = _prefs.getStringList('listAnswer');
      if (listAnswerLocal != null) {
        final listAnswer = listAnswerLocal
            .map(
              (e) =>
                  GivenAnswer.fromJson(jsonDecode(e) as Map<String, dynamic>),
            )
            .toList();
        await submitQuiz(
          startTime: startedAt,
          endTime: currentTime,
          quizId: quizId,
          takeQuizFrom: takeQuizFrom,
          listAnswer: listAnswer,
        );
      }
      customSnackBar(
        'Time to answer the quiz is over and your answer is submitted',
        isError: false,
      );
      await clearDataQuizLocal();
      return false;
    }

    _prefs.setString('quizId', quizId);
    _prefs.setString('quizStartTime', startedAt.toString());
    _prefs.setString('durationQuiz', (timeMinutesLimit * 60).toString());
    _prefs.setString('takeQuizFrom', takeQuizFrom);

    state = AsyncData(
      current.copyWith(
        startTime: startedAt,
        duration: timeMinutesLimit * 60,
      ),
    );
    return true;
  }

  Future<int?> _checkQuizStarted(String quizId) async {
    final storedQuizId = _prefs.getString('quizId');
    if (storedQuizId == null) {
      return DateTime.now().millisecondsSinceEpoch;
    } else if (storedQuizId != quizId) {
      return null;
    } else {
      final startTimeString = _prefs.getString('quizStartTime');
      if (startTimeString != null) {
        return int.parse(startTimeString);
      }
      return null;
    }
  }

  void setDuration(int minutes) {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(duration: minutes * 60));
  }

  void setDataQuiz(DataQuiz? dataQuiz) {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(dataQuiz: dataQuiz));
  }

  void updateLoading(bool value) {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(isLoading: value));
  }

  void updateResultLoading(bool value) {
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(isResultLoading: value));
  }

  /// Returns true if submission succeeded, false if offline or error.
  Future<bool> submitQuiz({
    required int startTime,
    required int endTime,
    required String quizId,
    required String takeQuizFrom,
    required List<GivenAnswer> listAnswer,
  }) async {
    final current = state.valueOrNull ?? const QuizState();

    final isConnected = await NetworkService().checkInternetConnection();
    if (!isConnected) {
      _prefs.setBool('isSendQuiz', true);
      customSnackBar(
        'Your answer is saved and when online it will be sent',
        isError: false,
      );
      return false;
    }

    state = AsyncData(
      current.copyWith(isLoading: true, isResultLoading: true),
    );

    try {
      final uid = _prefs.getString(AppConstants.uid) ?? '';
      final response = await _repo.submitQuiz(
        startTime: startTime,
        endTime: endTime,
        quizId: quizId,
        user: uid,
        takeQuizFrom: takeQuizFrom,
        listAnswer: listAnswer,
      );

      QuizResult? result;
      if (response != null && response.statusCode == 200) {
        result = QuizResult.fromJson(
          response.body['data'] as Map<String, dynamic>,
        );
      }
      await clearDataQuizLocal();
      state = AsyncData(
        current.copyWith(
          quizResult: result,
          isLoading: false,
          isResultLoading: false,
        ),
      );
      return true;
    } on Exception {
      state = AsyncData(
        current.copyWith(isLoading: false, isResultLoading: false),
      );
      return false;
    }
  }

  Future<void> clearDataQuizLocal() async {
    await _prefs.remove('listAnswer');
  }

  bool emptyAnswerChecker(List<GivenAnswer> answers) {
    return answers.every(
      (a) => a.selectedIndexes.isEmpty && (a.shortQuestionAns?.isEmpty ?? true),
    );
  }

  Future<void> getListQuizDataByLessonId(Course? course) async {
    if (course == null) return;
    final current = state.valueOrNull ?? const QuizState();
    state = AsyncData(current.copyWith(isLoading: true));

    final quizList = <QuizModel?>[];
    for (final section in course.listChapters ?? []) {
      for (final lesson in section.lessons ?? []) {
        final response =
            await _repo.getQuizDataByLessonId(lesson.id.toString());
        if (response != null && response.statusCode == 200) {
          quizList.add(
            QuizModel.fromJson(response.body as Map<String, dynamic>),
          );
        }
      }
    }

    state = AsyncData(
      current.copyWith(
        listQuizModelLesson: quizList,
        isLoading: false,
      ),
    );
  }

  Future<void> getQuizDataByChapterIds(List<Chapter> chapters) async {
    final current = state.valueOrNull ?? const QuizState();
    final updated = Map<String, QuizModel>.from(current.quizModelChapter);
    for (final chapter in chapters) {
      final response =
          await _repo.getQuizDataByChapterId(chapter.id.toString());
      if (response != null && response.statusCode == 200) {
        final model = QuizModel.fromJson(response.body as Map<String, dynamic>);
        updated[chapter.id.toString()] = model;
      }
    }
    state = AsyncData(current.copyWith(quizModelChapter: updated));
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final quizProvider = AsyncNotifierProvider<QuizNotifier, QuizState>(
  QuizNotifier.new,
);
