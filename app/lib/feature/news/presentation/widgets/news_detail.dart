import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class NewsDetail extends StatelessWidget {
  const NewsDetail({this.newsArg, super.key});
  final dynamic newsArg;

  @override
  Widget build(BuildContext context) {
    final news = newsArg;

    return Scaffold(
      appBar: const CustomAppBar(
        title: 'Back',
        centerTitle: false,
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.symmetric(
            vertical: Dimensions.paddingSizeDefault,
            horizontal: Dimensions.paddingSizeSmall,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            spacing: Dimensions.paddingSizeSmall,
            children: [
              Flexible(
                child: Text(
                  news.title ?? '',
                  style: Theme.of(context).textTheme.titleLarge,
                ),
              ),
              CustomImage(
                image: news.thumbnail ?? '',
              ),
              newsContent(context, news.content ?? ''),
              Padding(
                padding: const EdgeInsets.only(
                  left: Dimensions.paddingSizeDefault,
                  bottom: Dimensions.paddingSizeDefault,
                ),
                child: RichText(
                  text: TextSpan(
                    text: 'Tags:\t',
                    style: Theme.of(context).textTheme.titleMedium,
                    children: [
                      TextSpan(
                        text: news.tag,
                        recognizer: TapGestureRecognizer()
                          ..onTap = () => context.push(
                                AppRoutes.newsTagScreen,
                                extra: news.tag ?? '',
                              ),
                        style: Theme.of(context)
                            .textTheme
                            .titleMedium!
                            .copyWith(color: Theme.of(context).primaryColor),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget newsContent(BuildContext context, String content) {
    final listSection = content.split('\n\n');
    return Column(
      children: listSection.map((section) {
        final sectionSplit = section.split('\n');
        final header = sectionSplit.first;
        final body = sectionSplit.last;
        return RichText(
          text: TextSpan(
            text: '$header\n',
            style: Theme.of(context).textTheme.titleLarge,
            children: [
              TextSpan(
                text: '$body\n',
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}
