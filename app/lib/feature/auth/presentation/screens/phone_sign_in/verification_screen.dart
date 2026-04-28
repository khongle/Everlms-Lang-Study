import 'dart:async';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/help_me.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pin_code_fields/pin_code_fields.dart';

class VerificationScreen extends ConsumerStatefulWidget {
  const VerificationScreen({this.number, this.isLogin, super.key});
  final String? number;
  final bool? isLogin;

  @override
  ConsumerState<VerificationScreen> createState() => _VerificationScreenState();
}

class _VerificationScreenState extends ConsumerState<VerificationScreen> {
  String? _number;
  bool? _isLogin = false;
  String _verificationCode = '';
  Timer? _timer;
  int? _seconds = 0;

  @override
  void initState() {
    super.initState();
    _number = widget.number;
    _isLogin = widget.isLogin;
    _startTimer();
  }

  void _startTimer() {
    _seconds = 30;
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _seconds = _seconds! - 1;
      if (_seconds == 0) {
        timer.cancel();
        _timer?.cancel();
      }
      setState(() {});
    });
  }

  @override
  void dispose() {
    super.dispose();
    _timer?.cancel();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        bgColor: Theme.of(context).cardColor,
        title: tl(context).otp_verification,
        titleColor: Theme.of(context).colorScheme.onSurface,
        centerTitle: false,
      ),
      body: Builder(
        builder: (context) {
          final authState = ref.watch(authProvider);
          final isLoading = authState.valueOrNull?.isLoading ?? false;
          return Padding(
            padding: const EdgeInsets.all(20),
            child: Stack(
              children: [
                mainUI(context),
                if (isLoading) const LoadingIndicator() else const SizedBox(),
              ],
            ),
          );
        },
      ),
    );
  }

  Column mainUI(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        //const SizedBox(height: Dimensions.paddingSizeExtraLarge,),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            RichText(
              textAlign: TextAlign.center,
              text: TextSpan(
                text: tl(context).enter_the_verification_code,
                style: Theme.of(context).textTheme.bodySmall!.copyWith(
                      height: 1.5,
                      color: Theme.of(context)
                          .textTheme
                          .bodyLarge!
                          .color!
                          .withValues(alpha: .6),
                    ),
                children: [
                  const TextSpan(
                    text: '  ',
                  ),
                  TextSpan(
                    text: _number,
                    style: Theme.of(context).textTheme.bodySmall!.copyWith(
                          color: Theme.of(context)
                              .textTheme
                              .bodyLarge!
                              .color!
                              .withValues(alpha: .6),
                        ),
                  ),
                ],
              ),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeExtraLarge,
            ),
            Center(
              child: SizedBox(
                width: MediaQuery.of(context).size.width / 1.8,
                child: PinCodeTextField(
                  length: 4,
                  appContext: context,
                  keyboardType: TextInputType.number,
                  pinTheme: PinTheme(
                    shape: PinCodeFieldShape.box,
                    fieldHeight: 46,
                    fieldWidth: 46,
                    borderWidth: 1,
                    borderRadius:
                        BorderRadius.circular(Dimensions.paddingSizeSmall),
                    selectedColor:
                        Theme.of(context).primaryColor.withValues(alpha: 0.2),
                    selectedFillColor: Theme.of(context).primaryColorLight,
                    inactiveFillColor: Theme.of(context).cardColor,
                    inactiveColor: Theme.of(context).primaryColorLight,
                    activeColor:
                        Theme.of(context).primaryColor.withValues(alpha: 0.2),
                    activeFillColor: Theme.of(context).cardColor,
                  ),
                  animationDuration: const Duration(milliseconds: 300),
                  backgroundColor: Colors.transparent,
                  enableActiveFill: true,
                  onChanged: (value) {
                    setState(() => _verificationCode = value);
                  },
                  beforeTextPaste: (text) => true,
                ),
              ),
            ),
            const SizedBox(height: 30),
            SizedBox(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    tl(context).did_not_receive_the_code,
                    style: Theme.of(context).textTheme.bodySmall!.copyWith(
                          color: Theme.of(context)
                              .textTheme
                              .bodyLarge!
                              .color!
                              .withValues(alpha: .6),
                        ),
                  ),
                  if (_seconds == 0)
                    TextButton(
                      onPressed: () {
                        ref
                            .read(authProvider.notifier)
                            .resendPhoneOTP(_number ?? '')
                            .then((value) => _startTimer());
                      },
                      child: Text(
                        tl(context).resend_it,
                        style:
                            Theme.of(context).textTheme.labelMedium!.copyWith(
                                  color: Theme.of(context)
                                      .textTheme
                                      .bodyLarge!
                                      .color!
                                      .withValues(alpha: .6),
                                ),
                        textAlign: TextAlign.end,
                      ),
                    )
                  else
                    Text(
                      _seconds.toString(),
                      style: Theme.of(context).textTheme.bodySmall!.copyWith(
                            color: Theme.of(context)
                                .textTheme
                                .bodyLarge!
                                .color!
                                .withValues(alpha: .6),
                          ),
                      textAlign: TextAlign.end,
                    ),
                ],
              ),
            ),
          ],
        ),

        Column(
          children: [
            if (_verificationCode.length == 4)
              !(ref.watch(authProvider).valueOrNull?.isLoading ?? false)
                  ? Padding(
                      padding: const EdgeInsets.only(
                        top: Dimensions.paddingSizeExtraLarge,
                      ),
                      child: CustomButton(
                        buttonText: tl(context).submit,
                        onPressed: () {
                          if (isRedundentClick(DateTime.now())) {
                            return;
                          }
                          _isLogin!
                              ? ref
                                  .read(authProvider.notifier)
                                  .verifyLoginPhoneOtp(
                                    _number ?? '',
                                    _verificationCode,
                                  )
                              : ref.read(authProvider.notifier).verifyPhoneOtp(
                                    _number ?? '',
                                    _verificationCode,
                                  );
                        },
                      ),
                    )
                  : const LoadingIndicator()
            else
              const SizedBox.shrink(),
            const SizedBox(
              height: Dimensions.paddingSizeExtraLarge,
            ),
          ],
        ),
      ],
    );
  }
}
