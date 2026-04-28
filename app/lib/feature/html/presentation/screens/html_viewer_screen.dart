import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/app_theme.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/enum.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/html/presentation/providers/pages_content_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class HtmlViewerScreen extends ConsumerWidget {
  const HtmlViewerScreen({required this.htmlType, super.key});
  final HtmlType? htmlType;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = context.isDarkMode;
    final pagesAsync = ref.watch(pagesContentProvider);
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: htmlType == HtmlType.termsOfUse
            ? tl(context).terms_of_use
            : htmlType == HtmlType.aboutUs
                ? tl(context).about_us
                : htmlType == HtmlType.privacyPolicy
                    ? tl(context).privacy_policy
                    : htmlType == HtmlType.helpAndSupport
                        ? tl(context).help_and_support
                        : tl(context).no_data_found,
      ),
      body: pagesAsync.when(
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(child: Text(e.toString())),
        data: (pages) {
          final data = htmlType == HtmlType.termsOfUse
              ? pages?.termsOfUse?.body
              : htmlType == HtmlType.aboutUs
                  ? pages?.aboutUs?.body
                  : htmlType == HtmlType.privacyPolicy
                      ? pages?.privacyPolicy?.body
                      : htmlType == HtmlType.helpAndSupport
                          ? pages?.helpAndSupport?.body
                          : null;

          if (data == null) return const SizedBox();

          return SizedBox(
            width: MediaQuery.of(context).size.width,
            height: MediaQuery.of(context).size.height,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
              physics: const BouncingScrollPhysics(),
              child: Html(
                data: data,
                style: Map.fromEntries(
                  ['p', 'ul', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr'].map(
                    (tag) => MapEntry(
                      tag,
                      tag == 'hr'
                          ? Style(
                              margin: Margins(
                                top: Margin(2),
                                bottom: Margin(2),
                              ),
                              height: Height(1),
                            )
                          : tag == 'p'
                              ? Style(
                                  backgroundColor: Colors.transparent,
                                  color: isDark ? Colors.white70 : Colors.black,
                                )
                              : Style(
                                  backgroundColor: Colors.transparent,
                                  color: isDark ? Colors.white : Colors.black,
                                ),
                    ),
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
