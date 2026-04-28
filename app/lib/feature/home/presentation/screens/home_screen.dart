import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/course_category/presentation/screens/course_category_screen.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/home/presentation/widgets/category_course_bar.dart';
import 'package:everlms/feature/home/presentation/widgets/custom_snackbar_suggest.dart';
import 'package:everlms/feature/home/presentation/widgets/list_courses_widget.dart';
import 'package:everlms/feature/my_courses/presentation/screens/my_courses_screen.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:everlms/feature/profile/presentation/screens/profile_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({
    required this.callback,
    required this.navigatorKey,
    super.key,
  });
  final Function() callback;
  final GlobalKey<NavigatorState> navigatorKey;

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen>
    with AutomaticKeepAliveClientMixin, TickerProviderStateMixin {
  @override
  bool get wantKeepAlive => true;
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();
    ref.listenManual(userProfileProvider, (_, __) {});
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(userProfileProvider.notifier).getUserProfile().then((value) {
        if (!mounted) return;
        final userData = ref.read(userProfileProvider).valueOrNull?.userData;
        if (userData?.roles == AppConstants.teacher) {
          // Teacher redirect: reserved for future role-based nav
        }
      });
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (Config.baseUrl == Config.defaultDomain) {
      _animationController = AnimationController(
        vsync: this,
        duration: const Duration(milliseconds: 300),
        reverseDuration: const Duration(milliseconds: 200),
      );
      showSnackBarSuggest(context, _animationController);
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return Navigator(
      key: widget.navigatorKey,
      onGenerateRoute: (settings) {
        return MaterialPageRoute(
          settings: settings,
          builder: (context) {
            if (settings.name == AppRoutes.myCourseScreen) {
              return const MyCoursesScreen();
            }
            if (settings.name == AppRoutes.profile) {
              return const ProfileScreen();
            }
            return main(context);
          },
        );
      },
    );
  }

  Widget main(BuildContext context) {
    return Scaffold(
      body: ref.watch(homeProvider).when(
            loading: () => const LoadingIndicator(),
            error: (_, __) => Container(),
            data: (homeState) {
              if (homeState.allCourses.isNotEmpty) {
                return CustomScrollView(
                  slivers: [
                    SliverAppBar(
                      expandedHeight: 50,
                      backgroundColor:
                          Theme.of(context).scaffoldBackgroundColor,
                      surfaceTintColor: Colors.transparent,
                    ),
                    SliverPadding(
                      padding:
                          const EdgeInsets.all(Dimensions.paddingSizeDefault),
                      sliver: SliverToBoxAdapter(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              tl(context).explore,
                              style: Theme.of(context).textTheme.headlineMedium,
                            ),
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                vertical: Dimensions.paddingSizeDefault,
                              ),
                              child: categoryCourses(homeState),
                            ),
                            const SizedBox(height: Dimensions.paddingSizeSmall),
                            Column(
                              children: listCourseHighlight(),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                );
              }
              return Container();
            },
          ),
    );
  }

  List<Widget> listCourseHighlight() {
    final groups = ref.read(homeProvider.notifier).getHighlightGroups();
    return groups
        .map(
          (group) => ListCoursesWidget(
            list: group.courses,
            title: group.title,
            axis: group.axis,
          ),
        )
        .toList();
  }

  Widget categoryCourses(HomeState homeState) {
    return Column(
      children: [
        TitleView(
          title: tl(context).course_collection,
          onViewAllClicked: () {
            widget.navigatorKey.currentState!.push(
              PageRouteBuilder(
                pageBuilder: (context, first, second) => CourseCategoryScreen(
                  categories: homeState.categoryCourses.toList(),
                  listCourse: homeState.allCourses,
                ),
                transitionsBuilder:
                    (context, animation, secondaryAnimation, child) {
                  final offsetAnimation = Tween(
                    begin: const Offset(1, 0),
                    end: Offset.zero,
                  ).animate(
                    CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeOut,
                    ),
                  );

                  return SlideTransition(
                    position: offsetAnimation,
                    child: child,
                  );
                },
              ),
            );
          },
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        const CategoryCourseBar(),
      ],
    );
  }
}
