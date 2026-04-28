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

class ForgotPasswordOTPVerificationScreen extends ConsumerStatefulWidget {
  const ForgotPasswordOTPVerificationScreen({this.emailArg, super.key});
  final String? emailArg;

  @override
  ConsumerState<ForgotPasswordOTPVerificationScreen> createState() =>
      _ForgotPasswordOTPVerificationScreenState();
}

class _ForgotPasswordOTPVerificationScreenState
    extends ConsumerState<ForgotPasswordOTPVerificationScreen> {
  String? _email;
  String _verificationCode = '';

  @override
  void initState() {
    super.initState();
    _email = widget.emailArg;
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final isLoading = authState.valueOrNull?.isLoading ?? false;
    return Scaffold(
      appBar: CustomAppBar(
        bgColor: Theme.of(context).cardColor,
        title: tl(context).email_otp_verification,
        titleColor: Theme.of(context).colorScheme.onSurface,
        centerTitle: false,
      ),
      body: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Stack(
          children: [
            mainUI(context),
            if (isLoading) const LoadingIndicator() else const SizedBox(),
          ],
        ),
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
                    //text: _number!.substring(1, _number!.length - 1),
                    text: _email,
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
                          ref
                              .read(authProvider.notifier)
                              .verifyOTPForForgotPassword(
                                _email ?? '',
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
