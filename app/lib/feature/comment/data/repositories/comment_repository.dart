import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/comment/data/models/comment_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CommentRepository {
  CommentRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  String? get _uid => _prefs.getString(AppConstants.uid);

  Future<ApiResponse?> createComment({
    required String commentBody,
    required String entityId,
    required String entityType,
    required String commentType,
  }) async {
    return _apiClient.postData(AppConstants.lmsCreateComment, {
      'comment_body': commentBody,
      'entity_id': entityId,
      'entity_type': entityType,
      'comment_type': commentType,
      'user_id': _uid,
    });
  }

  Future<List<CommentModel>> getCommentLesson(String lessonId) async {
    final res = await _apiClient.getData(
      AppConstants.lmsGetComments,
      body: {
        'entity_id': lessonId,
        'entity_type': 'lesson',
        'comment_type': 'lesson',
        'user_id': _uid,
      },
    );
    return _parseComments(res);
  }

  Future<List<CommentModel>> getComment({
    required String entityId,
    required String entityType,
    required String commentType,
  }) async {
    final res = await _apiClient.getData(
      AppConstants.lmsGetComments,
      body: {
        'entity_id': entityId,
        'entity_type': entityType,
        'comment_type': commentType,
        'user_id': _uid,
      },
    );
    return _parseComments(res);
  }

  List<CommentModel> _parseComments(ApiResponse res) {
    final list = <CommentModel>[];
    if (res.statusCode == 200) {
      final data = res.body['data'];
      if (data is Map<String, dynamic>) {
        data.forEach((key, value) {
          list.add(CommentModel.fromJson(value as Map<String, dynamic>));
        });
      }
    }
    return list;
  }
}
