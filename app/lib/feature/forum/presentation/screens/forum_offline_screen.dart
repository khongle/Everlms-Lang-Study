import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ForumOfflineScreen extends ConsumerStatefulWidget {
  const ForumOfflineScreen({super.key});

  @override
  ConsumerState<ForumOfflineScreen> createState() => _ForumOfflineScreenState();
}

class _ForumOfflineScreenState extends ConsumerState<ForumOfflineScreen> {
  @override
  void initState() {
    super.initState();
    ref.read(forumProvider.notifier).getForumOffline();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).forum,
        isBackButtonExist: false,
      ),
      body: Builder(
        builder: (context) {
          final forumState = ref.watch(forumProvider).valueOrNull;
          if (forumState == null || forumState.isLoading) {
            return const LoadingIndicator();
          }
          return mainUI(forumState);
        },
      ),
    );
  }

  Widget mainUI(ForumState forumState) {
    return Column(
      children: [
        Expanded(
          child: ListView.builder(
            itemCount: forumState.listForum.length,
            itemBuilder: (context, index) {
              final item = forumState.listForum[index];
              final isParent = item.parents == '0';
              return GestureDetector(
                onTap: () {
                  if (!isParent) {
                    context.push(
                      AppRoutes.forumDetail,
                      extra: {
                        'forum_id': item.id,
                        'forum_name': item.name,
                      },
                    );
                  }
                },
                child: ColoredBox(
                  color: isParent
                      ? Theme.of(context)
                          .colorScheme
                          .onSurface
                          .withValues(alpha: 0.06)
                      : Theme.of(context).scaffoldBackgroundColor,
                  child: ListTile(
                    title: Row(
                      children: [
                        const SizedBox(width: 5),
                        Expanded(
                          child: Text(
                            item.name,
                            style: isParent
                                ? Theme.of(context).textTheme.headlineSmall
                                : Theme.of(context).textTheme.titleMedium,
                          ),
                        ),
                      ],
                    ),
                    subtitle: Column(
                      children: [
                        Html(
                          data: item.description,
                          style: {
                            '*': Style(
                              margin:
                                  Margins(top: Margin(5), bottom: Margin(2)),
                            ),
                          },
                        ),
                        if (!isParent)
                          Padding(
                            padding: const EdgeInsets.only(left: 15),
                            child: Row(
                              children: [
                                SvgPicture.asset(
                                  Images.message,
                                  height: 20,
                                ),
                                const SizedBox(width: 10),
                                Text('${item.numberTopics} Topics'),
                                const SizedBox(width: 20),
                                SvgPicture.asset(
                                  Images.noteFile,
                                  height: 20,
                                ),
                                const SizedBox(width: 10),
                                Text('${item.numPosts} Posts'),
                              ],
                            ),
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
