import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_default_dialog.dart';
import 'package:everlms/core/widgets/language_change.dart';
import 'package:everlms/core/widgets/select_domain.dart';
import 'package:everlms/feature/settings/presentation/providers/theme_provider.dart';
import 'package:everlms/feature/settings/presentation/widgets/settings_button_section.dart';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:package_info_plus/package_info_plus.dart';

class SettingScreen extends ConsumerStatefulWidget {
  const SettingScreen({super.key});

  @override
  ConsumerState<SettingScreen> createState() => _SettingScreenState();
}

class _SettingScreenState extends ConsumerState<SettingScreen> {
  PackageInfo? _packageInfo;

  IconData _themeIcon(ThemeMode mode) {
    return switch (mode) {
      ThemeMode.dark => Icons.dark_mode,
      ThemeMode.light => Icons.light_mode,
      ThemeMode.system =>
        SchedulerBinding.instance.platformDispatcher.platformBrightness ==
                Brightness.dark
            ? Icons.dark_mode
            : Icons.light_mode,
    };
  }

  @override
  void initState() {
    super.initState();
    _initPackageInfo();
  }

  Future<void> _initPackageInfo() async {
    final info = await PackageInfo.fromPlatform();
    setState(() {
      _packageInfo = info;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).setting,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            titleSection(context, tl(context).appearance),
            SettingsButtonSection(
              titleBtn: tl(context).change_theme,
              leadingBtn: Images.darkMode,
              trailing: _themeIcon(ref.watch(themeProvider)),
              onTap: () => ref.read(themeProvider.notifier).toggleTheme(),
            ),
            // dividerSection(context),
            // Padding(
            //   padding: EdgeInsets.symmetric(horizontal: horizonalPadding),
            //   child: language(context),
            // ),
            dividerSection(context),
            titleSection(context, tl(context).legal),
            SettingsButtonSection(
              leadingBtn: Images.about,
              titleBtn: tl(context).about_us,
              onTap: () => context.push(AppRoutes.getHtmlRoute('about_us')),
            ),
            SettingsButtonSection(
              leadingBtn: Images.policy,
              titleBtn: tl(context).privacy_policy,
              onTap: () =>
                  context.push(AppRoutes.getHtmlRoute('privacy_policy')),
            ),
            SettingsButtonSection(
              leadingBtn: Images.termsAndConditions,
              titleBtn: tl(context).terms_of_use,
              onTap: () => context.push(AppRoutes.getHtmlRoute('terms_of_use')),
            ),
            dividerSection(context),
            switchDomain(context),
            dividerSection(context),
            Padding(
              padding: const EdgeInsets.only(bottom: 40),
              child: Text(
                'Version ${_packageInfo?.version}',
                style: Theme.of(context)
                    .textTheme
                    .labelSmall!
                    .copyWith(color: Theme.of(context).hintColor),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Text titleSection(BuildContext context, String title) {
    return Text(
      title.toUpperCase(),
      style: Theme.of(context).textTheme.titleMedium,
    );
  }

  Column language(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        titleSection(context, tl(context).language),
        SettingsButtonSection(
          leadingBtn: Images.languageChange,
          titleBtn: tl(context).change_language,
          onTap: () => showModalBottomSheet(
            context: context,
            backgroundColor: Colors.transparent,
            isScrollControlled: true,
            builder: (_) => const LanguageChange(),
          ),
        ),
      ],
    );
  }

  Widget switchDomain(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        titleSection(context, tl(context).switch_domain),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeExtraSmall,
          ),
          child: SettingsButtonSection(
            leadingBtn: Images.refresh,
            leadingSize: 20,
            titleBtn: tl(context).switch_domain,
            onTap: () => customDialog(
              title: tl(context).switch_domain,
              middleTitle: tl(context).switch_domain_message,
              textConfirm: 'Continue',
              onConfirm: () {
                showDialog(
                  context: context,
                  builder: (context) => const SelectDomain(),
                );
              },
              context: context,
            ),
          ),
        ),
      ],
    );
  }

  Widget dividerSection(BuildContext context) {
    return Divider(
      thickness: 0.3,
      color: Theme.of(context).dividerColor.withValues(alpha: 0.6),
    );
  }
}
