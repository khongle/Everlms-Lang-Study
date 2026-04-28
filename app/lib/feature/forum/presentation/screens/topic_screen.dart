import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/create_comment_widget.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/list_comment_widget.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/comment/presentation/providers/comment_provider.dart';
import 'package:everlms/feature/forum/data/models/topic_model.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class TopicScreen extends ConsumerStatefulWidget {
  const TopicScreen({
    this.topicIdArg,
    this.topicNameArg,
    this.forumIdArg,
    this.forumNameArg,
    super.key,
  });
  final String? topicIdArg;
  final String? topicNameArg;
  final String? forumIdArg;
  final String? forumNameArg;

  @override
  ConsumerState<TopicScreen> createState() => _ForumScreenState();
}

class _ForumScreenState extends ConsumerState<TopicScreen> {
  String topicId = '';
  String topicName = '';
  String forumId = '';
  String forumName = '';
  TopicModel? topic;
  @override
  void initState() {
    super.initState();
    topicId = widget.topicIdArg ?? '';
    topicName = widget.topicNameArg ?? '';
    forumId = widget.forumIdArg ?? '';
    forumName = widget.forumNameArg ?? '';
    ref.read(forumProvider.notifier).getTopic(topicId);
    ref.read(commentProvider.notifier).getComment(
          entityId: topicId,
          entityType: Comment.create(CommentType.forum).entityType ?? '',
          commentType: Comment.create(CommentType.forum).commentType ?? '',
        );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: false,
      appBar: CustomAppBar(
        title: forumName,
        onBackPressed: () {
          ref.read(forumProvider.notifier).getListTopic(forumId);
          context.pop();
        },
      ),
      body: Builder(
        builder: (context) {
          final forumState = ref.watch(forumProvider).valueOrNull;
          if (forumState == null || forumState.isLoadingTopicDetail) {
            return const LoadingIndicator();
          }
          topic = forumState.topic;
          return mainUI();
        },
      ),
      bottomSheet: SingleChildScrollView(
        child: CreateCommentWidget(
          entityId: topicId,
          type: CommentType.forum,
        ),
      ),
    );
  }

  Widget mainUI() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            DateFormat('d MMMM y')
                .format(epochToDateTime(topic?.created ?? '')),
            style: Theme.of(context).textTheme.bodyMedium,
          ),
          Text(
            topic?.title ?? '',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          Html(
            data: topic?.body ?? '',
            style: {
              '*': Style(
                fontFamily: 'Roboto',
                fontSize: FontSize(Dimensions.fontSizeLarge),
              ),
            },
          ),
          ListTile(
            leading: SvgPicture.asset(Images.chat),
            title: Text(
              tl(context).comments,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            contentPadding: EdgeInsets.zero,
            horizontalTitleGap: 0,
          ),
          Builder(
            builder: (context) {
              final commentState = ref.watch(commentProvider).valueOrNull;
              return ListCommentWidget(
                list: commentState?.comments ?? [],
                isScroll: false,
              );
            },
          ),
        ],
      ),
    );
  }
}
