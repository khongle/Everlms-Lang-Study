import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ConfirmationDialog extends ConsumerWidget {
  const ConfirmationDialog({
    required this.icon,
    required this.description,
    required this.onYesPressed,
    super.key,
    this.title,
    this.isLogOut = false,
    this.onNoPressed,
    this.widget,
  });
  final String? icon;
  final String? title;
  final String? description;
  final Function()? onYesPressed;
  final bool? isLogOut;
  final Function? onNoPressed;
  final Widget? widget;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final width = MediaQuery.of(context).size.width;
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      insetPadding: const EdgeInsets.all(30),
      clipBehavior: Clip.antiAliasWithSaveLayer,
      child: SizedBox(
        width: 300,
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          child: widget ??
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const SizedBox(
                    height: Dimensions.paddingSizeSmall,
                  ),
                  SvgPicture.asset(icon!, width: 50, height: 50),
                  if (title != null)
                    Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: Dimensions.paddingSizeLarge,
                      ),
                      child: Text(
                        title!,
                        textAlign: TextAlign.center,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeExtraLarge,
                          color: Colors.red,
                        ),
                      ),
                    )
                  else
                    Padding(
                      padding:
                          const EdgeInsets.all(Dimensions.paddingSizeDefault),
                      child: Text(
                        description!,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeLarge,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  Builder(
                    builder: (context) {
                      final isLoading = ref
                              .watch(userProfileProvider)
                              .valueOrNull
                              ?.isLoading ??
                          false;
                      return isLoading
                          ? const Center(
                              child: LoadingIndicator(),
                            )
                          : Row(
                              children: [
                                Expanded(
                                  child: TextButton(
                                    onPressed: () => isLogOut!
                                        ? onYesPressed!()
                                        : onNoPressed != null
                                            ? onNoPressed!()
                                            : context.pop(),
                                    style: TextButton.styleFrom(
                                      backgroundColor: Theme.of(context)
                                          .disabledColor
                                          .withValues(alpha: 0.3),
                                      minimumSize: Size(width, 35),
                                      padding: EdgeInsets.zero,
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                          Dimensions.radiusSmall,
                                        ),
                                      ),
                                    ),
                                    child: Text(
                                      isLogOut!
                                          ? tl(context).yes
                                          : tl(context).no,
                                      textAlign: TextAlign.center,
                                      style: robotoBold.copyWith(
                                        color: Theme.of(context)
                                            .textTheme
                                            .bodyLarge!
                                            .color,
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(
                                  width: Dimensions.paddingSizeLarge,
                                ),
                                Expanded(
                                  child: CustomButton(
                                    buttonText: isLogOut!
                                        ? tl(context).no
                                        : tl(context).yes,
                                    fontSize: Dimensions.fontSizeSmall,
                                    onPressed: () => isLogOut!
                                        ? context.pop()
                                        : onYesPressed!(),
                                    height: 35,
                                  ),
                                ),
                              ],
                            );
                    },
                  ),
                ],
              ),
        ),
      ),
    );
  }
}
