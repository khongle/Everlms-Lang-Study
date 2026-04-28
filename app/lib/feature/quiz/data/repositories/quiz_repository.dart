import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:shared_preferences/shared_preferences.dart';

class QuizRepository {
  QuizRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  String? get _uid => _prefs.getString(AppConstants.uid);

  Future<ApiResponse?> getQuizDataByCourseId(String courseId) async {
    return _apiClient.getData(
      AppConstants.getListQuizByCourseId,
      body: {'course_id': courseId, 'user_id': _uid},
    );
  }

  Future<ApiResponse?> getQuizDataByChapterId(String chapterId) async {
    return _apiClient.getData(
      AppConstants.getListQuizByChapterId,
      body: {'chapter_id': chapterId, 'user_id': _uid},
    );
  }

  Future<ApiResponse?> getQuizDataByLessonId(String lessonId) async {
    return _apiClient.getData(
      AppConstants.getListQuizByLessonId,
      body: {'lesson_id': lessonId, 'user_id': _uid},
    );
  }

  Future<ApiResponse?> submitQuiz({
    required int startTime,
    required int endTime,
    required String quizId,
    required String user,
    required String takeQuizFrom,
    required List<GivenAnswer> listAnswer,
  }) async {
    final formValues = <String, dynamic>{};
    for (var i = 0; i < listAnswer.length; i++) {
      final answer = listAnswer[i];
      final key = 'answers_select_$i';
      final answerMap = <String, dynamic>{};
      final optionLength = answer.question.option?.length ?? 0;
      for (var index = 0; index < optionLength; index++) {
        answerMap[index.toString()] = 0;
      }
      for (var j = 0; j < answer.selectedIndexes.length; j++) {
        answerMap[answer.selectedIndexes[j].toString()] =
            answer.selectedIndexes[j].toString();
      }
      formValues[key] = answerMap;
    }
    final body = {
      'data_json': {
        'start_time': startTime,
        'end_time': endTime,
        'quiz_id': quizId,
        'user_id': user,
        'take_quiz_from': takeQuizFrom,
        'form_values': formValues,
      },
    };
    return _apiClient.postData(AppConstants.submitQuiz, body);
  }
}
