import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/faq.dart';

class FaqRepository {
  const FaqRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<Faq>> getFaqList() async {
    final response = await _apiClient.getData(AppConstants.getFaq);
    if (response.statusCode == 200) {
      final data = response.body['data'];
      if (data is Map<String, dynamic>) {
        return data.values
            .map((e) => Faq.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    }
    return [];
  }
}
