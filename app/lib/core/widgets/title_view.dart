import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class TitleView extends StatelessWidget {
  const TitleView({
    required this.title,
    super.key,
    this.isViewAllEnabled = true,
    this.onViewAllClicked,
  });
  final String title;
  final bool? isViewAllEnabled;
  final Function()? onViewAllClicked;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      spacing: Dimensions.paddingSizeDefault,
      children: [
        Expanded(
          child: Text(
            title,
            style: Theme.of(context).textTheme.headlineSmall,
          ),
        ),
        if (isViewAllEnabled == true)
          GestureDetector(
            onTap: () => onViewAllClicked?.call(),
            child: Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                border: Border.all(
                  color: Theme.of(context).brightness == Brightness.dark
                      ? Theme.of(context)
                          .colorScheme
                          .onSurface
                          .withValues(alpha: 0.6)
                      : Colors.transparent,
                ),
                color: Theme.of(context).brightness == Brightness.dark
                    ? Colors.transparent
                    : Theme.of(context)
                        .colorScheme
                        .onSurface
                        .withValues(alpha: 0.03),
              ),
              child: Text(
                tl(context).view_all,
                style: Theme.of(context).textTheme.labelSmall,
              ),
            ),
          ),
      ],
    );
  }
}
