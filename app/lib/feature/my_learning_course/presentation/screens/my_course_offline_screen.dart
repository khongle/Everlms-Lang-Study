import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/my_learning_course/presentation/providers/my_course_offline_provider.dart';
import 'package:everlms/feature/my_learning_course/presentation/widgets/my_course_offline_widget_item.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MyCourseOfflineScreen extends ConsumerStatefulWidget {
  const MyCourseOfflineScreen({super.key});

  @override
  ConsumerState<MyCourseOfflineScreen> createState() =>
      _MyCourseOfflineScreenState();
}

class _MyCourseOfflineScreenState extends ConsumerState<MyCourseOfflineScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    Future.delayed(
      const Duration(milliseconds: 1500),
      () {
        if (mounted) {
          ref.read(myCourseOfflineProvider.notifier).refresh();
        }
      },
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        centerTitle: false,
        title: Text(
          'Your downloaded courses',
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        backgroundColor: Theme.of(context).colorScheme.surface,
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: Dimensions.paddingSizeLarge),
            child: Icon(
              Icons.cloud_off_rounded,
              size: Dimensions.iconSizeExtraLarge,
            ),
          ),
        ],
      ),
      body: StreamBuilder<bool>(
        stream: NetworkService().networkStatusStream,
        builder: (context, snapshot) {
          if (snapshot.hasData && snapshot.data == true) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              final currentRoute = GoRouterState.of(context).uri.path;
              if (!currentRoute.contains(AppRoutes.main)) {
                customSnackBar('Back online', isError: false);
                context.go(AppRoutes.getMainRoute(AppRoutes.splash));
              }
            });
            return Center(
              child: Text(tl(context).connected_to_internet),
            );
          } else {
            return ref.watch(myCourseOfflineProvider).when(
                  loading: () => const LoadingIndicator(),
                  error: (e, _) => Center(child: Text(e.toString())),
                  data: (courses) {
                    if (courses.isEmpty) {
                      return _EmptyCoursesWidget();
                    }
                    return _CourseListWidget(
                      scrollController: _scrollController,
                      courses: courses,
                    );
                  },
                );
          }
        },
      ),
    );
  }
}

class _EmptyCoursesWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Image.asset(Images.emptyCourse, scale: 3),
          const Text(
            "You don't have any downloaded course.",
            style: robotoRegular,
          ),
        ],
      ),
    );
  }
}

class _CourseListWidget extends StatelessWidget {
  const _CourseListWidget({
    required this.scrollController,
    required this.courses,
  });

  final ScrollController scrollController;
  final List courses;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeSmall,
      ),
      child: CustomScrollView(
        controller: scrollController,
        slivers: [
          SliverList(
            delegate: SliverChildBuilderDelegate(
              (context, index) => MyCourseOfflineWidgetItem(
                course: courses[index],
              ),
              childCount: courses.length,
            ),
          ),
        ],
      ),
    );
  }
}
