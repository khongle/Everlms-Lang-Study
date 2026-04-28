import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class ChangePasswordScreen extends ConsumerStatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  ConsumerState<ChangePasswordScreen> createState() =>
      _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends ConsumerState<ChangePasswordScreen> {
  final changePassKey = GlobalKey<FormState>();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final isLoading = authState.valueOrNull?.isLoading ?? false;

    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: tl(context).changePassword,
      ),
      body: Stack(
        children: [
          mainUI(context),
          if (isLoading) const LoadingIndicator() else const SizedBox(),
        ],
      ),
    );
  }

  Form mainUI(BuildContext context) {
    return Form(
      key: changePassKey,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tl(context).change_your_password,
                  style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                        color: Theme.of(context)
                            .textTheme
                            .bodyLarge!
                            .color!
                            .withValues(alpha: 0.8),
                      ),
                ),
                const SizedBox(height: 20),
                passwordField(
                  context,
                  _currentPasswordController,
                  tl(context).current_password,
                ),
                const SizedBox(height: 20),
                passwordField(
                  context,
                  _newPasswordController,
                  tl(context).new_password,
                ),
                const SizedBox(height: 20),
                passwordField(
                  context,
                  _confirmPasswordController,
                  tl(context).confirm_password,
                ),
              ],
            ),
          ),
          CustomButton(
            onPressed: () {
              if (changePassKey.currentState!.validate()) {
                ref.read(authProvider.notifier).resetPassword(
                      currentPassword: _currentPasswordController.text,
                      newPassword: _newPasswordController.text,
                      confirmPassword: _confirmPasswordController.text,
                    );
              }
            },
            buttonText: tl(context).save_and_change,
          ),
        ],
      ),
    );
  }

  Widget passwordField(
    BuildContext context,
    TextEditingController textController,
    String hintText,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        CustomTextField(
          hintText: hintText,
          controller: textController,
          prefixIcon: Padding(
            padding:
                const EdgeInsets.only(right: Dimensions.paddingSizeDefault),
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
        ),
      ],
    );
  }
}
