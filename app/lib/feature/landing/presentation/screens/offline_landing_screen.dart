import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/forum/presentation/screens/forum_offline_screen.dart';
import 'package:everlms/feature/my_learning_course/presentation/screens/my_course_offline_screen.dart';
import 'package:everlms/feature/settings/presentation/screens/menu_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

class MainOfflineScreen extends StatefulWidget {
  const MainOfflineScreen({required this.pageIndex, super.key});
  final int pageIndex;

  @override
  State<MainOfflineScreen> createState() => _MainOfflineScreenState();
}

class _MainOfflineScreenState extends State<MainOfflineScreen> {
  PageController? _pageController;
  int _currentIndex = 0;
  late List<Widget> _screens;

  @override
  void initState() {
    _currentIndex = widget.pageIndex;

    _pageController = PageController(initialPage: widget.pageIndex);

    _screens = [
      const MyCourseOfflineScreen(),
      const ForumOfflineScreen(),
    ];
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      // onWillPop: () async {
      //   return false;
      // },
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
              label: tl(context).my_courses,
              backgroundColor: Theme.of(context).cardColor,
            ),
            BottomNavigationBarItem(
              icon: SvgPicture.asset(
                Images.chat,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: .5),
                  BlendMode.srcIn,
                ),
              ),
              activeIcon: SvgPicture.asset(
                Images.chat,
                colorFilter: ColorFilter.mode(
                  Theme.of(context).primaryColor,
                  BlendMode.srcIn,
                ),
              ),
              label: tl(context).forum,
              backgroundColor: Theme.of(context).cardColor,
            ),
          ],
        ),
      ),
    );
  }

  void _setPage(int pageIndex) {
    if (pageIndex == 3) {
      showModalBottomSheet(
        context: context,
        backgroundColor: Colors.transparent,
        isScrollControlled: true,
        builder: (_) => const MenuScreen(),
      );
    } else {
      setState(() {
        _pageController!.jumpToPage(pageIndex);
        _currentIndex = pageIndex;
      });
    }
  }
}
