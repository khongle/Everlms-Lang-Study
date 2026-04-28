import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/forum/data/models/forum_model.dart';
import 'package:everlms/feature/forum/data/models/forum_topic_model.dart';
import 'package:everlms/feature/forum/data/models/topic_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ForumRepository {
  ForumRepository(this._apiClient, this._prefs);
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  String? get _uid => _prefs.getString(AppConstants.uid);

  Future<List<ForumModel>> getForum() async {
    final listForum = <ForumModel>[];
    final res = await _apiClient.getData(AppConstants.getForum);
    final mapRes = res.body['data'];
    if (mapRes is Map) {
      (mapRes as Map<String, dynamic>).forEach((key, value) {
        listForum.add(ForumModel.fromJson(value as Map<String, dynamic>));
      });
    }
    return listForum;
  }

  Future<List<ForumModel>> getForumOffline() async {
    final listForum = <ForumModel>[];
    final res = await _apiClient.getData(AppConstants.lmsGetForumsOffline);
    final data = res.body['forums'];
    if (data is Map) {
      final mapRes = res.body['forums'] as Map<String, dynamic>;
      mapRes.forEach((key, value) {
        listForum.add(ForumModel.fromJson(value as Map<String, dynamic>));
      });
    }
    return listForum;
  }

  Future<List<ForumTopicModel>> getListTopic(String forumId) async {
    final list = <ForumTopicModel>[];
    final body = {
      'forum_id': forumId,
      'user_id': _uid,
    };
    final res = await _apiClient.getData(
      AppConstants.getListTopic,
      body: body,
    );
    final data = res.body['data'];
    if (data is Map) {
      final mapRes = res.body['data'] as Map<String, dynamic>;
      mapRes.forEach((key, value) {
        list.add(ForumTopicModel.fromJson(value as Map<String, dynamic>));
      });
    }
    return list;
  }

  Future<TopicModel> getTopic(String topicId) async {
    final res = await _apiClient.getData(
      AppConstants.getTopic(topicId),
    );
    final mapRes = res.body['data'];
    return TopicModel.fromJson(mapRes as Map<String, dynamic>);
  }

  Future<void> createForum(
    String subject,
    String forumId,
    String bodyString,
  ) async {
    final body = {
      'subject': subject,
      'body': bodyString,
      'forum_id': forumId,
      'user_id': _uid,
    };
    await _apiClient.postData(AppConstants.createForumTopic, body);
  }

  Future<ApiResponse?> createForumTopic(
    String subject,
    String forumId,
    String bodyString,
  ) async {
    final body = {
      'subject': subject,
      'body': bodyString,
      'forum_id': forumId,
      'user_id': _uid,
    };
    return _apiClient.postData(AppConstants.createForumTopic, body);
  }

  Future<bool> createComment(String topicId, String bodyString) async {
    final body = {
      'comment_body': bodyString,
      'topic_id': topicId,
      'user_id': _uid,
    };
    final res = await _apiClient.postData(AppConstants.createComment, body);
    return res.statusCode == 200;
  }
}
