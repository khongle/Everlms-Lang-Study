import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_default_dialog.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:everlms/feature/profile/presentation/widgets/bottom_sheet_custom.dart';
import 'package:everlms/feature/settings/presentation/widgets/settings_button_section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen>
    with TickerProviderStateMixin {
  bool lastStatus = true;
  bool isHide = false;
  final double horizonalPadding = Dimensions.paddingSizeDefault;
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final profile = ref.read(userProfileProvider).valueOrNull;
      if (profile?.userData == null && !(profile?.isLoading ?? false)) {
        ref.read(userProfileProvider.notifier).getUserProfile();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return mainUI(context);
  }

  Widget mainUI(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 50,
              backgroundColor: Theme.of(context).scaffoldBackgroundColor,
              actions: [
                InkWell(
                  onTap: () => context.push(AppRoutes.settingScreen),
                  child: Padding(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
                    child: Icon(
                      Icons.settings_outlined,
                      size: 30,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
              ],
            ),
            SliverPadding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              sliver: SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      tl(context).profile,
                      style: Theme.of(context).textTheme.headlineMedium,
                    ),
                    profile(context),
                    const SizedBox(
                      height: Dimensions.paddingSizeDefault,
                    ),
                    titleSection(context, tl(context).download),
                    buttonSection(
                      Images.download,
                      tl(context).download_certificates,
                      () => context.push(AppRoutes.userCertificateScreen),
                    ),
                    dividerSection(context),
                    titleSection(context, tl(context).my_courses),
                    buttonSection(
                      Images.myCourse,
                      tl(context).my_courses,
                      () => context.push(AppRoutes.myCourseScreen),
                    ),
                    dividerSection(context),
                    titleSection(context, tl(context).social),
                    buttonSection(
                      Images.message,
                      tl(context).forum,
                      () => context.push(AppRoutes.forum),
                    ),
                    buttonSection(
                      Images.document,
                      tl(context).news,
                      () => context.push(AppRoutes.news),
                    ),
                    if (Config.baseUrl != Config.defaultDomain)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          dividerSection(context),
                          titleSection(context, tl(context).account),
                          buttonSection(
                            Images.profile,
                            tl(context).update_profile,
                            () => context.push(AppRoutes.editProfileScreen),
                          ),
                          buttonSection(
                            Images.changePassword,
                            tl(context).changePassword,
                            () => context.push(AppRoutes.changePassword),
                          ),
                          SettingsButtonSection(
                            leadingBtn: Images.profileLogout,
                            leadingSize: 20,
                            titleBtn: tl(context).log_out,
                            isShowTrailing: false,
                            color: Theme.of(context).colorScheme.error,
                            onTap: () => showModalBottomSheet(
                              context: context,
                              builder: (ctx) => BottomSheetConfirm(
                                title: tl(context).logout_dialog,
                                middleTitle:
                                    tl(context).are_you_sure_logout_from_device,
                                textConfirm: tl(context).logout,
                                onConfirm: () =>
                                    ref.read(authProvider.notifier).logout(),
                              ),
                            ),
                          ),
                          SettingsButtonSection(
                            leadingBtn: Images.deleteAccount,
                            leadingSize: 20,
                            titleBtn: tl(context).delete_account,
                            isShowTrailing: false,
                            color: Theme.of(context).colorScheme.error,
                            onTap: () => showModalBottomSheet(
                              context: context,
                              builder: (context) {
                                final message =
                                    'Are your sure you want to delete your account?  All associated data will be permanently removed. '
                                        .trim();
                                return BottomSheetConfirm(
                                  title: tl(context).delete_account,
                                  middleTitle: message,
                                  textConfirm: tl(context).delete_account,
                                  onConfirm: () {
                                    context.pop();
                                    customDialog(
                                      imageSvg: Images.deleteAccount,
                                      title: tl(context).delete_account,
                                      middleTitle:
                                          'You can undo changes after delete',
                                      textConfirm: 'Delete',
                                      onConfirm: () {
                                        ref
                                            .read(userProfileProvider.notifier)
                                            .removeUser();
                                      },
                                      context: context,
                                    );
                                  },
                                );
                              },
                              isScrollControlled: true,
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget buttonSection(String image, String title, VoidCallback onTap) {
    return SettingsButtonSection(
      titleBtn: title,
      leadingBtn: image,
      leadingSize: 20,
      onTap: onTap,
    );
  }

  Widget profile(BuildContext context) {
    return Consumer(
      builder: (context, ref, child) {
        final userState = ref.watch(userProfileProvider).valueOrNull;
        final userData = userState?.userData;
        final name = userData?.displayName ?? '';
        final mail = userData?.displayEmail ?? '';
        final image = userData?.displayAvatarUrl ?? '';
        return Row(
          children: [
            Expanded(
              child: Config.baseUrl == Config.defaultDomain
                  ? Text(
                      'Guest Account',
                      style: Theme.of(context).textTheme.headlineSmall,
                    )
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          name.isEmpty ? 'Student' : name,
                          style: Theme.of(context).textTheme.headlineSmall,
                        ),
                        if (!mail.contains('.temp'))
                          Text(
                            mail,
                            style: Theme.of(context).textTheme.bodyMedium,
                          )
                        else
                          const SizedBox.shrink(),
                      ],
                    ),
            ),
            if (image != '')
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
                child: ClipOval(
                  child: Image.network(
                    image,
                    fit: BoxFit.cover,
                    height: 64,
                    width: 64,
                    errorBuilder: (context, error, stackTrace) => Image.asset(
                      Images.placeholder,
                      fit: BoxFit.cover,
                      height: 64,
                      width: 64,
                    ),
                  ),
                ),
              )
            else
              SizedBox(
                width: 64,
                child: CircleAvatar(
                  radius: 32,
                  backgroundColor: Theme.of(context).shadowColor,
                  child: SvgPicture.asset(
                    Images.profile,
                    height: 24,
                    colorFilter: ColorFilter.mode(
                      Theme.of(context)
                          .colorScheme
                          .onPrimaryFixedVariant
                          .withValues(alpha: 0.6),
                      BlendMode.srcIn,
                    ),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  Widget dividerSection(BuildContext context) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
      child: Divider(
        height: 0.5,
        color: Theme.of(context).highlightColor,
      ),
    );
  }

  Text titleSection(BuildContext context, String title) {
    return Text(
      title.toUpperCase(),
      style: Theme.of(context).textTheme.titleMedium,
    );
  }
}
