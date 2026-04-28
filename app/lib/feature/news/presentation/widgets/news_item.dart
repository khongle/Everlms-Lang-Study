import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/label_widget.dart';
import 'package:everlms/feature/news/data/models/news.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class NewsItem extends StatelessWidget {
  const NewsItem({required this.news, super.key});
  final News news;

  @override
  Widget build(BuildContext context) {
    final createdDateTime = epochToDateTime(news.created ?? '');
    final created = DateFormat('dd MMMM, yyyy').format(createdDateTime);
    return InkWell(
      onTap: () => context.push(AppRoutes.newsDetail, extra: news),
      child: mainUI(context, created),
    );
  }

  Container mainUI(BuildContext context, String created) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        border: Border.all(
          color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
        ),
      ),
      margin: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
      padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          thumbnail(),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Flexible(child: titileNews(context)),
          Flexible(child: createdNews(created, context)),
          summaryNews(context),
        ],
      ),
    );
  }

  Text summaryNews(BuildContext context) {
    return Text(
      news.summary ?? '',
      maxLines: 3,
      overflow: TextOverflow.ellipsis,
      style: Theme.of(context).textTheme.bodyMedium,
    );
  }

  Text createdNews(String created, BuildContext context) {
    return Text(
      created,
      style: Theme.of(context).textTheme.bodyMedium,
    );
  }

  Text titileNews(BuildContext context) {
    return Text(
      news.title ?? '',
      style: Theme.of(context).textTheme.headlineSmall,
    );
  }

  Stack thumbnail() {
    return Stack(
      alignment: Alignment.topLeft,
      children: [
        Builder(
          builder: (context) {
            return CustomImage(
              image: news.thumbnail ?? '',
              height: 180,
              width: MediaQuery.of(context).size.width,
            );
          },
        ),
        LabelWidget(label: news.tag),
      ],
    );
  }
}
