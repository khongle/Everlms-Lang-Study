import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/search_screen/presentation/widgets/search_widget.dart';
import 'package:flutter/material.dart';

class SearchAppBar extends StatelessWidget implements PreferredSizeWidget {
  const SearchAppBar({super.key, this.backButton = true});
  final bool? backButton;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: Theme.of(context).primaryColor,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const SizedBox(
            height: Dimensions.paddingSizeExtraLarge,
          ),
          Row(
            children: [
              if (backButton!)
                Padding(
                  padding: const EdgeInsets.only(
                    left: Dimensions.paddingSizeExtraLarge,
                  ),
                  child: IconButton(
                    icon: const Icon(
                      Icons.arrow_back_ios,
                    ),
                    color: Theme.of(context).primaryColorLight,
                    onPressed: () => Navigator.pop(context),
                  ),
                )
              else
                const SizedBox(),
              const Expanded(child: SearchWidget()),
            ],
          ),
        ],
      ),
    );
  }

  @override
  Size get preferredSize => const Size(double.infinity, 70);
}
