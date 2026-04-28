import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/form_validation.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/auth/presentation/widgets/dialog_manager.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

class SignUpScreen extends ConsumerStatefulWidget {
  const SignUpScreen({super.key});

  @override
  ConsumerState<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends ConsumerState<SignUpScreen> {
  final _formGlobalKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _emailFocus = FocusNode();
  final _passwordFocus = FocusNode();
  final isActiveSignUp = ValueNotifier<bool>(false);

  @override
  void initState() {
    _emailController.addListener(_onFormChanged);
    _passwordController.addListener(_onFormChanged);
    _confirmPasswordController.addListener(_onFormChanged);
    super.initState();
  }

  void _onFormChanged() {
    final email = _emailController.text;
    final pass = _passwordController.text;
    final confirm = _confirmPasswordController.text;
    isActiveSignUp.value =
        email.isNotEmpty && pass.isNotEmpty && confirm.isNotEmpty;
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    isActiveSignUp.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final notifier = ref.read(authProvider.notifier);
    return PopScope(
      onPopInvokedWithResult: (_, __) {
        _emailController.clear();
        _passwordController.clear();
        _confirmPasswordController.clear();
      },
      child: Scaffold(
        appBar: const CustomAppBar(),
        body: Form(
          key: _formGlobalKey,
          child: SingleChildScrollView(
            reverse: true,
            padding: const EdgeInsets.only(
              top: 50,
              left: Dimensions.paddingSizeLarge,
              right: Dimensions.paddingSizeLarge,
            ),
            child: Column(
              spacing: Dimensions.paddingSizeLarge,
              children: [
                Text(
                  tl(context).sign_up,
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                CustomTextField(
                  controller: _emailController,
                  inputType: TextInputType.emailAddress,
                  hintText: tl(context).email,
                  focusNode: _emailFocus,
                ),
                CustomTextField(
                  isPassword: true,
                  hintText: tl(context).password,
                  controller: _passwordController,
                  focusNode: _passwordFocus,
                ),
                CustomTextField(
                  controller: _confirmPasswordController,
                  hintText: tl(context).confirm_password,
                  isPassword: true,
                ),
                policySignUpWidget(context),
                ValueListenableBuilder<bool>(
                  valueListenable: isActiveSignUp,
                  builder: (context, isActive, _) {
                    return CustomButton(
                      margin: EdgeInsets.zero,
                      isActive: isActive,
                      onPressed: () async {
                        final email = _emailController.text;
                        final pass = _passwordController.text;
                        final confirm = _confirmPasswordController.text;
                        if (!FormValidation().isValidEmail(email)) {
                          return DialogManager(context: context)
                              .dialogInvalidEmail()
                              .then((_) => _emailFocus.requestFocus());
                        }
                        if (pass.isNotEmpty &&
                            confirm.isNotEmpty &&
                            pass != confirm) {
                          return DialogManager(context: context)
                              .dialogPasswordMismatch()
                              .then((_) => _passwordFocus.requestFocus());
                        }
                        await notifier.requestRegisterOtp(
                          email: email,
                          password: pass,
                        );
                      },
                      buttonText: tl(context).continueAction,
                    );
                  },
                ),
                newUserUI(context),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Padding policySignUpWidget(BuildContext context) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
      child: RichText(
        text: TextSpan(
          style: Theme.of(context).textTheme.bodyMedium,
          children: [
            TextSpan(
              text: '${tl(context).terms_of_use}\t',
              recognizer: TapGestureRecognizer()
                ..onTap = () => launchUrl(Uri.parse(AppConstants.termsUrl)),
              style: Theme.of(context).textTheme.labelLarge!.copyWith(
                    color: Theme.of(context).primaryColor,
                  ),
            ),
            TextSpan(text: '${tl(context).and_text}\t'),
            TextSpan(
              text: tl(context).privacy_policy,
              recognizer: TapGestureRecognizer()
                ..onTap = () => launchUrl(
                      Uri.parse(AppConstants.privacyPolicyUrl),
                    ),
              style: Theme.of(context).textTheme.labelLarge!.copyWith(
                    color: Theme.of(context).primaryColor,
                  ),
            ),
          ],
          text: tl(context).by_sign_up_agree,
        ),
      ),
    );
  }

  Widget newUserUI(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          tl(context).already_have_an_account,
          style: Theme.of(context).textTheme.labelLarge,
        ),
        InkWell(
          onTap: () {
            context.pop();
            _emailController.clear();
            _passwordController.clear();
            _confirmPasswordController.clear();
          },
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
            child: Text(
              tl(context).sign_in,
              style: Theme.of(context)
                  .textTheme
                  .labelLarge!
                  .copyWith(color: Theme.of(context).primaryColor),
            ),
          ),
        ),
      ],
    );
  }
}
