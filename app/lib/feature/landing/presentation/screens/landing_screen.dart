import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/course_catalog/presentation/screens/course_catalog_screen.dart';
import 'package:everlms/feature/dashboard/presentation/screens/home_dashboard_screen.dart';
import 'package:everlms/feature/my_courses/presentation/screens/my_courses_screen.dart';
import 'package:everlms/feature/profile/presentation/screens/profile_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

final mainScreenKey = GlobalKey<_MainScreenState>();

/// Phase-01 main shell with 4 fixed bottom-nav tabs:
/// Home (Dashboard) · Search (Course Catalog) · Learn (My Courses) · Profile.
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
  late final List<Widget> _screens;
  StreamSubscription<bool>? _connectivitySub;
  Timer? _offlineDebounce;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.pageIndex;
    _pageController = PageController(initialPage: widget.pageIndex);
    _screens = const [
      HomeDashboardScreen(),
      CourseCatalogScreen(),
      MyCoursesScreen(),
      ProfileScreen(),
    ];
    _initConnectivityListener();
  }

  Future<void> _initConnectivityListener() async {
    final initiallyConnected = await NetworkService().checkInternetConnection();
    if (!mounted) return;
    _connectivitySub =
        NetworkService().networkStatusStream.listen((isConnected) {
      if (!isConnected) {
        _offlineDebounce ??= Timer(const Duration(seconds: 3), () async {
          if (!mounted) return;
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

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (_currentIndex != 0) {
          _setPage(0);
          return;
        }
        if (_doubleBackToExitPressedOnce) return;
        _doubleBackToExitPressedOnce = true;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(tl(context).back_press_again_to_exit),
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 2),
            margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          ),
        );
        Future.delayed(const Duration(seconds: 2), () {
          _doubleBackToExitPressedOnce = false;
        });
      },
      child: Scaffold(
        body: PageView.builder(
          controller: _pageController,
          onPageChanged: _setPage,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) => _screens[index],
          itemCount: _screens.length,
        ),
        bottomNavigationBar: _buildBottomNav(context),
      ),
    );
  }

  Widget _buildBottomNav(BuildContext context) {
    final theme = Theme.of(context);
    final unselected = theme.colorScheme.onSurface.withValues(alpha: .55);
    return Container(
      decoration: BoxDecoration(
        color: theme.bottomNavigationBarTheme.backgroundColor,
        border: Border(top: BorderSide(color: theme.dividerColor)),
      ),
      child: SafeArea(
        top: false,
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.transparent,
          elevation: 0,
          selectedItemColor: AppColors.accentPrimary,
          unselectedItemColor: unselected,
          selectedFontSize: 11,
          unselectedFontSize: 11,
          onTap: _setPage,
          items: [
            BottomNavigationBarItem(
              icon: const Icon(Icons.home_outlined),
              activeIcon: const Icon(Icons.home_rounded),
              label: tl(context).home,
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.search_outlined),
              activeIcon: const Icon(Icons.search_rounded),
              label: tl(context).search,
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.play_circle_outline),
              activeIcon: const Icon(Icons.play_circle),
              label: tl(context).learn,
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.person_outline),
              activeIcon: const Icon(Icons.person),
              label: tl(context).profile,
            ),
          ],
        ),
      ),
    );
  }

  void _setPage(int pageIndex) {
    if (pageIndex < 0 || pageIndex >= _screens.length) return;
    setState(() {
      _pageController!.jumpToPage(pageIndex);
      _currentIndex = pageIndex;
    });
  }
}
