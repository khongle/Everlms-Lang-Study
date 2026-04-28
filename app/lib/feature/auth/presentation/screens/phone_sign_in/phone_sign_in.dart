import 'package:country_pickers/country_pickers.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class PhoneSignIn extends ConsumerStatefulWidget {
  const PhoneSignIn({super.key});

  @override
  ConsumerState<PhoneSignIn> createState() => _PhoneSignInState();
}

class _PhoneSignInState extends ConsumerState<PhoneSignIn> {
  final _phoneController = TextEditingController();

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        bgColor: Theme.of(context).cardColor,
        title: tl(context).sign_in_with_phone,
        titleColor: Theme.of(context).colorScheme.onSurface,
        centerTitle: false,
      ),
      body: mainUI(context),
    );
  }

  Column mainUI(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
          child: Container(
            height: 45,
            width: MediaQuery.of(context).size.width,
            decoration: BoxDecoration(
              border: Border.all(
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge!
                    .color!
                    .withValues(alpha: 0.06),
              ),
              borderRadius: const BorderRadius.all(
                Radius.circular(Dimensions.paddingSizeExtraSmall),
              ),
            ),
            child: Row(
              children: [
                SizedBox(
                  width: 120,
                  child: Padding(
                    padding: const EdgeInsets.only(left: 10),
                    child: CountryPickerDropdown(
                      isFirstDefaultIfInitialValueNotProvided: false,
                      initialValue: 'BD',
                      isExpanded: true,
                      itemBuilder: (country) => Row(
                        children: <Widget>[
                          CountryPickerUtils.getDefaultFlagImage(country),
                          const SizedBox(width: 2),
                          Text('+${country.phoneCode}'),
                        ],
                      ),
                      onValuePicked: (country) {},
                    ),
                  ),
                ),
                Expanded(
                  child: CustomTextField(
                    controller: _phoneController,
                    hintText: tl(context).phone_number,
                    inputType: TextInputType.phone,
                  ),
                ),
              ],
            ),
          ),
        ),
        const Spacer(),
        SizedBox(
          height: 120,
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            child: CustomButton(
              onPressed: () {
                ref
                    .read(authProvider.notifier)
                    .getPhoneLoginOTP(_phoneController.text);
              },
              buttonText: tl(context).submit,
            ),
          ),
        ),
      ],
    );
  }
}
