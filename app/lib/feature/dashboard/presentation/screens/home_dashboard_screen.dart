import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/feature/course_catalog/presentation/providers/course_list_provider.dart';
import 'package:everlms/feature/dashboard/presentation/providers/dashboard_query_provider.dart';
import 'package:everlms/feature/dashboard/presentation/providers/notification_badge_provider.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/continue_learning_card.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/featured_courses_section.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/popular_categories_section.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/stats_row_widget.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Home Dashboard screen (Feature 02).
///
/// Shows a personalized summary: in-progress courses, stats, featured courses,
/// and a notification badge on the app bar bell.
class HomeDashboardScreen extends ConsumerWidget {
  const HomeDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboardAsync = ref.watch(dashboardQueryProvider);
    final featuredAsync = ref.watch(featuredCoursesQueryProvider);
    final badgeAsync = ref.watch(notificationBadgeProvider);
    final categoriesAsync = ref.watch(categoriesQueryProvider);

    final profileAsync = ref.watch(userProfileProvider);
    final userName =
        profileAsync.valueOrNull?.userData?.firstDisplayName.isNotEmpty == true
            ? profileAsync.valueOrNull!.userData!.firstDisplayName
            : 'there';

    final theme = Theme.of(context);
    final cs = theme.colorScheme;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: _buildAppBar(context, badgeAsync),
      body: RefreshIndicator(
        color: cs.primary,
        backgroundColor: cs.surface,
        onRefresh: () async {
          ref.invalidate(dashboardQueryProvider);
          ref.invalidate(featuredCoursesQueryProvider);
          ref.invalidate(categoriesQueryProvider);
          ref.invalidate(notificationBadgeProvider);
        },
        child: CustomScrollView(
          slivers: [
            // Greeting
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
                child: Text(
                  'Welcome back, $userName! 👋',
                  style: theme.textTheme.headlineSmall,
                ),
              ),
            ),

            // Continue Learning section
            dashboardAsync.when(
              loading: () => const SliverToBoxAdapter(
                child: _SectionHeader(title: 'Continue Learning'),
              ),
              error: (e, _) => const SliverToBoxAdapter(child: SizedBox()),
              data: (dashboard) {
                if (dashboard.inProgressCourses.isEmpty) {
                  return SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: cs.surface,
                          border: Border.all(color: cs.outline),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(
                                Icons.school_outlined,
                                color: theme.hintColor,
                                size: 40,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'No courses in progress',
                                style: theme.textTheme.bodySmall,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                }
                return SliverToBoxAdapter(
                  child: Column(
                    children: [
                      const _SectionHeader(title: 'Continue Learning'),
                      SizedBox(
                        height: 132,
                        child: PageView.builder(
                          padEnds: false,
                          controller: PageController(viewportFraction: 0.96),
                          itemCount: dashboard.inProgressCourses.length,
                          itemBuilder: (context, index) {
                            final course = dashboard.inProgressCourses[index];
                            return ContinueLearningCard(
                              course: course,
                              onCourseTap: () => context.push(
                                '/courses/${course.id}',
                              ),
                              onContinue: () {
                                if (course.lastLessonId != null) {
                                  context.push(
                                    '/courses/${course.id}/lessons/${course.lastLessonId}',
                                  );
                                } else {
                                  context.push('/courses/${course.id}');
                                }
                              },
                            );
                          },
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),

            // Stats strip
            dashboardAsync.when(
              loading: () => SliverToBoxAdapter(
                child: _buildStatsShimmer(context),
              ),
              error: (_, __) => const SliverToBoxAdapter(child: SizedBox()),
              data: (dashboard) => SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: StatsRowWidget(
                    enrolledCount: dashboard.enrolledCoursesCount,
                    completedCount: dashboard.completedCoursesCount,
                    certificatesCount: dashboard.certificatesCount,
                  ),
                ),
              ),
            ),

            // Popular Categories
            const SliverToBoxAdapter(
              child: _SectionHeader(title: 'Popular Categories'),
            ),
            categoriesAsync.when(
              loading: () => const SliverToBoxAdapter(
                child: SizedBox(height: 156),
              ),
              error: (_, __) => const SliverToBoxAdapter(child: SizedBox()),
              data: (categories) => SliverToBoxAdapter(
                child: PopularCategoriesSection(
                  categories: categories,
                  onCategoryTap: (category) {
                    ref.read(courseListProvider.notifier).updateFilter(
                          const CourseFilterParams().copyWith(
                            categoryIds: [category.id],
                          ),
                        );
                    context.push(AppRoutes.courseCatalog);
                  },
                ),
              ),
            ),

            // Featured Courses
            const SliverToBoxAdapter(
              child: _SectionHeader(title: 'Featured Courses'),
            ),
            featuredAsync.when(
              loading: () => const SliverToBoxAdapter(
                child: SizedBox(height: 180),
              ),
              error: (_, __) => const SliverToBoxAdapter(child: SizedBox()),
              data: (courses) => SliverToBoxAdapter(
                child: FeaturedCoursesSection(
                  courses: courses,
                  onCourseTap: (id) => context.push('/courses/$id'),
                ),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 24)),
          ],
        ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar(
    BuildContext context,
    AsyncValue<int> badgeAsync,
  ) {
    final unreadCount = badgeAsync.valueOrNull ?? 0;

    final theme = Theme.of(context);
    final cs = theme.colorScheme;

    return AppBar(
      backgroundColor: cs.surface,
      elevation: 0,
      title: Text(
        'EverLMS',
        style: theme.textTheme.titleLarge,
      ),
      actions: [
        IconButton(
          onPressed: () => context.push(AppRoutes.notificationScreen),
          icon: Stack(
            clipBehavior: Clip.none,
            children: [
              Icon(
                Icons.notifications_outlined,
                color: theme.iconTheme.color,
                size: 24,
              ),
              if (unreadCount > 0)
                Positioned(
                  top: -4,
                  right: -4,
                  child: Container(
                    width: 16,
                    height: 16,
                    decoration: BoxDecoration(
                      color: cs.primary,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        unreadCount > 9 ? '9+' : '$unreadCount',
                        style: const TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
        const SizedBox(width: 8),
      ],
    );
  }

  Widget _buildStatsShimmer(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Row(
          children: List.generate(
            3,
            (_) => Expanded(
              child: Container(
                height: 80,
                margin: const EdgeInsets.symmetric(horizontal: 4),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ),
      );
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleLarge,
      ),
    );
  }
}
