import 'dart:ui';

import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Locale Provider
// ---------------------------------------------------------------------------
class LocaleNotifier extends Notifier<Locale> {
  @override
  Locale build() {
    final prefs = ref.read(sharedPreferencesProvider);
    final langCode = prefs.getString(AppConstants.languageCode) ??
        AppConstants.languages[0].languageCode!;
    final countryCode = prefs.getString(AppConstants.countryCode) ??
        AppConstants.languages[0].countryCode!;
    return Locale(langCode, countryCode);
  }

  void setLocale(Locale locale) {
    state = locale;
    final prefs = ref.read(sharedPreferencesProvider);
    prefs.setString(AppConstants.languageCode, locale.languageCode);
    prefs.setString(AppConstants.countryCode, locale.countryCode ?? '');
  }
}

final localeProvider =
    NotifierProvider<LocaleNotifier, Locale>(LocaleNotifier.new);
