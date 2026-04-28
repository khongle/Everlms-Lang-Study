import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/latest_course_list.dart';

class WishListRepository {
  const WishListRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<List<Course>> getWishList(int page) async {
    final response = await _apiClient
        .getData('${AppConstants.myWishList}?page=$page&&type=course');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final list = LatestCourseList.fromJson(response.body);
      return list.data?.courses ?? [];
    }
    return [];
  }

  Future<String?> addOrRemoveWishList(int id, String type) async {
    final response = await _apiClient.postData(
      AppConstants.addOrRemoveWishList,
      {'id': '$id', 'type': type},
    );
    if (response.statusCode == 200) {
      return response.body['message'] as String?;
    }
    return null;
  }
}
