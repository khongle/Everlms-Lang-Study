import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:everlms/feature/search_screen/presentation/widgets/course_shimmer.dart';
import 'package:everlms/feature/search_screen/presentation/widgets/item_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';

class SearchResultScreen extends ConsumerWidget {
  const SearchResultScreen({
    required this.queryText,
    super.key,
    this.searchFocusNode,
  });
  final String? queryText;
  final FocusNode? searchFocusNode;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final searchState = ref.watch(searchProvider).valueOrNull;
    final isSearching = searchState?.isSearching ?? false;
    final results = searchState?.searchResults;

    if (isSearching) {
      return Padding(
        padding: const EdgeInsets.symmetric(
          vertical: Dimensions.paddingSizeDefault,
        ),
        child: GridView.builder(
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisSpacing: Dimensions.paddingSizeDefault,
            mainAxisSpacing: Dimensions.paddingSizeDefault,
            crossAxisCount: ResponsiveHelper.isMobile(context)
                ? 2
                : ResponsiveHelper.isTab(context)
                    ? 3
                    : 5,
          ),
          physics: const NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          itemCount: 15,
          itemBuilder: (context, index) {
            return const CourseShimmer(
              isEnabled: true,
              hasDivider: true,
            );
          },
        ),
      );
    }

    if (results?.isEmpty ?? true) {
      return Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        child: Row(
          children: [
            SvgPicture.asset(
              Images.search,
              height: Dimensions.iconSizeLarge,
              colorFilter: ColorFilter.mode(
                Theme.of(context).hintColor,
                BlendMode.srcIn,
              ),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            Flexible(
              child: InkWell(
                onTap: () {
                  searchFocusNode?.unfocus();
                  ref.read(searchProvider.notifier).searchData(
                        queryText ?? '',
                        updateHistory: true,
                      );
                },
                child: Text(queryText ?? ''),
              ),
            ),
          ],
        ),
      );
    }

    return const Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(height: Dimensions.paddingSizeDefault),
        ItemView(),
      ],
    );
  }
}
