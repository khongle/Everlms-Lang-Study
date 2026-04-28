import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class UserLogout extends ConsumerWidget {
  const UserLogout({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
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
            tl(context).logout_dialog,
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Divider(
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: 0.06),
            thickness: 1,
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Text(
            tl(context).are_you_sure_logout_from_device,
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
                      color: Theme.of(context).brightness == Brightness.dark
                          ? Theme.of(context).primaryColor
                          : Theme.of(context).primaryColorLight,
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Text(
                      tl(context).no,
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).colorScheme.onSurface,
                        fontSize: Dimensions.fontSizeSmall,
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
                  onTap: () {
                    ref.read(authProvider.notifier).socialLogout();
                    ref.read(authProvider.notifier).logout();
                    ref.read(cartProvider.notifier).emptyCart();
                    context.go(
                      AppRoutes.getSignInRoute(AppRoutes.splash),
                    );
                  },
                  child: Container(
                    height: 44,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.tertiary,
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Text(
                      tl(context).yes,
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).primaryColorLight,
                        fontSize: Dimensions.fontSizeSmall,
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
