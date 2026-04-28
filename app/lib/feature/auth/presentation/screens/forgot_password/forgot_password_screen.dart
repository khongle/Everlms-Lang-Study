import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/form_validation.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/auth/presentation/widgets/dialog_manager.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() =>
      _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final isActiveSubmit = ValueNotifier<bool>(false);
  final _emailController = TextEditingController();
  final _emailFocus = FocusNode();

  @override
  void initState() {
    super.initState();
    _emailController.addListener(_onFormChanged);
  }

  void _onFormChanged() {
    isActiveSubmit.value = _emailController.text.isNotEmpty;
  }

  @override
  void dispose() {
    _emailController.dispose();
    _emailFocus.dispose();
    isActiveSubmit.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final isLoading = authState.valueOrNull?.isLoading ?? false;

    return Scaffold(
      appBar: const CustomAppBar(),
      body: Stack(
        children: [
          mainUI(context),
          if (isLoading) const LoadingIndicator() else const SizedBox(),
        ],
      ),
    );
  }

  Widget mainUI(BuildContext context) {
    return SingleChildScrollView(
      child: Padding(
        padding: const EdgeInsets.only(
          top: 20,
          left: Dimensions.paddingSizeDefault,
          right: Dimensions.paddingSizeDefault,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          spacing: Dimensions.paddingSizeDefault,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              tl(context).forgot_password.replaceAll('?', ''),
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            Text(
              tl(context).enter_your_email,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            CustomTextField(
              controller: _emailController,
              hintText: tl(context).email_address,
              focusNode: _emailFocus,
              inputType: TextInputType.emailAddress,
              prefixIcon: Padding(
                padding: const EdgeInsets.only(
                  right: Dimensions.paddingSizeDefault,
                ),
                child: Padding(
                  padding: const EdgeInsets.only(
                    left: Dimensions.paddingSizeDefault,
                    top: Dimensions.paddingSizeDefault,
                    bottom: Dimensions.paddingSizeDefault,
                  ),
                  child: SvgPicture.asset(
                    Images.mail,
                  ),
                ),
              ),
            ),
            ValueListenableBuilder<bool>(
              valueListenable: isActiveSubmit,
              builder: (context, isActive, _) => CustomButton(
                margin: const EdgeInsets.only(top: Dimensions.paddingSizeSmall),
                isActive: isActive,
                onPressed: () => _validateThenRun(context),
                buttonText: tl(context).submit,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _validateThenRun(BuildContext context) async {
    final email = _emailController.text;
    if (!FormValidation().isValidEmail(email)) {
      return DialogManager(context: context)
          .dialogInvalidEmail()
          .then((_) => _emailFocus.requestFocus());
    }
    final notifier = ref.read(authProvider.notifier);
    final success = await notifier.sendForgotPasswordOtp(email);
    if (!context.mounted) return;
    if (success) {
      await context.push(
        AppRoutes.getEmailVerificationScreen(
          AppRoutes.forgotPasswordScreen,
          email,
        ),
      );
    }
  }
}
