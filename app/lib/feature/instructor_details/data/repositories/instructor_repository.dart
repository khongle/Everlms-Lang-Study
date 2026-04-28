import 'package:everlms/core/api/api_client.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_course_list.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_profile.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_student_list.dart';
import 'package:everlms/feature/instructor_details/data/models/student.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';

class InstructorRepository {
  const InstructorRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<InstructorProfile?> getInstructorProfile(int id) async {
    final response =
        await _apiClient.getData('${AppConstants.instructorProfile}/$id');
    if (response.statusCode == 200 && response.body['success'] == true) {
      return InstructorProfile.fromJson(response.body);
    }
    return null;
  }

  Future<List<Course>> getCourseListOfInstructors({
    required int id,
    required int page,
  }) async {
    final response = await _apiClient
        .getData('${AppConstants.instructorCourseList}/$id?page=$page');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final list = InstructorCourseList.fromJson(response.body);
      return list.data?.courses ?? [];
    }
    return [];
  }

  Future<List<Student>> getStudentsList({
    required int id,
    required int page,
  }) async {
    final response = await _apiClient
        .getData('${AppConstants.instructorStudentList}/$id?page=$page');
    if (response.statusCode == 200 && response.body['success'] == true) {
      final list = InstructorStudentList.fromJson(response.body);
      return list.data?.students ?? [];
    }
    return [];
  }

  Future<bool> followUnfollow(int id) async {
    final response =
        await _apiClient.getData('${AppConstants.followUnfollow}/$id');
    return response.statusCode == 200 && response.body['success'] == true;
  }
}
