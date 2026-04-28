import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/news/data/models/news.dart';
import 'package:everlms/feature/news/presentation/providers/news_provider.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class NewsTag extends ConsumerStatefulWidget {
  const NewsTag({required this.tag, super.key});
  final String tag;

  @override
  ConsumerState<NewsTag> createState() => _NewsTagState();
}

class _NewsTagState extends ConsumerState<NewsTag> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(newsProvider.notifier).filterByTag(widget.tag);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).back,
        centerTitle: false,
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(left: Dimensions.paddingSizeSmall),
              child: Text(
                widget.tag,
                style: Theme.of(context).textTheme.headlineMedium,
              ),
            ),
            Builder(
              builder: (context) {
                final newsAsync = ref.watch(newsProvider);
                return newsAsync.when(
                  loading: () => const CupertinoActivityIndicator(
                    radius: Dimensions.radiusLarge,
                  ),
                  error: (e, _) => Text(e.toString()),
                  data: (state) {
                    return ListView.builder(
                      shrinkWrap: true,
                      itemCount: state.newsByTags.length,
                      physics: const NeverScrollableScrollPhysics(),
                      itemBuilder: (context, index) => Padding(
                        padding: const EdgeInsets.only(
                          bottom: Dimensions.paddingSizeDefault,
                        ),
                        child: newsCategoryItem(
                          state.newsByTags.elementAt(index),
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget newsCategoryItem(News news) {
    final createdDateTime = epochToDateTime(news.created ?? '');
    final created = DateFormat('dd MMMM, yyyy').format(createdDateTime);
    return InkWell(
      onTap: () => context.push(AppRoutes.newsDetail, extra: news),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
          border: Border.all(
            color:
                Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
          ),
        ),
        margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          spacing: Dimensions.paddingSizeSmall,
          children: [
            CustomImage(
              image: news.thumbnail ?? '',
            ),
            Flexible(
              child: Text(
                news.title ?? '',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
            ),
            Flexible(
              child: Text(
                news.tag ?? '',
                style: Theme.of(context)
                    .textTheme
                    .titleSmall!
                    .copyWith(color: Theme.of(context).primaryColor),
              ),
            ),
            Flexible(
              child: Text(
                created,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
            Text(
              news.summary ?? '',
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.bodyMedium,
            ),
          ],
        ),
      ),
    );
  }
}
