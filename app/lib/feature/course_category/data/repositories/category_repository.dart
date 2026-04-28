import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/root/data/models/common/category.dart';

class CategoryRepository {
  const CategoryRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<Category>> getCategoryList() async {
    final response = await _apiClient.getData(AppConstants.courseCategories);
    if (response.statusCode == 200 && response.body != null) {
      final categories = response.body['data']['categories'] as List?;
      if (categories != null) {
        return categories
            .map((e) => Category.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }
}
