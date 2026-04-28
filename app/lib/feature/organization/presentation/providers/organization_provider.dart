import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/organization/data/models/organization_profile.dart';
import 'package:everlms/feature/organization/data/repositories/organization_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/instructor_old.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final organizationRepositoryProvider = Provider<OrganizationRepository>((ref) {
  return OrganizationRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class OrganizationState {
  const OrganizationState({
    this.profile,
    this.courseList = const [],
    this.instructorList = const [],
    this.coursePageNumber = 1,
    this.instructorPageNumber = 1,
    this.isCourseLoading = false,
    this.isInstructorLoading = false,
    this.hasMoreCourses = true,
    this.hasMoreInstructors = true,
  });

  final OrganizationProfileData? profile;
  final List<Course> courseList;
  final List<InstructorOld> instructorList;
  final int coursePageNumber;
  final int instructorPageNumber;
  final bool isCourseLoading;
  final bool isInstructorLoading;
  final bool hasMoreCourses;
  final bool hasMoreInstructors;

  OrganizationState copyWith({
    OrganizationProfileData? profile,
    List<Course>? courseList,
    List<InstructorOld>? instructorList,
    int? coursePageNumber,
    int? instructorPageNumber,
    bool? isCourseLoading,
    bool? isInstructorLoading,
    bool? hasMoreCourses,
    bool? hasMoreInstructors,
  }) {
    return OrganizationState(
      profile: profile ?? this.profile,
      courseList: courseList ?? this.courseList,
      instructorList: instructorList ?? this.instructorList,
      coursePageNumber: coursePageNumber ?? this.coursePageNumber,
      instructorPageNumber: instructorPageNumber ?? this.instructorPageNumber,
      isCourseLoading: isCourseLoading ?? this.isCourseLoading,
      isInstructorLoading: isInstructorLoading ?? this.isInstructorLoading,
      hasMoreCourses: hasMoreCourses ?? this.hasMoreCourses,
      hasMoreInstructors: hasMoreInstructors ?? this.hasMoreInstructors,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier (family: keyed by orgId)
// ---------------------------------------------------------------------------
class OrganizationNotifier extends FamilyAsyncNotifier<OrganizationState, int> {
  @override
  Future<OrganizationState> build(int arg) async {
    final repo = ref.read(organizationRepositoryProvider);
    final profile = await repo.getOrganizationProfile(arg);
    final courses = await repo.getOrganizationCourseList(arg, 1);
    final instructors = await repo.getOrganizationInstructorList(arg, 1);
    return OrganizationState(
      profile: profile,
      courseList: courses,
      instructorList: instructors,
      coursePageNumber: courses.isNotEmpty ? 2 : 1,
      instructorPageNumber: instructors.isNotEmpty ? 2 : 1,
      hasMoreCourses: courses.isNotEmpty,
      hasMoreInstructors: instructors.isNotEmpty,
    );
  }

  Future<void> loadMoreCourses() async {
    final current = state.valueOrNull;
    if (current == null || current.isCourseLoading || !current.hasMoreCourses) {
      return;
    }
    state = AsyncData(current.copyWith(isCourseLoading: true));
    final repo = ref.read(organizationRepositoryProvider);
    final newCourses =
        await repo.getOrganizationCourseList(arg, current.coursePageNumber);
    state = AsyncData(
      current.copyWith(
        courseList: [...current.courseList, ...newCourses],
        coursePageNumber: newCourses.isNotEmpty
            ? current.coursePageNumber + 1
            : current.coursePageNumber,
        hasMoreCourses: newCourses.isNotEmpty,
        isCourseLoading: false,
      ),
    );
  }

  Future<void> loadMoreInstructors() async {
    final current = state.valueOrNull;
    if (current == null ||
        current.isInstructorLoading ||
        !current.hasMoreInstructors) {
      return;
    }
    state = AsyncData(current.copyWith(isInstructorLoading: true));
    final repo = ref.read(organizationRepositoryProvider);
    final newInstructors = await repo.getOrganizationInstructorList(
      arg,
      current.instructorPageNumber,
    );
    state = AsyncData(
      current.copyWith(
        instructorList: [...current.instructorList, ...newInstructors],
        instructorPageNumber: newInstructors.isNotEmpty
            ? current.instructorPageNumber + 1
            : current.instructorPageNumber,
        hasMoreInstructors: newInstructors.isNotEmpty,
        isInstructorLoading: false,
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final AsyncNotifierProviderFamily<OrganizationNotifier, OrganizationState, int>
    organizationProvider =
    AsyncNotifierProvider.family<OrganizationNotifier, OrganizationState, int>(
  OrganizationNotifier.new,
);
