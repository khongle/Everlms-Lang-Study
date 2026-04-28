import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/dashboard/presentation/providers/dashboard_query_provider.dart';
import 'package:everlms/feature/dashboard/presentation/providers/notification_badge_provider.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/continue_learning_card.dart';
import 'package:everlms/feature/dashboard/presentation/widgets/featured_courses_section.dart';
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

    final profileAsync = ref.watch(userProfileProvider);
    final userName =
        profileAsync.valueOrNull?.userData?.fullName?.split(' ').first ??
            'there';

    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: _buildAppBar(context, badgeAsync),
      body: RefreshIndicator(
        color: AppColors.accentPrimary,
        backgroundColor: AppColors.bgSecondary,
        onRefresh: () async {
          ref.invalidate(dashboardQueryProvider);
          ref.invalidate(featuredCoursesQueryProvider);
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
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
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
                          color: AppColors.bgSecondary,
                          border: Border.all(color: AppColors.border),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Center(
                          child: Column(
                            children: [
                              Icon(
                                Icons.school_outlined,
                                color: AppColors.textMuted,
                                size: 40,
                              ),
                              SizedBox(height: 8),
                              Text(
                                'No courses in progress',
                                style: TextStyle(
                                  color: AppColors.textSecondary,
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                }
                return SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      if (index == 0) {
                        return const _SectionHeader(title: 'Continue Learning');
                      }
                      final course = dashboard.inProgressCourses[index - 1];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: ContinueLearningCard(
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
                        ),
                      );
                    },
                    childCount: dashboard.inProgressCourses.length + 1,
                  ),
                );
              },
            ),

            // Stats strip
            dashboardAsync.when(
              loading: () => SliverToBoxAdapter(
                child: _buildStatsShimmer(),
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

    return AppBar(
      backgroundColor: AppColors.bgSecondary,
      elevation: 0,
      title: const Text(
        'EverLMS',
        style: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w700,
          color: AppColors.textPrimary,
        ),
      ),
      actions: [
        IconButton(
          onPressed: () => context.push(AppRoutes.notificationScreen),
          icon: Stack(
            clipBehavior: Clip.none,
            children: [
              const Icon(
                Icons.notifications_outlined,
                color: AppColors.textSecondary,
                size: 24,
              ),
              if (unreadCount > 0)
                Positioned(
                  top: -4,
                  right: -4,
                  child: Container(
                    width: 16,
                    height: 16,
                    decoration: const BoxDecoration(
                      color: AppColors.accentPrimary,
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

  Widget _buildStatsShimmer() => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Row(
          children: List.generate(
            3,
            (_) => Expanded(
              child: Container(
                height: 80,
                margin: const EdgeInsets.symmetric(horizontal: 4),
                decoration: BoxDecoration(
                  color: AppColors.bgSecondary,
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
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: AppColors.textPrimary,
        ),
      ),
    );
  }
}
