import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/core/widgets/ripple_button.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class SearchSuggestion extends ConsumerWidget {
  const SearchSuggestion({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final searchState = ref.watch(searchProvider).valueOrNull;
    final historyList = searchState?.historyList ?? [];

    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                tl(context).suggestions,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeLarge,
                  color: Theme.of(context).primaryColor,
                ),
              ),
              InkWell(
                onTap: () =>
                    ref.read(searchProvider.notifier).clearSearchHistory(),
                child: Padding(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: Text(
                    tl(context).clear_all,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          GridView.builder(
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: ResponsiveHelper.isTab(context) ? 6 : 4,
              childAspectRatio: 3 / 1.6,
              crossAxisSpacing: Dimensions.paddingSizeDefault,
              mainAxisSpacing: Dimensions.paddingSizeDefault,
            ),
            itemCount: historyList.length,
            itemBuilder: (context, index) {
              return Stack(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      color: Theme.of(context).hoverColor,
                      borderRadius: const BorderRadius.all(
                        Radius.circular(Dimensions.radiusDefault),
                      ),
                    ),
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeExtraSmall,
                          vertical: Dimensions.paddingSizeExtraSmall,
                        ),
                        child: Text(historyList.elementAt(index)),
                      ),
                    ),
                  ),
                  Positioned.fill(
                    child: RippleButton(
                      onTap: () {
                        ref.read(searchProvider.notifier).searchData(
                              historyList.elementAt(index),
                            );
                      },
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}
