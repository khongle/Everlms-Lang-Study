import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class BottomSheetConfirm extends StatelessWidget {
  const BottomSheetConfirm({
    required this.onConfirm,
    required this.title,
    required this.middleTitle,
    required this.textConfirm,
    super.key,
  });
  final VoidCallback onConfirm;
  final String title;
  final String middleTitle;
  final String textConfirm;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeDefault,
      ),
      decoration: BoxDecoration(
        borderRadius: const BorderRadius.vertical(
          top: Radius.circular(Dimensions.radiusSmall),
        ),
        color: Theme.of(context).cardColor,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          const Divider(
            thickness: 1,
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Text(
            middleTitle,
            style: Theme.of(context).textTheme.bodyMedium,
          ),
          const SizedBox(height: Dimensions.paddingSizeLarge),
          Row(
            children: [
              //no button
              Expanded(
                child: InkWell(
                  onTap: () => context.pop(),
                  child: Container(
                    height: 44,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primaryContainer,
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Text(
                      tl(context).cancel,
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).colorScheme.onSurface,
                        fontSize: Dimensions.fontSizeDefault,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(
                width: Dimensions.paddingSizeDefault,
              ),
              // yes button
              Expanded(
                child: InkWell(
                  onTap: onConfirm,
                  child: Container(
                    height: 44,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.error,
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Text(
                      textConfirm,
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).primaryColorLight,
                        fontSize: Dimensions.fontSizeDefault,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraLarge),
        ],
      ),
    );
  }
}
