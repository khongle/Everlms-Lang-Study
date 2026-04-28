import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:everlms/feature/search_screen/presentation/widgets/search_result_screen.dart';
import 'package:everlms/feature/search_screen/presentation/widgets/search_segregation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final FocusNode _focus = FocusNode();
  final TextEditingController _textController = TextEditingController();
  Timer? _debounce;

  @override
  void initState() {
    super.initState();
    _focus.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _focus.dispose();
    _textController.dispose();
    super.dispose();
  }

  void _onSearchChanged(String value) {
    if (_debounce?.isActive ?? false) _debounce!.cancel();
    _debounce = Timer(
      const Duration(seconds: 1),
      () => ref.read(searchProvider.notifier).searchData(value),
    );
  }

  @override
  Widget build(BuildContext context) {
    final asyncState = ref.watch(searchProvider);

    return PopScope(
      onPopInvokedWithResult: (didPop, result) async {
        ref.read(searchProvider.notifier).clearResults();
      },
      child: SafeArea(
        child: Scaffold(
          body: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
            ),
            child: CustomScrollView(
              slivers: [
                SliverAppBar(
                  automaticallyImplyLeading: false,
                  surfaceTintColor: Colors.transparent,
                  backgroundColor: Theme.of(context).scaffoldBackgroundColor,
                  floating: true,
                  snap: true,
                  bottom: PreferredSize(
                    preferredSize: const Size.fromHeight(0),
                    child: _buildSearchField(context),
                  ),
                ),
                SliverPadding(
                  padding: const EdgeInsets.only(
                    top: Dimensions.paddingSizeSmall,
                  ),
                  sliver: SliverToBoxAdapter(
                    child: _buildBody(asyncState),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSearchField(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: TextField(
            controller: _textController,
            focusNode: _focus,
            onChanged: _onSearchChanged,
            decoration: InputDecoration(
              hintText: tl(context).search,
              prefixIcon: Padding(
                padding: const EdgeInsets.all(12),
                child: SvgPicture.asset(
                  Images.search,
                  width: 20,
                  height: 20,
                ),
              ),
              suffixIcon: _textController.text.isNotEmpty
                  ? IconButton(
                      onPressed: () {
                        _textController.clear();
                        ref.read(searchProvider.notifier).clearResults();
                      },
                      icon: const Icon(Icons.close),
                    )
                  : null,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildBody(AsyncValue<SearchState> asyncState) {
    return asyncState.when(
      loading: () => const SizedBox(
        height: 200,
        child: LoadingIndicator(),
      ),
      error: (error, _) => Center(child: Text('$error')),
      data: (state) {
        final showSearchResult =
            _focus.hasFocus || _textController.text.isNotEmpty;

        if (!showSearchResult) {
          return const SearchSegregation();
        }

        if (state.isSearching) {
          return const SizedBox(
            height: 200,
            child: LoadingIndicator(),
          );
        }

        if (_focus.hasFocus && _textController.text.isEmpty) {
          return _buildRecentSearches(state);
        }

        if (state.searchResults?.isEmpty ?? true) {
          return Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(height: MediaQuery.of(context).size.height / 5),
              Image.asset(Images.emptySearchResult, scale: 3),
              Text(
                tl(context).no_result_found,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ],
          );
        }

        return SearchResultScreen(
          queryText: _textController.text,
          searchFocusNode: _focus,
        );
      },
    );
  }

  Widget _buildRecentSearches(SearchState state) {
    if (state.historyList.isEmpty) return const SizedBox.shrink();
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Recent Searches',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            TextButton(
              onPressed: () =>
                  ref.read(searchProvider.notifier).clearSearchHistory(),
              child: Text(
                'Clear'.toUpperCase(),
                style: Theme.of(context).textTheme.labelMedium!.copyWith(
                      color: Theme.of(context)
                          .colorScheme
                          .onSurface
                          .withValues(alpha: 0.6),
                    ),
              ),
            ),
          ],
        ),
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: state.historyList.length,
          itemBuilder: (context, index) {
            final label = state.historyList[index];
            return ListTile(
              leading: SizedBox(
                width: 30,
                height: 25,
                child: SvgPicture.asset(
                  Images.search,
                  colorFilter: ColorFilter.mode(
                    Theme.of(context)
                        .colorScheme
                        .onSurface
                        .withValues(alpha: 0.6),
                    BlendMode.srcIn,
                  ),
                ),
              ),
              title: Text(label),
              onTap: () {
                _textController.text = label;
                ref.read(searchProvider.notifier).searchData(label);
              },
            );
          },
        ),
      ],
    );
  }
}
