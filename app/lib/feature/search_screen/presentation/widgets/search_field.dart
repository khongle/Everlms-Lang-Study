import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class SearchField extends ConsumerStatefulWidget {
  const SearchField({
    required this.focusSearch,
    required this.textController,
    super.key,
  });
  final FocusNode focusSearch;
  final TextEditingController textController;

  @override
  ConsumerState<SearchField> createState() => _SearchFieldState();
}

class _SearchFieldState extends ConsumerState<SearchField> {
  Timer? _debounce;

  @override
  void dispose() {
    _debounce?.cancel();
    super.dispose();
  }

  void _onSearchChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 500), () {
      if (value.trim().isNotEmpty) {
        ref.read(searchProvider.notifier).searchData(value);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: CustomTextField(
            controller: widget.textController,
            focusNode: widget.focusSearch,
            inputAction: TextInputAction.search,
            onChanged: (value) {
              setState(() {});
              _onSearchChanged(value);
            },
            onSubmit: (value) => ref
                .read(searchProvider.notifier)
                .searchData(value, updateHistory: true),
            fillColor: Theme.of(context).colorScheme.primaryContainer,
            isShowBorder: false,
            hintText: 'Search',
            prefixIcon: Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: SvgPicture.asset(
                Images.search,
                colorFilter:
                    const ColorFilter.mode(Colors.grey, BlendMode.srcIn),
              ),
            ),
          ),
        ),
        if (widget.focusSearch.hasFocus ||
            widget.textController.text.isNotEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeSmall,
            ),
            child: InkWell(
              onTap: () {
                widget.focusSearch.unfocus();
                widget.textController.clear();
                ref.read(searchProvider.notifier).clearResults();
              },
              child: Text(
                tl(context).cancel,
                style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                      color: Theme.of(context).primaryColor,
                    ),
              ),
            ),
          )
        else
          const SizedBox.shrink(),
      ],
    );
  }
}
