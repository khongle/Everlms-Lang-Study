import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/auth/presentation/widgets/dialog_manager.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class NewPassScreen extends ConsumerStatefulWidget {
  const NewPassScreen({super.key});

  @override
  ConsumerState<NewPassScreen> createState() => _NewPassScreenState();
}

class _NewPassScreenState extends ConsumerState<NewPassScreen> {
  final _newPasswordController = TextEditingController();
  final _confirmNewPasswordController = TextEditingController();
  final isActiveResetPass = ValueNotifier<bool>(false);
  final _passFocus = FocusNode();
  @override
  void initState() {
    super.initState();
    _newPasswordController.addListener(_onFormChanged);
    _confirmNewPasswordController.addListener(_onFormChanged);
  }

  void _onFormChanged() {
    final pass = _newPasswordController.text;
    final confirmPass = _confirmNewPasswordController.text;
    isActiveResetPass.value = pass.isNotEmpty && confirmPass.isNotEmpty;
  }

  Future<void> handleResetPassword() async {
    final pass = _newPasswordController.text;
    final confirmPass = _confirmNewPasswordController.text;
    if (pass.isNotEmpty && confirmPass.isNotEmpty && pass == confirmPass) {
      return ref.read(authProvider.notifier).resetPasswordWithToken(pass);
    }
    return DialogManager(context: context)
        .dialogPasswordMismatch()
        .then((_) => _passFocus.requestFocus());
  }

  @override
  void dispose() {
    _newPasswordController.dispose();
    _confirmNewPasswordController.dispose();
    _passFocus.dispose();
    isActiveResetPass.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      child: Scaffold(
        body: Builder(
          builder: (context) {
            final authState = ref.watch(authProvider);
            final isLoading = authState.valueOrNull?.isLoading ?? false;
            return Stack(
              children: [
                mainUI(context),
                if (isLoading) const LoadingIndicator() else const SizedBox(),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget mainUI(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.only(
        top: 100,
        left: Dimensions.paddingSizeDefault,
        right: Dimensions.paddingSizeDefault,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        spacing: Dimensions.paddingSizeLarge,
        children: [
          Text(
            tl(context).set_new_password,
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          passwordField(tl(context).password),
          confirmPasswordField(tl(context).confirm_password),
          ValueListenableBuilder<bool>(
            valueListenable: isActiveResetPass,
            builder: (context, isActive, _) {
              return CustomButton(
                margin: EdgeInsets.zero,
                isActive: isActive,
                onPressed: () async => handleResetPassword(),
                buttonText: tl(context).reset_password,
              );
            },
          ),
          backToLoginButton(context),
        ],
      ),
    );
  }

  Align backToLoginButton(BuildContext context) {
    return Align(
      child: Padding(
        padding: const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
        child: TextButton.icon(
          icon: Icon(
            Icons.arrow_back,
            color: Theme.of(context)
                .textTheme
                .titleLarge!
                .color!
                .withValues(alpha: 0.6),
          ),
          onPressed: () => context.go(AppRoutes.getSignInRoute('fromSplash')),
          label: Text(
            tl(context).back_to_login,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyMedium!.copyWith(
                  color: Theme.of(context).textTheme.titleLarge!.color,
                ),
          ),
        ),
      ),
    );
  }

  Widget passwordField(String hintText) {
    return CustomTextField(
      hintText: hintText,
      controller: _newPasswordController,
      focusNode: _passFocus,
      prefixIcon: Padding(
        padding: const EdgeInsets.only(right: Dimensions.paddingSizeDefault),
        child: Padding(
          padding: const EdgeInsets.only(
            left: Dimensions.paddingSizeDefault,
            top: Dimensions.paddingSizeDefault,
            bottom: Dimensions.paddingSizeDefault,
          ),
          child: SvgPicture.asset(
            Images.lock,
          ),
        ),
      ),
      isPassword: true,
    );
  }

  Widget confirmPasswordField(String hintText) {
    return CustomTextField(
      hintText: hintText,
      controller: _confirmNewPasswordController,
      prefixIcon: Padding(
        padding: const EdgeInsets.only(right: Dimensions.paddingSizeDefault),
        child: Padding(
          padding: const EdgeInsets.only(
            left: Dimensions.paddingSizeDefault,
            top: Dimensions.paddingSizeDefault,
            bottom: Dimensions.paddingSizeDefault,
          ),
          child: SvgPicture.asset(
            Images.lock,
          ),
        ),
      ),
      isPassword: true,
    );
  }
}
