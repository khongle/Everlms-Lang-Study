import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ForumScreen extends ConsumerStatefulWidget {
  const ForumScreen({super.key});

  @override
  ConsumerState<ForumScreen> createState() => _ForumScreenState();
}

class _ForumScreenState extends ConsumerState<ForumScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(forumProvider.notifier).getForum(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final asyncState = ref.watch(forumProvider);
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).forum,
      ),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(child: Text('$e')),
        data: (state) {
          if (state.isLoading) return const LoadingIndicator();
          return RefreshIndicator(
            onRefresh: () => ref.read(forumProvider.notifier).handleRefresh(),
            child: _mainUI(state),
          );
        },
      ),
    );
  }

  Widget _mainUI(ForumState state) {
    if (state.listForum.isEmpty) {
      return Center(
        child: Text(tl(context).no_forums_found),
      );
    }
    return ListView.builder(
      itemCount: state.listForum.length,
      itemBuilder: (context, index) {
        final parent = state.listForum[index];
        final children = state.listForumNotParent[parent.id] ?? [];
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _forumParent(parent),
            ...children.map(_forumItem),
          ],
        );
      },
    );
  }

  Widget _forumParent(dynamic item) {
    return ColoredBox(
      color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.06),
      child: ListTile(
        title: Text(
          item.name,
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        subtitle: Html(
          data: item.description,
          style: {
            '*': Style(margin: Margins(top: Margin(5), bottom: Margin(2))),
          },
        ),
      ),
    );
  }

  Widget _forumItem(dynamic item) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
      child: ListTile(
        onTap: () {
          context.push(
            AppRoutes.forumDetail,
            extra: {
              'forum_id': item.id,
              'forum_name': item.name,
            },
          );
        },
        title: Text(
          item.name,
          style: Theme.of(context).textTheme.titleMedium,
        ),
        subtitle: Column(
          children: [
            Html(
              data: item.description,
              style: {
                '*': Style(margin: Margins(top: Margin(5), bottom: Margin(5))),
              },
            ),
            Row(
              children: [
                SvgPicture.asset(
                  Images.message,
                  height: 20,
                ),
                const SizedBox(width: 10),
                Text(
                  '${item.numberTopics} Topics',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(width: 20),
                SvgPicture.asset(
                  Images.noteFile,
                  height: 20,
                ),
                const SizedBox(width: 10),
                Text(
                  '${item.numPosts} Posts',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
