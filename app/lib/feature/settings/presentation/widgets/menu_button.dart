import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/core/widgets/language_change.dart';
import 'package:everlms/core/widgets/ripple_button.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/settings/data/models/menu_model.dart';
import 'package:everlms/feature/settings/presentation/providers/theme_provider.dart';
import 'package:everlms/feature/settings/presentation/widgets/confirmation_dialog.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher_string.dart';

class MenuButton extends ConsumerWidget {
  const MenuButton({required this.menu, required this.isLogout, super.key});
  final MenuModel? menu;
  final bool? isLogout;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final count = ResponsiveHelper.isTab(context) ? 6 : 4;
    final screenWidth = MediaQuery.of(context).size.width;
    final size = ResponsiveHelper.isTab(context)
        ? (screenWidth / count) - 50
        : (screenWidth / count) - Dimensions.paddingSizeExtraMoreLarge;

    return Stack(
      children: [
        Column(
          children: [
            Container(
              height: size - (size * 0.35),
              // margin: const EdgeInsets.symmetric(
              //     horizontal: Dimensions.paddingSizeSmall),
              alignment: Alignment.center,
              child: SvgPicture.asset(menu!.icon!, width: size, height: size),
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            Text(
              menu!.title!,
              style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeSmall),
              textAlign: TextAlign.center,
            ),
          ],
        ),
        Positioned.fill(
          child: RippleButton(
            onTap: () async {
              if (isLogout!) {
                context.pop();
                final isLoggedIn =
                    ref.read(authProvider).valueOrNull?.isLoggedIn ?? false;
                if (isLoggedIn) {
                  showDialog(
                    context: context,
                    builder: (_) => ConfirmationDialog(
                      icon: Images.logout,
                      description: tl(context).are_you_sure_to_logout,
                      isLogOut: true,
                      onYesPressed: () {
                        ref.read(authProvider.notifier).logout();
                        ref.read(cartProvider.notifier).emptyCart();
                        context.go(
                          AppRoutes.getSignInRoute(AppRoutes.splash),
                        );
                      },
                    ),
                    useSafeArea: false,
                  );
                } else {
                  context.push(AppRoutes.getSignInRoute(AppRoutes.main));
                }
              } else if (menu!.route!.startsWith('http')) {
                if (await canLaunchUrlString(menu!.route!)) {
                  launchUrlString(
                    menu!.route!,
                    mode: LaunchMode.externalApplication,
                  );
                }
              } else if (menu!.route!.contains('forum')) {
                context.pop();
                context.push(AppRoutes.forum);
              } else if (menu!.route!.contains('language')) {
                context.pop();
                showModalBottomSheet(
                  context: context,
                  backgroundColor: Colors.transparent,
                  isScrollControlled: true,
                  builder: (_) => const LanguageChange(),
                );
              } else if (menu!.route!.contains('dark_mode')) {
                context.pop();
                ref.read(themeProvider.notifier).toggleTheme();
                // } else if(menu!.route!.contains('about_us') || menu!.route!.contains('privacy_policy') || menu!.route!.contains('terms_of_use')) {
                //   showModalBottomSheet(
                //       context: context,
                //       isScrollControlled: true,
                //       sheetAnimationStyle: AnimationStyle(
                //           curve: Curves.linear,
                //           duration: const Duration(milliseconds: 400)),
                //       builder: (BuildContext context) {
                //         return SizedBox(
                //             height: MediaQuery.of(context).size.height * 0.9,
                //             child: menu!.route!.contains('about_us')
                //                 ? const HtmlViewerScreen(
                //                     htmlType: HtmlType.aboutUs,
                //                   )
                //                 : menu!.route!.contains('privacy_policy')
                //                     ? const HtmlViewerScreen(
                //                         htmlType: HtmlType.privacyPolicy,
                //                       )
                //                     : menu!.route!.contains('terms_of_use')
                //                         ? const HtmlViewerScreen(
                //                             htmlType: HtmlType.termsOfUse,
                //                           )
                //                         : const SizedBox.shrink());
                //       });
              } else {
                context.replace(menu!.route!);
              }
            },
          ),
        ),
      ],
    );
  }
}
