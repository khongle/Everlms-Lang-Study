import 'dart:async';

import 'package:badges/badges.dart' as badges;
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/home/presentation/screens/home_screen.dart';
import 'package:everlms/feature/landing/presentation/widgets/introduce_ecosystem.dart';
import 'package:everlms/feature/my_learning_course/presentation/screens/my_learning_course_screen.dart';
import 'package:everlms/feature/profile/presentation/screens/profile_screen.dart';
import 'package:everlms/feature/search_screen/presentation/screens/search_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

final mainScreenKey = GlobalKey<_MainScreenState>();

class MainScreen extends ConsumerStatefulWidget {
  const MainScreen({required this.pageIndex, super.key});
  final int pageIndex;

  @override
  ConsumerState<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends ConsumerState<MainScreen> {
  bool _doubleBackToExitPressedOnce = false;
  PageController? _pageController;
  int _currentIndex = 0;
  late List<Widget> _screens;
  final landingNavigatorKey = GlobalKey<NavigatorState>();
  StreamSubscription<bool>? _connectivitySub;
  Timer? _offlineDebounce;

  @override
  void initState() {
    _currentIndex = widget.pageIndex;
    _pageController = PageController(initialPage: widget.pageIndex);
    _screens = [
      HomeScreen(
        navigatorKey: landingNavigatorKey,
        callback: () {
          setState(() {
            _pageController!.jumpToPage(2);
            _currentIndex = 2;
          });
        },
      ),
      const SearchScreen(),
      const MyLearningCourseScreen(),
      const ProfileScreen(),
    ];
    promptUpdateProfile();
    // Do an explicit connectivity check first. If we're actually online,
    // ignore the first false event from the stream (transient on restart).
    _initConnectivityListener();
    super.initState();
  }

  Future<void> _initConnectivityListener() async {
    final initiallyConnected = await NetworkService().checkInternetConnection();
    if (!mounted) return;
    // Listen for sustained connectivity loss (debounced 3 s to ignore
    // brief transient events fired at startup by connectivity_plus on iOS).
    _connectivitySub =
        NetworkService().networkStatusStream.listen((isConnected) {
      if (!isConnected) {
        _offlineDebounce ??= Timer(const Duration(seconds: 3), () async {
          if (!mounted) return;
          // Double-check actual connectivity before navigating to offline.
          final stillOffline =
              !await NetworkService().checkInternetConnection();
          if (!mounted || !stillOffline) {
            _offlineDebounce = null;
            return;
          }
          context.push(AppRoutes.mainOfflineScreen);
          _offlineDebounce = null;
        });
      } else {
        _offlineDebounce?.cancel();
        _offlineDebounce = null;
      }
    });
    // If initially connected, emit a connected event so any pending
    // debounce from a stale stream event is cancelled.
    if (initiallyConnected) {
      _offlineDebounce?.cancel();
      _offlineDebounce = null;
    }
  }

  @override
  void dispose() {
    _connectivitySub?.cancel();
    _offlineDebounce?.cancel();
    _pageController?.dispose();
    super.dispose();
  }

  Future<void> promptUpdateProfile() async {
    final notifier = ref.read(authProvider.notifier);
    final isFirstLogin = notifier.isFirstLogin;
    final isProfileComplete = await notifier.isProfileComplete();
    final isDefaultDomain = Config.baseUrl != Config.defaultDomain;
    if (isFirstLogin && !isProfileComplete && isDefaultDomain) {
      notifier.updateFirstLogin(false);
      Future.delayed(
        const Duration(seconds: 1),
        () {
          if (!mounted) return;
          showModalPromptUpdateProfile(context);
        },
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (_currentIndex != 0) {
          _setPage(0);
          return;
        } else {
          if (_doubleBackToExitPressedOnce) {
            return;
          }
          _doubleBackToExitPressedOnce = true;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                tl(context).back_press_again_to_exit,
                style: const TextStyle(color: Colors.white),
              ),
              behavior: SnackBarBehavior.floating,
              backgroundColor: Colors.amber,
              duration: const Duration(seconds: 2),
              margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            ),
          );
          // Reset the flag after 2 seconds
          Future.delayed(const Duration(seconds: 2), () {
            _doubleBackToExitPressedOnce = false;
          });
          return;
          // return false;
        }
      },
      child: Scaffold(
        body: PageView.builder(
          controller: _pageController,
          onPageChanged: _setPage,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) {
            return _screens[index];
          },
        ),
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: _currentIndex,
          type: BottomNavigationBarType.fixed,
          selectedFontSize: 12,
          selectedLabelStyle: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeSmall,
            color: Theme.of(context).primaryColor,
          ),
          unselectedItemColor: Theme.of(context)
              .colorScheme
              .onSurface
              .withValues(alpha: .5),
          selectedItemColor: Theme.of(context).primaryColor,
          unselectedLabelStyle: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeSmall,
            color: Theme.of(context).colorScheme.onSurface,
          ),
          elevation: 5,
          showSelectedLabels: true,
          showUnselectedLabels: true,
          onTap: _setPage,
          items: <BottomNavigationBarItem>[
            BottomNavigationBarItem(
              icon: SvgPicture.asset(
                Images.home,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: .5),
                  BlendMode.srcIn,
                ),
              ),
              activeIcon: SvgPicture.asset(
                Images.home,
                colorFilter: ColorFilter.mode(
                  Theme.of(context).primaryColor,
                  BlendMode.srcIn,
                ),
              ),
              label: tl(context).home,
              backgroundColor: Theme.of(context).cardColor,
            ),
            BottomNavigationBarItem(
              icon: SvgPicture.asset(
                Images.search,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: .5),
                  BlendMode.srcIn,
                ),
              ),
              activeIcon: SvgPicture.asset(
                Images.search,
                colorFilter: ColorFilter.mode(
                  Theme.of(context).primaryColor,
                  BlendMode.srcIn,
                ),
              ),
              label: tl(context).search,
              backgroundColor: Theme.of(context).cardColor,
            ),
            BottomNavigationBarItem(
              icon: SvgPicture.asset(
                Images.myCourse,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: .5),
                  BlendMode.srcIn,
                ),
              ),
              activeIcon: SvgPicture.asset(
                Images.myCourse,
                colorFilter: ColorFilter.mode(
                  Theme.of(context).primaryColor,
                  BlendMode.srcIn,
                ),
              ),
              label: tl(context).learn,
              backgroundColor: Theme.of(context).cardColor,
            ),
            BottomNavigationBarItem(
              icon: SvgPicture.asset(
                Images.profile,
                height: 18,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: .5),
                  BlendMode.srcIn,
                ),
              ),
              activeIcon: SvgPicture.asset(
                Images.profile,
                height: 18,
                colorFilter: ColorFilter.mode(
                  Theme.of(context).primaryColor,
                  BlendMode.srcIn,
                ),
              ),
              label: tl(context).profile,
              backgroundColor: Theme.of(context).cardColor,
            ),
            if (Config.baseUrl == Config.defaultDomain)
              BottomNavigationBarItem(
                icon: badges.Badge(
                  child: SvgPicture.asset(
                    Images.more,
                    height: 18,
                    colorFilter: ColorFilter.mode(
                      Theme.of(context)
                          .colorScheme
                          .onSurface
                          .withValues(alpha: .5),
                      BlendMode.srcIn,
                    ),
                  ),
                ),
                label: tl(context).discovery,
                backgroundColor: Theme.of(context).cardColor,
              ),
          ],
        ),
      ),
    );
  }

  void showModalPromptUpdateProfile(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.topRight,
              child: IconButton.filled(
                onPressed: () => context.pop(),
                highlightColor: Colors.transparent,
                style: IconButton.styleFrom(
                  backgroundColor:
                      Theme.of(context).highlightColor.withValues(alpha: 0.4),
                ),
                icon: const Icon(
                  Icons.close,
                  size: Dimensions.iconSizeExtraLarge,
                ),
              ),
            ),
            Image.asset(
              Images.updateProfile,
              height: 250,
            ),
            Text(
              tl(context).prompt_update_profile_message,
              textAlign: TextAlign.center,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeExtraLarge,
              ),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeExtraLarge,
            ),
            CustomButton(
              buttonText: tl(context).update_profile,
              radius: Dimensions.radiusExtraLarge,
              margin: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeExtraLarge,
              ),
              onPressed: () {
                context.pop();
                context.push(AppRoutes.editProfileScreen);
              },
            ),
            const SizedBox(
              height: Dimensions.paddingSizeExtraLarge,
            ),
          ],
        ),
      ),
    );
  }

  void _setPage(int pageIndex) {
    if (pageIndex == _screens.length) {
      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        sheetAnimationStyle: const AnimationStyle(
          curve: Curves.linear,
          reverseCurve: Curves.linear,
          duration: Duration(milliseconds: 300),
          reverseDuration: Duration(milliseconds: 300),
        ),
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadiusDirectional.vertical(
            top: Radius.circular(Dimensions.radiusLarge),
          ),
        ),
        useSafeArea: true,
        builder: (context) {
          return const IntroduceEcosystem();
        },
      );
    } else {
      if (_currentIndex == pageIndex) {
        landingNavigatorKey.currentState?.popUntil((route) => route.isFirst);
      }
      setState(() {
        _pageController!.jumpToPage(pageIndex);
        _currentIndex = pageIndex;
      });
    }
  }
}
