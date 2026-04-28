import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_alert_dialog.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:intl/intl.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  static const _genderList = ['Male', 'Female'];

  final FocusNode _firstNameFocusNode = FocusNode();

  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _userNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _addressController = TextEditingController();

  bool _initialized = false;

  @override
  void dispose() {
    _firstNameFocusNode.dispose();
    _firstNameController.dispose();
    _lastNameController.dispose();
    _userNameController.dispose();
    _emailController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(userProfileProvider.notifier).getUserProfile(),
    );
  }

  void _initFromUserData() {
    final profileState = ref.read(userProfileProvider).valueOrNull;
    final userData = profileState?.userData;
    if (userData == null || _initialized) return;
    _initialized = true;
    _firstNameController.text = userData.firstName ?? '';
    _lastNameController.text = userData.lastName ?? '';
    _userNameController.text = userData.name ?? '';
    _emailController.text = userData.mail ?? '';
    _addressController.text = userData.address ?? '';
  }

  Future<void> _selectDate(BuildContext context) async {
    final profileState = ref.read(userProfileProvider).valueOrNull;
    final currentDate = profileState?.selectedDate;
    var initial = DateTime.now();
    if (currentDate != null && currentDate.isNotEmpty) {
      try {
        initial = DateFormat('yyyy-MM-dd').parse(currentDate);
      } on FormatException catch (_) {}
    }
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1900),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      ref
          .read(userProfileProvider.notifier)
          .changeDate(DateFormat('yyyy-MM-dd').format(picked));
    }
  }

  @override
  Widget build(BuildContext context) {
    final profileState = ref.watch(userProfileProvider).valueOrNull;
    final userData = profileState?.userData;
    final isLoading = profileState?.isLoading ?? false;

    _initFromUserData();

    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).update_profile,
      ),
      body: Stack(
        children: [
          if (userData == null)
            const LoadingIndicator()
          else
            _mainUI(context, profileState!),
          if (isLoading) const LoadingIndicator() else const SizedBox(),
        ],
      ),
    );
  }

  Widget _mainUI(BuildContext context, UserProfileState profileState) {
    final userData = profileState.userData;
    return Column(
      children: [
        Expanded(
          flex: 7,
          child: SingleChildScrollView(
            physics: const ClampingScrollPhysics(),
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeLarge,
                vertical: Dimensions.paddingSizeDefault,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Center(
                    child: Stack(
                      children: [
                        if (userData?.image != null)
                          Padding(
                            padding: const EdgeInsets.all(1),
                            child: ClipOval(
                              child: CustomImage(
                                image: userData?.image ?? '',
                                height: 75,
                                width: 75,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 30),
                  _userNameField(context),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _emailAddressField(context),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _firstNameField(context),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _lastNameField(context),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _genderField(context, profileState),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _dateOfBirthField(context, profileState),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  const SizedBox(height: Dimensions.paddingSizeLarge),
                ],
              ),
            ),
          ),
        ),
        if (!profileState.isLoading)
          CustomButton(
            onPressed: () {
              if (_firstNameController.text.isEmpty) {
                return customAlertDialog(
                  context,
                  'Profile Incomplete',
                  'Please enter your first name.',
                  onTap: _firstNameFocusNode.requestFocus,
                );
              } else {
                final fullName =
                    '${_firstNameController.text} ${_lastNameController.text}';
                ref.read(userProfileProvider.notifier).editProfile(
                      username: _userNameController.text,
                      firstName: _firstNameController.text,
                      lastName: _lastNameController.text,
                      gender: profileState.selectedGender,
                      dateOfBirth: profileState.selectedDate,
                      address: _addressController.text,
                      fullName: fullName,
                    );
              }
            },
            buttonText: tl(context).update_profile,
          ),
      ],
    );
  }

  Column _firstNameField(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tl(context).first_name,
          style: robotoMedium.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeDefault,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        CustomTextField(
          hintText: tl(context).first_name,
          focusNode: _firstNameFocusNode,
          controller: _firstNameController,
        ),
      ],
    );
  }

  Column _lastNameField(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tl(context).last_name,
          style: robotoMedium.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeDefault,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        CustomTextField(
          hintText: tl(context).last_name,
          controller: _lastNameController,
        ),
      ],
    );
  }

  Column _userNameField(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tl(context).user_name,
          style: robotoMedium.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeDefault,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        CustomTextField(
          hintText: tl(context).user_name,
          isReadOnly: true,
          controller: _userNameController,
        ),
      ],
    );
  }

  Column _emailAddressField(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tl(context).email_address,
          style: robotoMedium.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeDefault,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        CustomTextField(
          hintText: tl(context).email_address,
          isReadOnly: true,
          controller: _emailController,
        ),
      ],
    );
  }

  Column _genderField(BuildContext context, UserProfileState profileState) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tl(context).gender,
          style: robotoMedium.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeDefault,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            border: Border.all(
              color: Theme.of(context)
                  .colorScheme
                  .onSurface
                  .withValues(alpha: 0.6),
            ),
          ),
          child: Padding(
            padding:
                const EdgeInsets.only(left: 10, right: 5, top: 3, bottom: 3),
            child: DropdownButton<String>(
              hint: Text(tl(context).select_gender),
              value: profileState.selectedGender,
              isExpanded: true,
              underline: Container(),
              onChanged: (newValue) {
                ref.read(userProfileProvider.notifier).changeGender(newValue!);
              },
              items: _genderList.map((gender) {
                return DropdownMenuItem<String>(
                  value: gender,
                  child: Text(
                    gender,
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                      color: Theme.of(context).colorScheme.onSurface,
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),
      ],
    );
  }

  Widget _dateOfBirthField(
    BuildContext context,
    UserProfileState profileState,
  ) {
    return InkWell(
      onTap: () => _selectDate(context),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            tl(context).date_of_birth,
            style: robotoMedium.copyWith(
              color: Theme.of(context).colorScheme.onSurface,
              fontSize: Dimensions.fontSizeDefault,
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Container(
            height: 44,
            decoration: BoxDecoration(
              border: Border.all(
                color: Theme.of(context)
                    .colorScheme
                    .onSurface
                    .withValues(alpha: 0.6),
              ),
              borderRadius: const BorderRadius.all(
                Radius.circular(Dimensions.paddingSizeExtraSmall),
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeSmall,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    profileState.selectedDate ?? '',
                    style: robotoRegular.copyWith(
                      color: Theme.of(context).colorScheme.onSurface,
                    ),
                  ),
                  SvgPicture.asset(Images.calendar),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
