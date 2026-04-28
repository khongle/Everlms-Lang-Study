import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/widgets/custom_alert_dialog.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pin_code_fields/pin_code_fields.dart';

class EmailVerificationScreen extends ConsumerStatefulWidget {
  const EmailVerificationScreen({
    required this.fromPage,
    required this.email,
    super.key,
  });
  final String fromPage;
  final String email;

  @override
  ConsumerState<EmailVerificationScreen> createState() =>
      _EmailVerificationScreenState();
}

class _EmailVerificationScreenState
    extends ConsumerState<EmailVerificationScreen> {
  late String _email;
  Timer? _timer;
  int? _seconds = 90;
  bool isForgotPassword = false;
  final FocusNode _focusNode = FocusNode();

  StreamController<ErrorAnimationType>? errorController;
  final _otpController = TextEditingController();
  int countResend = 0;
  final formKey = GlobalKey<FormState>();
  final isActiveSubmit = ValueNotifier<bool>(false);

  @override
  void initState() {
    super.initState();
    _email = widget.email;
    isForgotPassword = widget.fromPage == AppRoutes.forgotPasswordScreen;
    if (_email.isNotEmpty) {
      errorController = StreamController<ErrorAnimationType>();
      _startTimer();
      _otpController.addListener(_onFormChange);
    }
  }

  void _onFormChange() {
    isActiveSubmit.value = _otpController.text.length == 6;
  }

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _seconds = _seconds! - 1;
      if (_seconds == 0) {
        timer.cancel();
      }
      setState(() {});
    });
  }

  @override
  void dispose() {
    super.dispose();
    _timer?.cancel();
    errorController?.close();
    _otpController.clear();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        bgColor: Theme.of(context).cardColor,
      ),
      body: Stack(
        children: [
          mainUI(context),
        ],
      ),
    );
  }

  Widget mainUI(BuildContext context) {
    return SingleChildScrollView(
      child: Padding(
        padding: const EdgeInsets.only(
          top: 50,
          left: Dimensions.paddingSizeDefault,
          right: Dimensions.paddingSizeDefault,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          spacing: Dimensions.paddingSizeDefault,
          children: [
            Text(
              tl(context).verify_your_email,
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            RichText(
              text: TextSpan(
                text: tl(context).enter_the_verification_code,
                style: Theme.of(context).textTheme.bodyLarge,
                children: [
                  const TextSpan(
                    text: '\n',
                  ),
                  TextSpan(
                    text: _email,
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                ],
              ),
            ),
            pinCode(context),
            bthVerificationOTP(),
            Padding(
              padding:
                  const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
              child: Align(
                child: btnResendOTP(context),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String formatTime(int seconds) {
    final minutes = seconds ~/ 60;
    final remainingSeconds = seconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${remainingSeconds.toString().padLeft(2, '0')}';
  }

  Widget btnResendOTP(BuildContext context) {
    return _seconds == 0
        ? Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                tl(context).did_not_receive_the_code,
                style: Theme.of(context).textTheme.labelLarge!.copyWith(
                      color: Theme.of(context)
                          .textTheme
                          .bodyLarge!
                          .color!
                          .withValues(alpha: .6),
                    ),
              ),
              InkWell(
                onTap: () {
                  countResend++;
                  _seconds = 120 + (countResend - 1) * 60;
                  _startTimer();
                  if (isForgotPassword) {
                    ref.read(authProvider.notifier).sendEmailOTP(_email);
                  } else {
                    ref.read(authProvider.notifier).resendRegisterOtpServer();
                  }
                },
                highlightColor: Colors.transparent,
                child: Text(
                  tl(context).resend_code,
                  style: Theme.of(context).textTheme.labelLarge,
                  textAlign: TextAlign.end,
                ),
              ),
            ],
          )
        : RichText(
            text: TextSpan(
              text: '${tl(context).resend_code} in ',
              style: Theme.of(context).textTheme.labelLarge!.copyWith(
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge!
                        .color!
                        .withValues(alpha: .6),
                  ),
              children: [
                TextSpan(
                  text: formatTime(_seconds ?? 0),
                  style: Theme.of(context).textTheme.labelLarge,
                ),
              ],
            ),
            textAlign: TextAlign.end,
          );
  }

  Widget pinCode(BuildContext context) {
    return Form(
      key: formKey,
      child: PinCodeTextField(
        length: 6,
        appContext: context,
        controller: _otpController,
        autoDisposeControllers: false,
        errorAnimationController: errorController,
        focusNode: _focusNode,
        keyboardType: TextInputType.number,
        animationType: AnimationType.none,
        textStyle: Theme.of(context).textTheme.titleLarge!.copyWith(
              fontWeight: FontWeight.w600,
              fontSize: Dimensions.fontSizeOverLarge,
            ),
        pinTheme: PinTheme(
          shape: PinCodeFieldShape.box,
          fieldHeight: 60,
          fieldWidth: 50,
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          selectedColor: Theme.of(context).primaryColor,
          selectedFillColor: Colors.transparent,
          inactiveColor:
              Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
          activeColor:
              Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
        ),
        errorTextSpace: 25,
        errorAnimationDuration: 200,
        backgroundColor: Colors.transparent,
        autovalidateMode: AutovalidateMode.disabled,
        validator: (code) => ref.read(authProvider.notifier).validateEmailOTP(
              _otpController.text,
              widget.fromPage,
            ),
        beforeTextPaste: (text) => true,
      ),
    );
  }

  Widget bthVerificationOTP() {
    return ValueListenableBuilder(
      valueListenable: isActiveSubmit,
      builder: (context, isActive, _) {
        return Column(
          children: [
            CustomButton(
              buttonText: tl(context).continueAction,
              fontSize: Dimensions.fontSizeLarge,
              margin: EdgeInsets.zero,
              isActive: isActive,
              onPressed: () async {
                if (isRedundentClick(DateTime.now())) {
                  return;
                }
                _focusNode.unfocus();
                if (formKey.currentState!.validate()) {
                  return handleVerificationSuccess();
                }
                errorController?.add(ErrorAnimationType.shake);
                handleBlockUser();
              },
            ),
          ],
        );
      },
    );
  }

  Future<void> handleBlockUser() async {
    final notifier = ref.read(authProvider.notifier);
    if (isForgotPassword && notifier.attemptsVerification == 5) {
      final uid = await notifier.getUserDrupalUID(_email);
      if (uid != null) {
        await notifier.blockUser(uid);
      }
      if (!mounted) return;
      customAlertDialog(
        context,
        tl(context).account_block,
        tl(context).too_many_fail_attempts,
        onTap: () {
          context.go(AppRoutes.getSignInRoute('fromSplash'));
        },
      );
    }
  }

  Future<void> handleVerificationSuccess() async {
    final notifier = ref.read(authProvider.notifier);
    final otp = _otpController.text;

    if (isForgotPassword) {
      await notifier.verifyForgotPasswordOtpServer(otp);
      return;
    }

    // Register flow: verify OTP server-side; auto-login on success.
    final error = await notifier.verifyRegisterOtpServer(otp);
    if (error != null && mounted) {
      errorController?.add(ErrorAnimationType.shake);
      customSnackBar(error);
    }
  }
}
