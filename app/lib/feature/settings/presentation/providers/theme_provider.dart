import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Theme Provider
// Stores ThemeMode (light / dark / system) in SharedPreferences per user.
// Default: light theme.
// ---------------------------------------------------------------------------
class ThemeNotifier extends Notifier<ThemeMode> {
  static const _light = 'light';
  static const _dark = 'dark';
  static const _system = 'system';

  String _getThemeKeyForCurrentUser() {
    final repo = ref.read(authRepositoryProvider);
    final uid = repo.getUserUid();
    if (uid.isEmpty) {
      // Not logged in, use global key
      return AppConstants.theme;
    }
    // Use per-user key format: theme_<uid>
    return '${AppConstants.theme}_$uid';
  }

  @override
  ThemeMode build() {
    final prefs = ref.read(sharedPreferencesProvider);
    final themeKey = _getThemeKeyForCurrentUser();

    // Migrate legacy bool value (old app stored false/true) to string.
    final raw = prefs.get(themeKey);
    if (raw is bool) {
      prefs.remove(themeKey);
      // Default to light theme
      return ThemeMode.light;
    }
    final saved = raw as String?;
    return switch (saved) {
      _dark => ThemeMode.dark,
      _light => ThemeMode.light,
      _system => ThemeMode.system,
      // Default: light theme
      _ => ThemeMode.light,
    };
  }

  void setTheme(ThemeMode mode) {
    state = mode;
    final value = switch (mode) {
      ThemeMode.dark => _dark,
      ThemeMode.light => _light,
      ThemeMode.system => _system,
    };
    final prefs = ref.read(sharedPreferencesProvider);
    final themeKey = _getThemeKeyForCurrentUser();
    prefs.setString(themeKey, value);
  }

  /// Toggles between light and dark.
  ///
  /// If the app is currently using system mode, switch to the opposite of the
  /// effective platform brightness so the change is always visible after tap.
  void toggleTheme() {
    final next = switch (state) {
      ThemeMode.light => ThemeMode.dark,
      ThemeMode.dark => ThemeMode.light,
      ThemeMode.system =>
        SchedulerBinding.instance.platformDispatcher.platformBrightness ==
                Brightness.dark
            ? ThemeMode.light
            : ThemeMode.dark,
    };
    setTheme(next);
  }
}

final themeProvider =
    NotifierProvider<ThemeNotifier, ThemeMode>(ThemeNotifier.new);
