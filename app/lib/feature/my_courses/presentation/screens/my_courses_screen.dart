import 'package:convert_vietnamese/convert_vietnamese.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/home/presentation/providers/home_provider.dart';
import 'package:everlms/feature/my_courses/presentation/widgets/my_courses_widget.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/presentation/screens/no_data_screen.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class MyCoursesScreen extends ConsumerStatefulWidget {
  const MyCoursesScreen({super.key});

  @override
  ConsumerState<MyCoursesScreen> createState() => _MyCoursesScreenState();
}

class _MyCoursesScreenState extends ConsumerState<MyCoursesScreen>
    with RouteAware {
  List<Course> listMyCourses = [];
  List<Course> listMyCoursesNotStart = [];
  Map<String, dynamic> mapCourses = {};
  SearchController searchController = SearchController();

  String? onTapCourseId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(homeProvider.notifier).loadMyCourses().then((_) {
        sortListCourseByCategory();
        setState(() {});
      });
    });
  }

  @override
  void didPopNext() {
    super.didPopNext();
    ref.read(homeProvider.notifier).loadMyCourses().then((_) {
      sortListCourseByCategory();
      setState(() {});
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void dispose() {
    super.dispose();
    routeObserver.unsubscribe(this);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: CustomAppBar(
        title: tl(context).my_courses,
        actions: [searchWidget()],
      ),
      body: Builder(
        builder: (context) {
          final homeState = ref.watch(homeProvider).valueOrNull;
          final isLoading = homeState?.isLoadingMyCourses ?? false;

          if (isLoading) {
            return const LoadingIndicator();
          }
          if (listMyCourses.isEmpty) {
            return NoDataScreen(
              text: tl(context).no_course_found,
              isShowHomePage: false,
              type: NoDataType.course,
            );
          }
          return SingleChildScrollView(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (listMyCoursesNotStart.isNotEmpty)
                  listCourseNotStartWidget(),
                const SizedBox(
                  height: Dimensions.paddingSizeDefault,
                ),
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: mapCourses.keys.map(categoryCoursesWidget).toList(),
                ),
                const SizedBox(
                  height: Dimensions.paddingSizeDefault,
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget listCourseNotStartWidget() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        Row(
          children: [
            SvgPicture.asset(Images.categoryOne),
            const SizedBox(
              width: Dimensions.paddingSizeSmall,
            ),
            Text(
              'Start learning new courses',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
          ],
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        ListView.builder(
          itemCount: listMyCoursesNotStart.length,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) {
            return CourseWidget(course: listMyCoursesNotStart[index]);
          },
        ),
      ],
    );
  }

  Future<void> sortListCourseByCategory() async {
    final homeState = ref.read(homeProvider).valueOrNull;
    final list = homeState?.myCourses ?? [];
    listMyCourses = list.where((course) => course.isStart ?? false).toList();
    listMyCoursesNotStart =
        list.where((course) => course.isStart == false).toList();
    final listCategoryCourse =
        listMyCourses.map((course) => course.fieldCategory ?? '').toList();
    mapCourses.clear();
    for (final category in listCategoryCourse) {
      mapCourses[category] =
          listMyCourses.where((course) => course.fieldCategory == category);
    }
  }

  Widget searchWidget() {
    return SearchAnchor(
      searchController: searchController,
      dividerColor:
          Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.06),
      viewHintText: 'Search...',
      viewTrailing: [
        IconButton(
          onPressed: () {
            searchController.clear();
          },
          icon: SvgPicture.asset(
            Images.search,
            colorFilter: ColorFilter.mode(
              Theme.of(context).colorScheme.onSurface,
              BlendMode.srcIn,
            ),
          ),
        ),
      ],
      builder: (context, controller) => IconButton(
        onPressed: () => searchController.openView(),
        icon: SvgPicture.asset(
          Images.search,
          colorFilter: ColorFilter.mode(
            Theme.of(context).colorScheme.onSurface,
            BlendMode.srcIn,
          ),
        ),
      ),
      suggestionsBuilder: (context, searchController) {
        return listMyCourses
            .where(
              (e) =>
                  removeDiacritics(e.title?.toLowerCase() ?? '')
                      .contains(searchController.text.toLowerCase()) ||
                  (e.title?.toLowerCase() ?? '')
                      .contains(searchController.text.toLowerCase()),
            )
            .map(
              (course) => Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: Dimensions.paddingSizeDefault,
                ),
                child: InkWell(
                  onTap: () {
                    searchController.clear();
                    FocusScope.of(context).unfocus();
                    searchController.closeView(searchController.text);
                    context.push(
                      AppRoutes.getCourseDetailsScreenRoute(),
                      extra: {'course': course},
                    );
                  },
                  child: searchCourseItem(course, context),
                ),
              ),
            );
      },
    );
  }

  ListTile searchCourseItem(Course e, BuildContext context) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(
        vertical: Dimensions.paddingSizeExtraSmall,
      ),
      leading: CustomImage(
        image: e.fieldImage,
        height: 100,
        width: 100,
      ),
      isThreeLine: false,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            e.fieldCategory ?? '',
            style: Theme.of(context)
                .textTheme
                .labelSmall!
                .copyWith(color: Theme.of(context).primaryColor),
          ),
          Text(
            e.title ?? '',
            style: Theme.of(context).textTheme.titleSmall,
          ),
          Row(
            spacing: Dimensions.paddingSizeDefault,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Flexible(
                child: LinearProgressIndicator(
                  value: e.progress,
                  backgroundColor: Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: 0.06),
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                ),
              ),
              Text(
                '${((e.progress ?? 0) * 100).toInt()}%',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget categoryCoursesWidget(String category) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          category,
          style: Theme.of(context).textTheme.headlineSmall,
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        GridView.count(
          padding: EdgeInsets.zero,
          childAspectRatio: 0.9,
          crossAxisSpacing: 20,
          crossAxisCount: 2,
          primary: false,
          shrinkWrap: true,
          children: List<Widget>.from(
            mapCourses[category].map(
              (Course? course) => InkWell(
                onTap: () async {
                  onTapCourseId =
                      course?.id != null ? (course?.id).toString() : null;
                  course?.isStart ?? false
                      ? await context.push(
                          AppRoutes.getCourseDetailsScreenRoute(),
                          extra: {'course': course},
                        )
                      : context.push(
                          AppRoutes.getCourseDetailsNotPurchaseScreenRoute(),
                          extra: course,
                        );
                },
                child: Builder(
                  builder: (context) {
                    return MyCoursesWidget(course: course);
                  },
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
