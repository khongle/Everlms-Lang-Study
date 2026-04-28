import 'dart:convert';

import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/feature/forum/data/models/forum_model.dart';
import 'package:everlms/feature/forum/data/models/forum_topic_model.dart';
import 'package:everlms/feature/forum/data/models/topic_model.dart';
import 'package:everlms/feature/forum/data/repositories/forum_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final forumRepositoryProvider = Provider<ForumRepository>((ref) {
  return ForumRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class ForumState {
  const ForumState({
    this.listForum = const [],
    this.listForumNotParent = const {},
    this.isExpandedParent = const {},
    this.listTopic = const [],
    this.topic,
    this.forumId,
    this.isLoading = false,
    this.isLoadingForumDetail = false,
    this.isLoadingTopicDetail = false,
  });

  final List<ForumModel> listForum;
  final Map<String, List<ForumModel>> listForumNotParent;
  final Map<String, bool> isExpandedParent;
  final List<ForumTopicModel> listTopic;
  final TopicModel? topic;
  final String? forumId;
  final bool isLoading;
  final bool isLoadingForumDetail;
  final bool isLoadingTopicDetail;

  ForumState copyWith({
    List<ForumModel>? listForum,
    Map<String, List<ForumModel>>? listForumNotParent,
    Map<String, bool>? isExpandedParent,
    List<ForumTopicModel>? listTopic,
    TopicModel? topic,
    String? forumId,
    bool? isLoading,
    bool? isLoadingForumDetail,
    bool? isLoadingTopicDetail,
  }) {
    return ForumState(
      listForum: listForum ?? this.listForum,
      listForumNotParent: listForumNotParent ?? this.listForumNotParent,
      isExpandedParent: isExpandedParent ?? this.isExpandedParent,
      listTopic: listTopic ?? this.listTopic,
      topic: topic ?? this.topic,
      forumId: forumId ?? this.forumId,
      isLoading: isLoading ?? this.isLoading,
      isLoadingForumDetail: isLoadingForumDetail ?? this.isLoadingForumDetail,
      isLoadingTopicDetail: isLoadingTopicDetail ?? this.isLoadingTopicDetail,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class ForumNotifier extends AsyncNotifier<ForumState> {
  @override
  Future<ForumState> build() async {
    return const ForumState();
  }

  ForumRepository get _repo => ref.read(forumRepositoryProvider);
  SharedPreferences get _prefs => ref.read(sharedPreferencesProvider);

  Future<void> handleRefresh() async {
    await getForum();
    await _saveForumOffline();
  }

  Future<void> getForum() async {
    final current = state.valueOrNull ?? const ForumState();
    state = AsyncData(current.copyWith(isLoading: true));

    try {
      final forums = await _repo.getForum();
      final parentForums = <ForumModel>[];
      final childMap = <String, List<ForumModel>>{};
      final expandMap = <String, bool>{};

      for (final forum in forums) {
        if (forum.parents == '0') {
          parentForums.add(forum);
          expandMap[forum.id] = false;
        } else {
          childMap.putIfAbsent(forum.parents, () => []).add(forum);
        }
      }

      state = AsyncData(
        current.copyWith(
          listForum: parentForums,
          listForumNotParent: childMap,
          isExpandedParent: expandMap,
          isLoading: false,
        ),
      );
    } on Exception catch (_) {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }

  Future<void> _saveForumOffline() async {
    final current = state.valueOrNull ?? const ForumState();
    final encoded = jsonEncode(
      current.listForum.map((e) => e.toJson()).toList(),
    );
    await _prefs.setString('forumOffline', encoded);
  }

  Future<void> getForumOffline() async {
    final current = state.valueOrNull ?? const ForumState();
    state = AsyncData(current.copyWith(isLoading: true));

    final data = _prefs.getString('forumOffline');
    if (data != null) {
      final list = (jsonDecode(data) as List)
          .map((e) => ForumModel.fromJson(e as Map<String, dynamic>))
          .toList();
      state = AsyncData(
        current.copyWith(
          listForum: list,
          isLoading: false,
        ),
      );
    } else {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }

  Future<void> getListTopic(String forumId) async {
    final current = state.valueOrNull ?? const ForumState();
    state = AsyncData(
      current.copyWith(
        isLoadingForumDetail: true,
        forumId: forumId,
      ),
    );

    try {
      final isOnline = await NetworkService().checkInternetConnection();
      List<ForumTopicModel> topics;
      if (isOnline) {
        topics = await _repo.getListTopic(forumId);
      } else {
        topics = [];
      }
      state = AsyncData(
        current.copyWith(
          listTopic: topics,
          forumId: forumId,
          isLoadingForumDetail: false,
        ),
      );
    } on Exception catch (_) {
      state = AsyncData(current.copyWith(isLoadingForumDetail: false));
    }
  }

  Future<void> getTopic(String topicId) async {
    final current = state.valueOrNull ?? const ForumState();
    state = AsyncData(current.copyWith(isLoadingTopicDetail: true));

    try {
      final isOnline = await NetworkService().checkInternetConnection();
      if (isOnline) {
        final topic = await _repo.getTopic(topicId);
        state = AsyncData(
          current.copyWith(
            topic: topic,
            isLoadingTopicDetail: false,
          ),
        );
      } else {
        state = AsyncData(current.copyWith(isLoadingTopicDetail: false));
      }
    } on Exception catch (_) {
      state = AsyncData(current.copyWith(isLoadingTopicDetail: false));
    }
  }

  Future<bool> createForumTopic({
    required String subject,
    required String forumId,
    required String body,
  }) async {
    final isOnline = await NetworkService().checkInternetConnection();
    if (isOnline) {
      final response = await _repo.createForumTopic(subject, forumId, body);
      if (response != null && response.statusCode == 200) {
        await handleRefresh();
        return true;
      }
      return false;
    } else {
      // Queue offline
      final offlineTopics = _prefs.getStringList('offlineTopics') ?? [];
      offlineTopics.add(
        jsonEncode({
          'subject': subject,
          'forum_id': forumId,
          'body': body,
        }),
      );
      await _prefs.setStringList('offlineTopics', offlineTopics);
      return true;
    }
  }

  Future<bool> createComment({
    required String topicId,
    required String body,
  }) async {
    final isOnline = await NetworkService().checkInternetConnection();
    if (isOnline) {
      final success = await _repo.createComment(topicId, body);
      if (success) {
        await getTopic(topicId);
        if (state.valueOrNull?.forumId != null) {
          await getListTopic(state.valueOrNull!.forumId!);
        }
      }
      return success;
    } else {
      final offlineComments = _prefs.getStringList('offlineComments') ?? [];
      offlineComments.add(
        jsonEncode({
          'topic_id': topicId,
          'body': body,
        }),
      );
      await _prefs.setStringList('offlineComments', offlineComments);
      return true;
    }
  }

  void toggleExpanded(String forumId) {
    final current = state.valueOrNull ?? const ForumState();
    final updated = Map<String, bool>.from(current.isExpandedParent);
    updated[forumId] = !(updated[forumId] ?? false);
    state = AsyncData(current.copyWith(isExpandedParent: updated));
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final forumProvider = AsyncNotifierProvider<ForumNotifier, ForumState>(
  ForumNotifier.new,
);
