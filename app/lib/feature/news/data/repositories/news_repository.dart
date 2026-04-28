import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/news/data/models/news.dart';

class NewsRepository {
  const NewsRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<News>> getNews() async {
    final response = await _apiClient.getData(AppConstants.getNews);
    if (response.statusCode == 200) {
      final data = response.body['data'];
      if (data != null && data is Map<String, dynamic>) {
        return data.values
            .map((e) => News.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }
}
