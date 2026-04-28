import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/settings/presentation/providers/switch_domain_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

class SelectDomain extends ConsumerStatefulWidget {
  const SelectDomain({super.key});

  @override
  ConsumerState<SelectDomain> createState() => _SelectDomainState();
}

class _SelectDomainState extends ConsumerState<SelectDomain> {
  bool isEmptyTextField = false;
  final TextEditingController domainController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    domainController.clear();
  }

  @override
  void dispose() {
    domainController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      insetPadding: const EdgeInsets.symmetric(horizontal: 50),
      clipBehavior: Clip.antiAliasWithSaveLayer,
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            title(),
            Padding(
              padding: const EdgeInsets.symmetric(
                vertical: Dimensions.paddingSizeDefault,
              ),
              child: textFieldCustom(context),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                platformButton(
                  context,
                  tl(context).cancel,
                  cancelSwitchButton,
                  isDestructive: true,
                ),
                platformButton(
                  context,
                  tl(context).switch_domain,
                  switchDomain,
                  isDefault: true,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget platformButton(
    BuildContext context,
    String label,
    VoidCallback onTap, {
    bool isDefault = false,
    bool isDestructive = false,
  }) {
    return TextButton(
      onPressed: onTap,
      style: TextButton.styleFrom(
        backgroundColor:
            isDestructive ? Colors.transparent : Theme.of(context).primaryColor,
        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.titleMedium!.copyWith(
              color: isDestructive
                  ? Theme.of(context).colorScheme.error
                  : Colors.white,
            ),
      ),
    );
  }

  Future<void> switchDomain() async {
    if (_formKey.currentState?.validate() ?? false) {
      final url = domainController.text.trim();
      ref.read(switchDomainProvider.notifier).setDomain(url);
      context.pop();
      context.push(AppRoutes.switchDomainScreen, extra: url);
    } else {
      setState(() {});
    }
  }

  void cancelSwitchButton() {
    context.pop();
    domainController.clear();
  }

  Widget title() => Row(
        children: [
          Expanded(
            child: Text(
              tl(context).switch_domain,
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.headlineSmall,
            ),
          ),
          InkWell(
            onTap: () => launchUrl(Uri.parse('https://everlms.com')),
            child: SvgPicture.asset(
              Images.quiz,
              height: 25,
              colorFilter: ColorFilter.mode(
                Theme.of(context).colorScheme.onSurface,
                BlendMode.srcIn,
              ),
            ),
          ),
        ],
      );

  Widget textFieldCustom(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeSmall),
      child: Form(
        key: _formKey,
        child: CustomTextField(
          controller: domainController,
          hintText: 'example.com',
          fillColor: Theme.of(context).colorScheme.primaryContainer,
          isShowBorder: false,
          isAutoFocus: true,
          maxLines: 2,
          inputType: TextInputType.url,
          inputAction: TextInputAction.go,
          onValidate: (value) => ref
              .read(switchDomainProvider.notifier)
              .checkValidDomain(value ?? ''),
        ),
      ),
    );
  }
}
