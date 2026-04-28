import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/course_category/data/repositories/category_repository.dart';
import 'package:everlms/feature/root/data/models/common/category.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final categoryRepositoryProvider = Provider<CategoryRepository>((ref) {
  return CategoryRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CategoryState {
  const CategoryState({this.categoryList = const []});

  final List<Category> categoryList;

  CategoryState copyWith({List<Category>? categoryList}) {
    return CategoryState(
      categoryList: categoryList ?? this.categoryList,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class CategoryNotifier extends AsyncNotifier<CategoryState> {
  @override
  Future<CategoryState> build() async {
    final repo = ref.read(categoryRepositoryProvider);
    final list = await repo.getCategoryList();
    return CategoryState(categoryList: list);
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final repo = ref.read(categoryRepositoryProvider);
      final list = await repo.getCategoryList();
      return CategoryState(categoryList: list);
    });
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final categoryProvider = AsyncNotifierProvider<CategoryNotifier, CategoryState>(
  CategoryNotifier.new,
);
