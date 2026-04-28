import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:everlms/feature/forum/presentation/screens/create_forum_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ForumDetailScreen extends ConsumerStatefulWidget {
  const ForumDetailScreen({this.forumIdArg, this.forumNameArg, super.key});
  final String? forumIdArg;
  final String? forumNameArg;

  @override
  ConsumerState<ForumDetailScreen> createState() => _ForumScreenState();
}

class _ForumScreenState extends ConsumerState<ForumDetailScreen> {
  String forumName = '';
  String? forumId;
  @override
  void initState() {
    super.initState();
    forumId = widget.forumIdArg;
    forumName = widget.forumNameArg ?? '';
    ref.read(forumProvider.notifier).getListTopic(forumId ?? '');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: forumName,
      ),
      body: Builder(
        builder: (context) {
          final forumState = ref.watch(forumProvider).valueOrNull;
          if (forumState == null || forumState.isLoadingForumDetail) {
            return const LoadingIndicator();
          }
          return mainUI(forumState);
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          showModalBottomSheet(
            isScrollControlled: true,
            useSafeArea: true,
            sheetAnimationStyle: const AnimationStyle(
              curve: Curves.linear,
              reverseCurve: Curves.linear,
              reverseDuration: Duration(milliseconds: 300),
              duration: Duration(milliseconds: 500),
            ),
            context: context,
            builder: (context) {
              return ClipRRect(
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(Dimensions.radiusLarge),
                  topRight: Radius.circular(Dimensions.radiusLarge),
                ),
                child: CreateForumScreen(
                  forumId: forumId,
                ),
              );
            },
          );
          // context.push(AppRoutes.createForumScreen, extra: {
          //   'forum_id': Get.arguments['forum_id'],
          // });
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget mainUI(ForumState forumState) {
    return Column(
      children: [
        Expanded(
          child: ListView.builder(
            itemCount: forumState.listTopic.length,
            itemBuilder: (context, index) {
              final item = forumState.listTopic[index];
              // Check time to now
              final time = epochToDateTime(item.created);
              final now = DateTime.now();
              final diff = diffInMonths(time, now);

              return GestureDetector(
                onTap: () async {
                  context.push(
                    AppRoutes.topic,
                    extra: {
                      'topic_id': item.id,
                      'topic_name': item.title,
                      'forum_id': forumId,
                      'forum_name': forumName,
                    },
                  );
                },
                child: Container(
                  margin: const EdgeInsets.only(
                    bottom: Dimensions.paddingSizeDefault,
                  ),
                  child: ListTile(
                    title: Padding(
                      padding: const EdgeInsets.only(
                        bottom: Dimensions.paddingSizeSmall,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.title,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          Text(
                            '$diff month${diff > 1 ? 's' : ''} ago',
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    subtitle: Row(
                      children: [
                        SvgPicture.asset(
                          Images.message,
                          height: 20,
                        ),
                        const SizedBox(width: Dimensions.paddingSizeSmall),
                        Text(
                          "${item.commentCount} Comment${int.parse(item.commentCount) > 1 ? 's' : ''}",
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
