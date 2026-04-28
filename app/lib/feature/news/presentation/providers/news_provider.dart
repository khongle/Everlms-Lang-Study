import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/news/data/models/news.dart';
import 'package:everlms/feature/news/data/repositories/news_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final newsRepositoryProvider = Provider<NewsRepository>((ref) {
  return NewsRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class NewsState {
  const NewsState({
    this.newsList = const [],
    this.newsByTags = const [],
  });

  final List<News> newsList;
  final List<News> newsByTags;

  NewsState copyWith({
    List<News>? newsList,
    List<News>? newsByTags,
  }) {
    return NewsState(
      newsList: newsList ?? this.newsList,
      newsByTags: newsByTags ?? this.newsByTags,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class NewsNotifier extends AsyncNotifier<NewsState> {
  @override
  Future<NewsState> build() async {
    final repo = ref.read(newsRepositoryProvider);
    final news = await repo.getNews();
    return NewsState(newsList: news);
  }

  void filterByTag(String tag) {
    final current = state.valueOrNull;
    if (current == null) return;
    final filtered = current.newsList.where((n) => n.tag == tag).toList();
    state = AsyncData(current.copyWith(newsByTags: filtered));
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final newsProvider =
    AsyncNotifierProvider<NewsNotifier, NewsState>(NewsNotifier.new);
