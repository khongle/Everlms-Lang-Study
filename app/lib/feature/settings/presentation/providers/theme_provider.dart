import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Theme Provider
// Stores ThemeMode (light / dark / system) in SharedPreferences.
// ---------------------------------------------------------------------------
class ThemeNotifier extends Notifier<ThemeMode> {
  static const _light = 'light';
  static const _dark = 'dark';

  @override
  ThemeMode build() {
    final prefs = ref.read(sharedPreferencesProvider);
    // Migrate legacy bool value (old app stored false/true) to string.
    final raw = prefs.get(AppConstants.theme);
    if (raw is bool) {
      prefs.remove(AppConstants.theme);
      return ThemeMode.system;
    }
    final saved = raw as String?;
    return switch (saved) {
      _dark => ThemeMode.dark,
      _light => ThemeMode.light,
      _ => ThemeMode.system,
    };
  }

  void setTheme(ThemeMode mode) {
    state = mode;
    final value = switch (mode) {
      ThemeMode.dark => _dark,
      ThemeMode.light => _light,
      ThemeMode.system => null,
    };
    final prefs = ref.read(sharedPreferencesProvider);
    if (value == null) {
      prefs.remove(AppConstants.theme);
    } else {
      prefs.setString(AppConstants.theme, value);
    }
  }

  /// Cycles light → dark → system → light …
  void toggleTheme() {
    final next = switch (state) {
      ThemeMode.light => ThemeMode.dark,
      ThemeMode.dark => ThemeMode.system,
      ThemeMode.system => ThemeMode.light,
    };
    setTheme(next);
  }
}

final themeProvider =
    NotifierProvider<ThemeNotifier, ThemeMode>(ThemeNotifier.new);
