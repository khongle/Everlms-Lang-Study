import 'dart:async';

import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/course_catalog/presentation/providers/course_list_provider.dart';
import 'package:everlms/feature/course_catalog/presentation/widgets/course_card.dart';
import 'package:everlms/feature/course_catalog/presentation/widgets/empty_state_widget.dart';
import 'package:everlms/feature/course_catalog/presentation/widgets/filter_chips_row.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

class CourseCatalogScreen extends ConsumerStatefulWidget {
  const CourseCatalogScreen({super.key});

  @override
  ConsumerState<CourseCatalogScreen> createState() =>
      _CourseCatalogScreenState();
}

class _CourseCatalogScreenState extends ConsumerState<CourseCatalogScreen> {
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();
  Timer? _debounce;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _onScroll() {
    if (!_scrollController.hasClients) return;
    final maxScroll = _scrollController.position.maxScrollExtent;
    final current = _scrollController.offset;
    if (current >= maxScroll * 0.7) {
      ref.read(courseListProvider.notifier).loadMore();
    }
  }

  void _onSearchChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      ref.read(courseListProvider.notifier).updateSearch(value);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(courseListProvider);
    final categoriesAsync = ref.watch(categoriesQueryProvider);

    return Scaffold(
      backgroundColor: AppColors.bgPrimary,
      appBar: AppBar(
        backgroundColor: AppColors.bgSecondary,
        elevation: 0,
        title: const Text(
          'Courses',
          style: TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize: 18,
          ),
        ),
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(courseListProvider.notifier).refresh(),
        color: AppColors.accentPrimary,
        backgroundColor: AppColors.bgSecondary,
        child: CustomScrollView(
          controller: _scrollController,
          slivers: [
            // Search bar
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                child: Container(
                  decoration: BoxDecoration(
                    color: AppColors.bgTertiary,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: TextField(
                    controller: _searchController,
                    onChanged: _onSearchChanged,
                    style: const TextStyle(
                      color: AppColors.textPrimary,
                      fontSize: 14,
                    ),
                    decoration: const InputDecoration(
                      hintText: 'Search courses…',
                      hintStyle:
                          TextStyle(color: AppColors.textMuted, fontSize: 14),
                      prefixIcon:
                          Icon(Icons.search, color: AppColors.textMuted),
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(vertical: 12),
                    ),
                  ),
                ),
              ),
            ),
            // Filter chips
            SliverToBoxAdapter(
              child: categoriesAsync.when(
                data: (cats) => FilterChipsRow(
                  categories: cats,
                  filter: state.filter,
                  onFilterChanged: (f) =>
                      ref.read(courseListProvider.notifier).updateFilter(f),
                ),
                loading: () => const SizedBox(height: 36),
                error: (_, __) => const SizedBox(height: 36),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 12)),
            // Results count
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  '${state.total} courses found',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 8)),
            // Grid or empty/loading
            if (state.isInitialLoading)
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: _ShimmerGrid(),
              )
            else if (state.items.isEmpty)
              SliverFillRemaining(
                child: EmptyStateWidget(
                  message: 'No courses found.\nTry adjusting your filters.',
                  onReset: () =>
                      ref.read(courseListProvider.notifier).resetFilters(),
                  resetLabel: 'Reset filters',
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 0.62,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final course = state.items[index];
                      return CourseCard(
                        course: course,
                        onTap: () => context.push('/courses/${course.id}'),
                      );
                    },
                    childCount: state.items.length,
                  ),
                ),
              ),
            // Load more indicator
            if (state.isLoadingMore)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: Center(
                    child: CircularProgressIndicator(
                      color: AppColors.accentPrimary,
                      strokeWidth: 2,
                    ),
                  ),
                ),
              ),
            const SliverToBoxAdapter(child: SizedBox(height: 24)),
          ],
        ),
      ),
    );
  }
}

/// 4-card shimmer placeholder grid.
class _ShimmerGrid extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SliverGrid(
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 0.62,
      ),
      delegate: SliverChildBuilderDelegate(
        (_, __) => Shimmer.fromColors(
          baseColor: AppColors.bgTertiary,
          highlightColor: AppColors.bgSecondary,
          child: Container(
            decoration: BoxDecoration(
              color: AppColors.bgTertiary,
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        childCount: 4,
      ),
    );
  }
}
