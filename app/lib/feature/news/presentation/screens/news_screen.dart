import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/news/presentation/providers/news_provider.dart';
import 'package:everlms/feature/news/presentation/widgets/news_item.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class NewsScreen extends ConsumerWidget {
  const NewsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncState = ref.watch(newsProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).news,
      ),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (error, _) => Center(child: Text('$error')),
        data: (state) {
          if (state.newsList.isEmpty) {
            return Center(child: Text(tl(context).no_news_found));
          }
          return ListView.builder(
            shrinkWrap: true,
            itemCount: state.newsList.length,
            itemBuilder: (context, index) =>
                NewsItem(news: state.newsList.elementAt(index)),
          );
        },
      ),
    );
  }
}
