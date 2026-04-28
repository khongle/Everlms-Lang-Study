import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/onboarding/data/models/on_board_model.dart';

class OnBoardRepository {
  const OnBoardRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<OnBoardData>> getOnBoardList() async {
    final response = await _apiClient.getData(AppConstants.onBoards);
    if (response.statusCode == 200 && response.body['success'] == true) {
      final model = OnBoardModel.fromJson(response.body);
      return model.data?.onBoards ?? [];
    }
    return [];
  }
}
