import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/membership/data/models/membership_model.dart';

class MembershipRepository {
  const MembershipRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<MembershipModel>> getMembershipList() async {
    final response = await _apiClient.getData(AppConstants.getMembership);
    if (response.statusCode == 200 && response.body != null) {
      if (response.body is List) {
        return (response.body as List)
            .map((e) => MembershipModel.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }
}
