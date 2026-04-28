import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/app_theme.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class SearchWidget extends ConsumerStatefulWidget {
  const SearchWidget({super.key});

  @override
  ConsumerState<SearchWidget> createState() => _SearchWidgetState();
}

class _SearchWidgetState extends ConsumerState<SearchWidget> {
  final TextEditingController _textController = TextEditingController();
  bool _isActiveSuffix = false;

  @override
  void dispose() {
    _textController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = context.isDarkMode;
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(
          top: 5,
          left: Dimensions.paddingSizeDefault,
          right: Dimensions.paddingSizeDefault,
        ),
        child: Container(
          height: Dimensions.searchBerSize,
          width: 350,
          decoration: BoxDecoration(
            borderRadius: const BorderRadius.horizontal(
              right: Radius.circular(25),
              left: Radius.circular(25),
            ),
            boxShadow: searchBoxShadow(context),
          ),
          child: TextField(
            controller: _textController,
            style: Theme.of(context).textTheme.displayMedium!.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: Dimensions.fontSizeLarge,
                ),
            cursorColor: Theme.of(context).hintColor,
            textAlignVertical: TextAlignVertical.center,
            textInputAction: TextInputAction.search,
            decoration: InputDecoration(
              contentPadding: const EdgeInsets.symmetric(
                vertical: 10,
                horizontal: 22,
              ),
              border: const OutlineInputBorder(
                borderRadius: BorderRadius.horizontal(
                  right: Radius.circular(25),
                  left: Radius.circular(25),
                ),
                borderSide: BorderSide.none,
              ),
              fillColor: isDark
                  ? Theme.of(context).primaryColorDark
                  : Theme.of(context).colorScheme.surface,
              isDense: true,
              hintText: tl(context).search_course,
              hintStyle: Theme.of(context).textTheme.displayMedium!.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context).hintColor,
                  ),
              filled: true,
              prefixIconConstraints:
                  const BoxConstraints(minWidth: 23, maxHeight: 20),
              suffixIcon: _isActiveSuffix
                  ? IconButton(
                      color: Theme.of(context).primaryColor,
                      onPressed: () {
                        if (_textController.text.trim().isNotEmpty) {
                          _textController.clear();
                          ref.read(searchProvider.notifier).clearResults();
                          setState(() => _isActiveSuffix = false);
                        }
                        FocusScope.of(context).unfocus();
                      },
                      icon: const Icon(Icons.cancel_outlined),
                    )
                  : const SizedBox(),
            ),
            onChanged: (text) {
              setState(() => _isActiveSuffix = text.isNotEmpty);
            },
            onSubmitted: (text) {
              if (text.isNotEmpty) {
                ref.read(searchProvider.notifier).searchData(text);
              }
            },
          ),
        ),
      ),
    );
  }
}
