import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/settings/data/models/menu_model.dart';
import 'package:everlms/feature/settings/presentation/providers/theme_provider.dart';
import 'package:everlms/feature/settings/presentation/widgets/menu_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MenuScreen extends ConsumerWidget {
  const MenuScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final width = MediaQuery.of(context).size.width;
    final isLoggedIn = ref.watch(authProvider).valueOrNull?.isLoggedIn ?? false;
    final ratio = ResponsiveHelper.isTab(context) ? 1.1 : 1.2;
    final themeMode = ref.watch(themeProvider);
    final themeTitle = switch (themeMode) {
      ThemeMode.dark => tl(context).light_mode,
      ThemeMode.light => tl(context).dark_mode,
      ThemeMode.system => tl(context).dark_mode,
    };
    final menuList = <MenuModel>[
      if (isLoggedIn)
        MenuModel(
          icon: Images.menuProfile,
          title: tl(context).profile,
          route: AppRoutes.getProfileRoute(),
        ),
      MenuModel(
        icon: Images.languageMenu,
        title: tl(context).language,
        route: 'language',
      ),
      MenuModel(
        icon: Images.conversation,
        title: tl(context).forum,
        route: AppRoutes.forum,
      ),
      MenuModel(
        icon: Images.about,
        title: tl(context).about_us,
        route: AppRoutes.getHtmlRoute('about_us'),
      ),
      MenuModel(
        icon: Images.policy,
        title: tl(context).privacy_policy,
        route: AppRoutes.getHtmlRoute('privacy_policy'),
      ),
      MenuModel(
        icon: Images.termsAndConditions,
        title: tl(context).terms_of_use,
        route: AppRoutes.getHtmlRoute('terms_of_use'),
      ),
      MenuModel(
        icon: Images.darkMode,
        title: themeTitle,
        route: 'dark_mode',
      ),
      MenuModel(
        icon: isLoggedIn ? Images.logout : Images.menuProfile,
        title: isLoggedIn ? tl(context).logout : tl(context).sign_in,
        route: '',
      ),
    ];

    return Container(
      width: width,
      padding:
          const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        color: Theme.of(context).cardColor,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          InkWell(
            onTap: () => context.pop(),
            child: Icon(
              Icons.keyboard_arrow_down_rounded,
              size: 30,
              color: Theme.of(context).primaryColor,
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          GridView.builder(
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: ResponsiveHelper.isTab(context) ? 6 : 4,
              childAspectRatio: 1 / ratio,
              crossAxisSpacing: Dimensions.paddingSizeExtraSmall,
              mainAxisSpacing: Dimensions.paddingSizeExtraSmall,
            ),
            itemCount: menuList.length,
            itemBuilder: (context, index) {
              return MenuButton(
                menu: menuList[index],
                isLogout: index == menuList.length - 1,
              );
            },
          ),
          SizedBox(
            height: ResponsiveHelper.isMobile(context)
                ? Dimensions.paddingSizeSmall
                : 0,
          ),
        ],
      ),
    );
  }
}
