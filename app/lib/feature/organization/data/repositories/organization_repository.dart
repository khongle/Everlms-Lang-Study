import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_course_list.dart';
import 'package:everlms/feature/organization/data/models/organization_instructor.dart';
import 'package:everlms/feature/organization/data/models/organization_profile.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/instructor_old.dart';

class OrganizationRepository {
  const OrganizationRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<OrganizationProfileData?> getOrganizationProfile(int id) async {
    final response =
        await _apiClient.getData('${AppConstants.organizationProfile}/$id');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final detail = OrganizationDetail.fromJson(response.body);
      return detail.data;
    }
    return null;
  }

  Future<List<Course>> getOrganizationCourseList(int id, int page) async {
    final response = await _apiClient
        .getData('${AppConstants.organizationCourseList}/$id?page=$page');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final list = InstructorCourseList.fromJson(response.body);
      return list.data?.courses ?? [];
    }
    return [];
  }

  Future<List<InstructorOld>> getOrganizationInstructorList(
    int id,
    int page,
  ) async {
    final response = await _apiClient
        .getData('${AppConstants.organizationInstructorList}/$id?page=$page');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final list = OrganizationInstructor.fromJson(response.body);
      return list.data?.instructors ?? [];
    }
    return [];
  }
}
