import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/settings/presentation/providers/locale_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class LanguageChange extends ConsumerWidget {
  const LanguageChange({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentLocale = ref.watch(localeProvider);
    final selectedIndex = AppConstants.languages.indexWhere(
      (lang) => lang.languageCode == currentLocale.languageCode,
    );
    return Container(
      decoration: BoxDecoration(
        borderRadius: const BorderRadius.vertical(
          top: Radius.circular(Dimensions.radiusExtraLarge),
        ),
        color: Theme.of(context).cardColor,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
              vertical: Dimensions.paddingSizeDefault,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  tl(context).language,
                  style: robotoSemiBold.copyWith(
                    color: Theme.of(context).colorScheme.onSurface,
                    fontSize: Dimensions.fontSizeDefault,
                  ),
                ),
                InkWell(
                  onTap: () => context.pop(),
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  child: Padding(
                    padding: const EdgeInsets.all(
                      Dimensions.paddingSizeExtraSmall,
                    ),
                    child: SvgPicture.asset(
                      Images.cancel,
                      height: Dimensions.paddingSizeSmall,
                      width: Dimensions.paddingSizeSmall,
                      colorFilter: ColorFilter.mode(
                        Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
                        BlendMode.srcIn,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          RadioGroup<int>(
            groupValue: selectedIndex,
            onChanged: (value) {
              if (value == null) return;
              ref.read(localeProvider.notifier).setLocale(
                    Locale(
                      AppConstants.languages[value].languageCode!,
                      AppConstants.languages[value].countryCode,
                    ),
                  );
            },
            child: ListView.builder(
              shrinkWrap: true,
              itemCount: AppConstants.languages.length,
              itemBuilder: (context, index) {
                return Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: Dimensions.paddingSizeDefault,
                      ),
                      child: Divider(
                        color: Theme.of(context)
                            .colorScheme
                            .onSurface
                            .withValues(alpha: 0.06),
                        thickness: 1,
                      ),
                    ),
                    InkWell(
                      onTap: () {
                        ref.read(localeProvider.notifier).setLocale(
                              Locale(
                                AppConstants.languages[index].languageCode!,
                                AppConstants.languages[index].countryCode,
                              ),
                            );
                      },
                      child: Row(
                        children: [
                          Radio<int>(
                            value: index,
                            fillColor: WidgetStateColor.resolveWith(
                              (states) => Theme.of(context).primaryColor,
                            ),
                          ),
                          Text(
                            AppConstants.languages[index].languageName!,
                            style: robotoRegular.copyWith(
                              color: Theme.of(context).colorScheme.onSurface,
                              fontSize: Dimensions.fontSizeSmall,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
