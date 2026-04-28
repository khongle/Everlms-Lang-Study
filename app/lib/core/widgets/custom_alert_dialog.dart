import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

Future<void> customAlertDialog(
  BuildContext context,
  String title,
  String middleTitle, {
  VoidCallback? onTap,
}) {
  return showDialog(
    context: context,
    builder: (context) {
      return Dialog(
        backgroundColor: Theme.of(context).colorScheme.surface,
        insetPadding: const EdgeInsets.symmetric(horizontal: 70),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
        clipBehavior: Clip.antiAliasWithSaveLayer,

        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(
              height: Dimensions.paddingSizeRadius,
            ),
            Icon(
              Icons.error_outline,
              color: Theme.of(context).colorScheme.error,
              size: 40,
            ),
            const SizedBox(
              height: Dimensions.paddingSizeExtraSmall,
            ),
            Text(
              title,
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeDefault,
              ),
              child: Text(
                middleTitle,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
            const Divider(),
            TextButton(
              onPressed: () {
                if (onTap != null) {
                  onTap();
                }
                context.pop();
              },
              child: Text(
                tl(context).got_it,
                style: Theme.of(context).textTheme.bodyMedium!.copyWith(
                      color: Theme.of(context).primaryColor,
                      fontSize: Dimensions.fontSizeExtraLarge,
                    ),
              ),
            ),
          ],
        ),
      );
    },
  );
}
