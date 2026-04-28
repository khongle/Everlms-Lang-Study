import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/home/data/models/explore.dart';

class ExploreRepository {
  const ExploreRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<Explore> getExploreData() async {
    // TODO: Wire currency from SplashProvider when explore_provider is connected
    const currency = '';
    final response =
        await _apiClient.getData('${AppConstants.explore}?currency=$currency');
    if (response.statusCode == 200 && response.body['success'] == true) {
      return Explore.fromJson(response.body);
    }
    return Explore();
  }
}
