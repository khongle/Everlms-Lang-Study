import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';

// ---------------------------------------------------------------------------
// Shared sub-themes (identical in light & dark)
// ---------------------------------------------------------------------------

const _progressIndicatorTheme = ProgressIndicatorThemeData(
  color: AppColors.primaryTeal,
  linearTrackColor: Color(0xff121212),
  circularTrackColor: Color(0xff121212),
);

const _sliderTheme = SliderThemeData(thumbShape: RoundSliderThumbShape());

final TextTheme _textTheme = const TextTheme(
  headlineMedium: TextStyle(fontWeight: FontWeight.bold),
  headlineSmall: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
  titleLarge: TextStyle(fontWeight: FontWeight.bold),
  titleMedium: TextStyle(fontWeight: FontWeight.bold),
  titleSmall: TextStyle(fontWeight: FontWeight.bold),
  labelSmall: TextStyle(fontWeight: FontWeight.normal),
).apply(fontFamily: 'Roboto');

const _expansionTileTheme = ExpansionTileThemeData(
  backgroundColor: Colors.transparent,
  collapsedBackgroundColor: Colors.transparent,
  shape: RoundedRectangleBorder(),
  childrenPadding: EdgeInsets.only(top: 40),
  expansionAnimationStyle: AnimationStyle(
    curve: Curves.linear,
    duration: Duration(milliseconds: 400),
  ),
);

// ---------------------------------------------------------------------------
// Theme instances
// ---------------------------------------------------------------------------

ThemeData light = ThemeData(
  useMaterial3: true,
  fontFamily: 'Ubuntu',
  primaryColor: AppColors.primaryGreen,
  primaryColorLight: const Color(0xFFF6F6F6),
  dividerColor: const Color(0xff666666),
  primaryColorDark: AppColors.primaryDarkBlue,
  secondaryHeaderColor: const Color(0xFF9BB8DA),
  disabledColor: const Color(0xFF8797AB),
  scaffoldBackgroundColor: const Color(0xFFFFFFFF),
  brightness: Brightness.light,
  hintColor: const Color(0xFFC0BFBF),
  focusColor: const Color(0xFFFFF9E5),
  hoverColor: const Color(0xFFF1F7FC),
  shadowColor: Colors.grey[300],
  cardColor: Colors.white,
  appBarTheme: const AppBarTheme(backgroundColor: AppColors.primaryGreenDark),
  bottomNavigationBarTheme: const BottomNavigationBarThemeData(),
  progressIndicatorTheme: _progressIndicatorTheme,
  sliderTheme: _sliderTheme,
  splashColor: Colors.transparent,
  textTheme: _textTheme,
  dividerTheme: DividerThemeData(color: Colors.grey.shade300, thickness: 0.5),
  colorScheme: ColorScheme.light(
    primaryContainer: const Color(0xFFF0F2F5),
    primary: AppColors.primaryGreen,
    outline: Colors.grey.shade600,
    error: const Color(0xFFFF6767),
    surface: const Color(0xffFCFCFC),
  ),
  floatingActionButtonTheme: const FloatingActionButtonThemeData(
    backgroundColor: Colors.white,
    foregroundColor: Colors.black,
  ),
  iconTheme: const IconThemeData(size: 16, color: Colors.black),
  expansionTileTheme: _expansionTileTheme,
);

ThemeData dark = ThemeData(
  useMaterial3: true,
  fontFamily: 'Ubuntu',
  primaryColor: AppColors.primaryTeal,
  dividerColor: const Color(0xff999999),
  primaryColorLight: const Color(0xFFF0F4F8),
  primaryColorDark: AppColors.primaryDarkBlue,
  secondaryHeaderColor: const Color(0xFF9BB8DA),
  disabledColor: const Color(0xFF8797AB),
  scaffoldBackgroundColor: const Color(0xFF151515),
  appBarTheme: const AppBarTheme(backgroundColor: Color(0xFF151417)),
  bottomNavigationBarTheme:
      const BottomNavigationBarThemeData(backgroundColor: Color(0xFF2B292E)),
  brightness: Brightness.dark,
  hintColor: const Color(0xFFC0BFBF),
  focusColor: const Color(0xFF484848),
  hoverColor: const Color(0x400461A5),
  shadowColor: const Color(0x33e2f1ff),
  splashColor: Colors.transparent,
  iconTheme: const IconThemeData(size: 16, color: Colors.white),
  dividerTheme: DividerThemeData(color: Colors.grey.shade800, thickness: 0.5),
  floatingActionButtonTheme: FloatingActionButtonThemeData(
    backgroundColor: Colors.grey.shade800,
    foregroundColor: Colors.white,
  ),
  progressIndicatorTheme: _progressIndicatorTheme,
  sliderTheme: _sliderTheme,
  textTheme: _textTheme,
  colorScheme: ColorScheme.dark(
    primaryContainer: const Color(0xFF292F37),
    surface: const Color(0xFF212121),
    primary: AppColors.primaryGreenDark,
    outline: Colors.grey.shade400,
  ).copyWith(error: const Color(0xFFdd3135)),
);

// ---------------------------------------------------------------------------
// Convenience accessors & context extension
// ---------------------------------------------------------------------------

class AppTheme {
  AppTheme._();

  static ThemeData get lightTheme => light;
  static ThemeData get darkTheme => dark;
}

extension ThemeContextExtension on BuildContext {
  bool get isDarkMode => Theme.of(this).brightness == Brightness.dark;
}
