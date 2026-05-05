import 'dart:async';

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

    final theme = Theme.of(context);
    final cs = theme.colorScheme;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: cs.surface,
        elevation: 0,
        title: Text(
          'Courses',
          style: theme.textTheme.titleLarge,
        ),
        iconTheme: IconThemeData(color: theme.iconTheme.color),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(courseListProvider.notifier).refresh(),
        color: cs.primary,
        backgroundColor: cs.surface,
        child: CustomScrollView(
          controller: _scrollController,
          slivers: [
            // Search bar
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                child: Container(
                  decoration: BoxDecoration(
                    color: cs.surfaceContainerHighest,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: cs.outline),
                  ),
                  child: TextField(
                    controller: _searchController,
                    onChanged: _onSearchChanged,
                    style: TextStyle(
                      color: cs.onSurface,
                      fontSize: 14,
                    ),
                    decoration: InputDecoration(
                      hintText: 'Search courses…',
                      hintStyle:
                          TextStyle(color: theme.hintColor, fontSize: 14),
                      prefixIcon: Icon(Icons.search, color: theme.hintColor),
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
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
                  style: theme.textTheme.labelSmall,
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
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  child: Center(
                    child: CircularProgressIndicator(
                      color: cs.primary,
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
    final cs = Theme.of(context).colorScheme;
    return SliverGrid(
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 0.62,
      ),
      delegate: SliverChildBuilderDelegate(
        (_, __) => Shimmer.fromColors(
          baseColor: cs.surfaceContainerHighest,
          highlightColor: cs.surface,
          child: Container(
            decoration: BoxDecoration(
              color: cs.surfaceContainerHighest,
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        childCount: 4,
      ),
    );
  }
}
