import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

Future<void> customDialog({
  required String title,
  required String middleTitle,
  required String textConfirm,
  required VoidCallback onConfirm,
  required BuildContext context,
  String? textCancel,
  VoidCallback? onCancel,
  String? imageSvg,
  Widget? image,
  Color? colorCancel,
  Color? colorConfirm,
}) {
  return showDialog(
    context: context,
    barrierDismissible: false,
    builder: (context) => Dialog(
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      insetPadding: const EdgeInsets.symmetric(horizontal: 50),
      clipBehavior: Clip.antiAliasWithSaveLayer,
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            image ??
                ((imageSvg?.isNotEmpty ?? false)
                    ? Padding(
                        padding: const EdgeInsets.only(
                          top: Dimensions.paddingSizeSmall,
                          bottom: Dimensions.paddingSizeDefault,
                        ),
                        child: SvgPicture.asset(
                          imageSvg ?? '',
                          width: 40,
                          height: 40,
                        ),
                      )
                    : const SizedBox.shrink()),
            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeLarge,
              ),
              child: Text(
                title,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.headlineSmall,
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: Text(
                middleTitle,
                style: Theme.of(context).textTheme.bodyMedium,
                textAlign: TextAlign.center,
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: () {
                      context.pop();
                      if (onCancel != null) {
                        onCancel();
                      }
                    },
                    style: TextButton.styleFrom(
                      // backgroundColor: Theme.of(context).highlightColor.withValues(alpha: 0.3),
                      padding: EdgeInsets.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(
                          Dimensions.radiusSmall,
                        ),
                      ),
                    ),
                    child: Text(
                      textCancel ?? 'Cancel',
                      textAlign: TextAlign.center,
                      style: Theme.of(context)
                          .textTheme
                          .titleMedium!
                          .copyWith(color: Theme.of(context).colorScheme.error),
                    ),
                  ),
                ),
                const SizedBox(
                  width: Dimensions.paddingSizeDefault,
                ),
                Expanded(
                  child: TextButton(
                    onPressed: () async {
                      context.pop();
                      await Future.delayed(const Duration(microseconds: 50));
                      onConfirm();
                    },
                    style: TextButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      padding: EdgeInsets.zero,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(
                          Dimensions.radiusSmall,
                        ),
                      ),
                    ),
                    child: Text(
                      textConfirm,
                      textAlign: TextAlign.center,
                      style: Theme.of(context)
                          .textTheme
                          .titleMedium!
                          .copyWith(color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    ),
  );
}
