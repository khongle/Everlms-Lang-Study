import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/profile/data/models/user_model/user_data.dart';
import 'package:everlms/feature/profile/data/repositories/user_repository.dart';
import 'package:everlms/feature/root/data/models/common/instructor.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final userRepositoryProvider = Provider<UserRepository>((ref) {
  return UserRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class UserProfileState {
  const UserProfileState({
    this.userData,
    this.isLoading = false,
    this.selectedGender,
    this.selectedDate,
  });

  final UserData? userData;
  final bool isLoading;
  final String? selectedGender;
  final String? selectedDate;

  UserProfileState copyWith({
    UserData? userData,
    bool? isLoading,
    String? selectedGender,
    String? selectedDate,
  }) {
    return UserProfileState(
      userData: userData ?? this.userData,
      isLoading: isLoading ?? this.isLoading,
      selectedGender: selectedGender ?? this.selectedGender,
      selectedDate: selectedDate ?? this.selectedDate,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class UserProfileNotifier extends AsyncNotifier<UserProfileState> {
  @override
  Future<UserProfileState> build() async {
    return const UserProfileState();
  }

  UserRepository get _repo => ref.read(userRepositoryProvider);

  Future<void> getUserProfile() async {
    final current = state.valueOrNull ?? const UserProfileState();
    state = AsyncData(current.copyWith(isLoading: true));

    final uid = _repo.getUserUid();
    if (uid.isEmpty) {
      state = AsyncData(current.copyWith(isLoading: false));
      return;
    }

    final response = await _repo.getUser(uid);
    UserData? userData;
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      if (data is List && data.isNotEmpty) {
        userData = UserData.fromJson(data[0] as Map<String, dynamic>);
      } else if (data is Map<String, dynamic>) {
        userData = UserData.fromJson(data);
      }
    }

    state = AsyncData(current.copyWith(
      userData: userData,
      isLoading: false,
      selectedGender: userData?.gender,
      selectedDate: userData?.dateOfBirth,
    ),);
  }

  Future<bool> editProfile({
    required String username,
    required String firstName,
    required String lastName,
    String? phone,
    String? gender,
    String? dateOfBirth,
    String? address,
    String? fullName,
  }) async {
    final uid = _repo.getUserUid();
    if (uid.isEmpty) return false;

    final response = await _repo.updateProfile(
      uid: uid,
      username: username,
      firstName: firstName,
      lastName: lastName,
      phone: phone,
      gender: gender,
      dateOfBirth: dateOfBirth,
      address: address,
      fullName: fullName,
    );

    if (response != null && response.statusCode == 200) {
      await getUserProfile();
      return true;
    }
    return false;
  }

  void changeGender(String gender) {
    final current = state.valueOrNull ?? const UserProfileState();
    state = AsyncData(current.copyWith(selectedGender: gender));
  }

  void changeDate(String date) {
    final current = state.valueOrNull ?? const UserProfileState();
    state = AsyncData(current.copyWith(selectedDate: date));
  }

  Future<bool> removeUser() async {
    final response = await _repo.deleteUser();
    return response != null && response.statusCode == 200;
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final userProfileProvider =
    AsyncNotifierProvider<UserProfileNotifier, UserProfileState>(
  UserProfileNotifier.new,
);

// ---------------------------------------------------------------------------
// Instructor by teacher ID (fetches a basic Instructor from user profile)
// ---------------------------------------------------------------------------
final FutureProviderFamily<Instructor?, String> instructorByTeacherIdProvider =
    FutureProvider.family<Instructor?, String>((ref, teacherId) async {
  if (teacherId.isEmpty) return null;
  final repo = ref.read(userRepositoryProvider);
  final response = await repo.getUser(teacherId);
  if (response != null && response.statusCode == 200) {
    final dataList = response.body['data'];
    if (dataList is! List || dataList.isEmpty) return null;
    final instructor =
        Instructor.fromJson(dataList[0] as Map<String, dynamic>);
    if ((instructor.name ?? '').isEmpty &&
        (instructor.expertise ?? '').isEmpty &&
        (instructor.biography ?? '').isEmpty) {
      return null;
    }
    return instructor;
  }
  return null;
});
