import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/instructor_details/data/models/instructor_profile.dart';
import 'package:everlms/feature/instructor_details/data/models/student.dart';
import 'package:everlms/feature/instructor_details/data/repositories/instructor_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final instructorRepositoryProvider = Provider<InstructorRepository>((ref) {
  return InstructorRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class InstructorDetailState {
  const InstructorDetailState({
    this.profile,
    this.courseList = const [],
    this.studentList = const [],
    this.isFollowing = false,
    this.followingInProgress = false,
    this.coursePageNumber = 1,
    this.studentPageNumber = 1,
    this.isCourseLoading = false,
    this.isStudentLoading = false,
    this.hasMoreCourses = true,
    this.hasMoreStudents = true,
  });

  final InstructorProfile? profile;
  final List<Course> courseList;
  final List<Student> studentList;
  final bool isFollowing;
  final bool followingInProgress;
  final int coursePageNumber;
  final int studentPageNumber;
  final bool isCourseLoading;
  final bool isStudentLoading;
  final bool hasMoreCourses;
  final bool hasMoreStudents;

  InstructorDetailState copyWith({
    InstructorProfile? profile,
    List<Course>? courseList,
    List<Student>? studentList,
    bool? isFollowing,
    bool? followingInProgress,
    int? coursePageNumber,
    int? studentPageNumber,
    bool? isCourseLoading,
    bool? isStudentLoading,
    bool? hasMoreCourses,
    bool? hasMoreStudents,
  }) {
    return InstructorDetailState(
      profile: profile ?? this.profile,
      courseList: courseList ?? this.courseList,
      studentList: studentList ?? this.studentList,
      isFollowing: isFollowing ?? this.isFollowing,
      followingInProgress: followingInProgress ?? this.followingInProgress,
      coursePageNumber: coursePageNumber ?? this.coursePageNumber,
      studentPageNumber: studentPageNumber ?? this.studentPageNumber,
      isCourseLoading: isCourseLoading ?? this.isCourseLoading,
      isStudentLoading: isStudentLoading ?? this.isStudentLoading,
      hasMoreCourses: hasMoreCourses ?? this.hasMoreCourses,
      hasMoreStudents: hasMoreStudents ?? this.hasMoreStudents,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier (family: keyed by instructorId)
// ---------------------------------------------------------------------------
class InstructorDetailNotifier
    extends FamilyAsyncNotifier<InstructorDetailState, int> {
  @override
  Future<InstructorDetailState> build(int arg) async {
    final repo = ref.read(instructorRepositoryProvider);
    final profile = await repo.getInstructorProfile(arg);
    final courses = await repo.getCourseListOfInstructors(id: arg, page: 1);
    final students = await repo.getStudentsList(id: arg, page: 1);
    return InstructorDetailState(
      profile: profile,
      courseList: courses,
      studentList: students,
      isFollowing: profile?.data?.isFollowing ?? false,
      coursePageNumber: courses.isNotEmpty ? 2 : 1,
      studentPageNumber: students.isNotEmpty ? 2 : 1,
      hasMoreCourses: courses.isNotEmpty,
      hasMoreStudents: students.isNotEmpty,
    );
  }

  Future<void> loadMoreCourses() async {
    final current = state.valueOrNull;
    if (current == null || current.isCourseLoading || !current.hasMoreCourses) {
      return;
    }
    state = AsyncData(current.copyWith(isCourseLoading: true));
    final repo = ref.read(instructorRepositoryProvider);
    final newCourses = await repo.getCourseListOfInstructors(
      id: arg,
      page: current.coursePageNumber,
    );
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

  Future<void> loadMoreStudents() async {
    final current = state.valueOrNull;
    if (current == null ||
        current.isStudentLoading ||
        !current.hasMoreStudents) {
      return;
    }
    state = AsyncData(current.copyWith(isStudentLoading: true));
    final repo = ref.read(instructorRepositoryProvider);
    final newStudents =
        await repo.getStudentsList(id: arg, page: current.studentPageNumber);
    state = AsyncData(
      current.copyWith(
        studentList: [...current.studentList, ...newStudents],
        studentPageNumber: newStudents.isNotEmpty
            ? current.studentPageNumber + 1
            : current.studentPageNumber,
        hasMoreStudents: newStudents.isNotEmpty,
        isStudentLoading: false,
      ),
    );
  }

  Future<void> followUnfollow() async {
    final current = state.valueOrNull;
    if (current == null || current.followingInProgress) return;
    state = AsyncData(current.copyWith(followingInProgress: true));
    final repo = ref.read(instructorRepositoryProvider);
    final success = await repo.followUnfollow(arg);
    if (success) {
      state = AsyncData(
        current.copyWith(
          isFollowing: !current.isFollowing,
          followingInProgress: false,
        ),
      );
    } else {
      state = AsyncData(current.copyWith(followingInProgress: false));
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final AsyncNotifierProviderFamily<InstructorDetailNotifier,
        InstructorDetailState, int> instructorDetailProvider =
    AsyncNotifierProvider.family<InstructorDetailNotifier,
        InstructorDetailState, int>(
  InstructorDetailNotifier.new,
);
