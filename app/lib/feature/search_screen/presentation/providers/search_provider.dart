import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/search_screen/data/repositories/search_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final searchRepositoryProvider = Provider<SearchRepository>((ref) {
  return SearchRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class SearchState {
  const SearchState({
    this.searchResults,
    this.historyList = const [],
    this.searchText = '',
    this.isSearching = false,
  });

  final List<Course>? searchResults;
  final List<String> historyList;
  final String searchText;
  final bool isSearching;

  SearchState copyWith({
    List<Course>? searchResults,
    List<String>? historyList,
    String? searchText,
    bool? isSearching,
  }) {
    return SearchState(
      searchResults: searchResults ?? this.searchResults,
      historyList: historyList ?? this.historyList,
      searchText: searchText ?? this.searchText,
      isSearching: isSearching ?? this.isSearching,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class SearchNotifier extends AsyncNotifier<SearchState> {
  @override
  Future<SearchState> build() async {
    final repo = ref.read(searchRepositoryProvider);
    final history = repo.getSearchHistory();
    return SearchState(historyList: history);
  }

  Future<void> searchData(String query, {bool updateHistory = false}) async {
    final current = state.valueOrNull ?? const SearchState();
    final trimmedQuery = query.trim();

    if (trimmedQuery.isEmpty) {
      state = AsyncData(current.copyWith(
        searchResults: [],
        searchText: '',
        isSearching: false,
      ),);
      return;
    }

    state = AsyncData(current.copyWith(isSearching: true));

    final repo = ref.read(searchRepositoryProvider);

    if (updateHistory) {
      final updatedHistory = List<String>.from(current.historyList);
      if (!updatedHistory.contains(trimmedQuery)) {
        updatedHistory.insert(0, trimmedQuery);
      }
      await repo.saveSearchHistory(updatedHistory);
      state = AsyncData(current.copyWith(
        historyList: updatedHistory,
        searchText: trimmedQuery,
      ),);
    }

    final results = await repo.searchCourses(trimmedQuery);
    final updated = state.valueOrNull ?? current;
    state = AsyncData(updated.copyWith(
      searchResults: results,
      searchText: trimmedQuery,
      isSearching: false,
    ),);
  }

  void removeHistory(String item) {
    final current = state.valueOrNull;
    if (current == null) return;
    final updatedHistory = List<String>.from(current.historyList)..remove(item);
    ref.read(searchRepositoryProvider).saveSearchHistory(updatedHistory);
    state = AsyncData(current.copyWith(historyList: updatedHistory));
  }

  Future<void> clearSearchHistory() async {
    final current = state.valueOrNull;
    if (current == null) return;
    await ref.read(searchRepositoryProvider).clearSearchHistory();
    state = AsyncData(current.copyWith(historyList: []));
  }

  void clearResults() {
    final current = state.valueOrNull;
    if (current == null) return;
    state = AsyncData(current.copyWith(
      searchText: '',
      isSearching: false,
    ),);
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final searchProvider =
    AsyncNotifierProvider<SearchNotifier, SearchState>(SearchNotifier.new);
