import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/form_validation.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/auth/presentation/widgets/dialog_manager.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class SignInScreen extends ConsumerStatefulWidget {
  const SignInScreen({
    required this.fromPage,
    super.key,
  });
  final String fromPage;

  @override
  ConsumerState<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends ConsumerState<SignInScreen> {
  final formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _emailFocus = FocusNode();
  final isActiveLogin = ValueNotifier<bool>(false);

  @override
  void initState() {
    super.initState();
    final authState = ref.read(authProvider).valueOrNull;
    _emailController.text = authState?.savedNumber ?? '';
    _passwordController.text = authState?.savedPassword ?? '';
    Future(() => ref.read(authProvider.notifier).allowSocialAuth());
    _emailController.addListener(_onFormChanged);
    _passwordController.addListener(_onFormChanged);
  }

  void _onFormChanged() {
    final email = _emailController.text;
    final pass = _passwordController.text;
    isActiveLogin.value = email.isNotEmpty && pass.isNotEmpty;
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailFocus.dispose();
    isActiveLogin.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final isLoading = authState.valueOrNull?.isLoading ?? false;
    final notifier = ref.read(authProvider.notifier);

    return Scaffold(
      body: Stack(
        children: [
          mainUI(context, authState, notifier),
          if (isLoading)
            Center(
              child: SizedBox(
                height: 100,
                width: 100,
                child: Card(
                  color: Theme.of(context).colorScheme.surface,
                  child: const LoadingIndicator(
                    height: 45,
                  ),
                ),
              ),
            )
          else
            const SizedBox.shrink(),
        ],
      ),
    );
  }

  Widget mainUI(
    BuildContext context,
    AsyncValue<AuthState> authState,
    AuthNotifier notifier,
  ) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.only(
          top: 100,
          left: Dimensions.paddingSizeLarge,
          right: Dimensions.paddingSizeLarge,
        ),
        child: Column(
          spacing: Dimensions.paddingSizeDefault,
          children: [
            Text(
              tl(context).sign_in,
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            emailField(context),
            passwordField(context),
            Align(
              alignment: Alignment.centerRight,
              child: forgetPasswordButton(context),
            ),
            ValueListenableBuilder<bool>(
              valueListenable: isActiveLogin,
              builder: (context, isActive, _) {
                return CustomButton(
                  margin: EdgeInsets.zero,
                  isActive: isActive,
                  onPressed: () async {
                    final email = _emailController.text;
                    if (!FormValidation().isValidEmail(email)) {
                      return DialogManager(context: context)
                          .dialogInvalidEmail()
                          .then((_) => _emailFocus.requestFocus());
                    }
                    notifier.login(
                      name: _emailController.text,
                      password: _passwordController.text,
                    );
                  },
                  buttonText: tl(context).sign_in,
                );
              },
            ),
            Visibility(
              visible: authState.valueOrNull?.checkSupportSocialAuth ?? false,
              child: socialAuthWidget(context, notifier),
            ),
            newUserUI(context),
          ],
        ),
      ),
    );
  }

  Widget textContinueWith(BuildContext context) {
    return Row(
      spacing: Dimensions.paddingSizeSmall,
      children: [
        const Expanded(child: Divider()),
        Text(
          tl(context).or_continue_with,
          style: Theme.of(context).textTheme.labelMedium,
        ),
        const Expanded(child: Divider()),
      ],
    );
  }

  Widget socialAuthWidget(BuildContext context, AuthNotifier notifier) {
    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
      child: Column(
        children: [
          textContinueWith(context),
          const SizedBox(
            height: Dimensions.paddingSizeDefault,
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            spacing: Dimensions.paddingSizeDefault,
            children: [
              socialAuthButton(
                context,
                'Google',
                Images.googleIcon,
                () async => notifier.googleAuth(),
              ),
              socialAuthButton(
                context,
                'Microsoft',
                Images.microsoftIcon,
                () async => notifier.microsoftAuth(),
              ),
              socialAuthButton(
                context,
                'Facebook',
                Images.facebookIcon,
                () async => notifier.facebookAuth(),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget socialAuthButton(
    BuildContext context,
    String text,
    String icon,
    VoidCallback onTap,
  ) {
    return Card(
      color: Colors.grey.shade200,
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        onTap: () {
          onTap();
          // hide keyboard
          FocusManager.instance.primaryFocus?.unfocus();
        },
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: SvgPicture.asset(
            icon,
            height: 40,
          ),
        ),
      ),
    );
  }

  Widget newUserUI(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            tl(context).new_user,
            style: Theme.of(context).textTheme.labelLarge,
          ),
          InkWell(
            onTap: () {
              // formKey.currentState!.reset();
              context.push(AppRoutes.signUp);
            },
            child: Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
              child: Text(
                tl(context).create_an_account,
                style: Theme.of(context)
                    .textTheme
                    .labelLarge!
                    .copyWith(color: Theme.of(context).primaryColor),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget emailField(BuildContext context) {
    return CustomTextField(
      hintText: tl(context).email_address,
      inputType: TextInputType.emailAddress,
      inputAction: TextInputAction.done,
      controller: _emailController,
      focusNode: _emailFocus,
      prefixIcon: Padding(
        padding: const EdgeInsets.only(right: Dimensions.paddingSizeDefault),
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
    );
  }

  Widget passwordField(BuildContext context) {
    return CustomTextField(
      hintText: tl(context).password,
      controller: _passwordController,
      inputType: TextInputType.visiblePassword,
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

  InkWell forgetPasswordButton(BuildContext context) {
    return InkWell(
      onTap: () {
        context.push(AppRoutes.forgotPasswordScreen);
      },
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
        child: Text(
          tl(context).forgot_password,
          style: Theme.of(context)
              .textTheme
              .labelLarge!
              .copyWith(color: Theme.of(context).primaryColor),
        ),
      ),
    );
  }
}
