import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// ---------------------------------------------------------------------------
// Surface tokens per brightness
//
// Dark palette = `design.md` (Phase 01 Premium Dark) — see AppColors.
// Light palette = complementary tokens for the same design system, keeping
// brand/accent colors identical across modes.
// ---------------------------------------------------------------------------

class _Surfaces {
  const _Surfaces({
    required this.bgPrimary,
    required this.bgSecondary,
    required this.bgTertiary,
    required this.bgCard,
    required this.bgGlass,
    required this.textPrimary,
    required this.textSecondary,
    required this.textMuted,
    required this.textLink,
    required this.border,
    required this.borderHover,
    required this.shadow,
  });

  final Color bgPrimary;
  final Color bgSecondary;
  final Color bgTertiary;
  final Color bgCard;
  final Color bgGlass;
  final Color textPrimary;
  final Color textSecondary;
  final Color textMuted;
  final Color textLink;
  final Color border;
  final Color borderHover;
  final Color shadow;
}

const _Surfaces _darkSurfaces = _Surfaces(
  bgPrimary: AppColors.bgPrimary,
  bgSecondary: AppColors.bgSecondary,
  bgTertiary: AppColors.bgTertiary,
  bgCard: AppColors.bgCard,
  bgGlass: AppColors.bgGlass,
  textPrimary: AppColors.textPrimary,
  textSecondary: AppColors.textSecondary,
  textMuted: AppColors.textMuted,
  textLink: AppColors.textLink,
  border: AppColors.border,
  borderHover: AppColors.borderHover,
  shadow: Color(0x66000000),
);

const _Surfaces _lightSurfaces = _Surfaces(
  bgPrimary: Color(0xFFF7F8FA),
  bgSecondary: Color(0xFFFFFFFF),
  bgTertiary: Color(0xFFEEF1F5),
  bgCard: Color(0xFFFFFFFF),
  bgGlass: Color(0x0D000000),
  textPrimary: Color(0xFF0D1117),
  textSecondary: Color(0xFF4A5568),
  textMuted: Color(0xFF8B949E),
  textLink: Color(0xFF1F6FEB),
  border: Color(0x14000000),
  borderHover: Color(0x33000000),
  shadow: Color(0x1A000000),
);

// ---------------------------------------------------------------------------
// Typography (Inter — fixed across modes; color overridden by ColorScheme)
// ---------------------------------------------------------------------------

TextTheme _buildTextTheme(Color primary, Color secondary, Color muted) {
  return GoogleFonts.interTextTheme(
    TextTheme(
      displayLarge: TextStyle(
        fontSize: 36,
        fontWeight: FontWeight.w800,
        height: 1.2,
        color: primary,
      ),
      headlineMedium: TextStyle(
        fontSize: 28,
        fontWeight: FontWeight.w700,
        height: 1.2,
        color: primary,
      ),
      headlineSmall: TextStyle(
        fontSize: 22,
        fontWeight: FontWeight.w600,
        height: 1.2,
        color: primary,
      ),
      titleLarge: TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.w600,
        height: 1.2,
        color: primary,
      ),
      titleMedium: TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.w600,
        height: 1.2,
        color: primary,
      ),
      titleSmall: TextStyle(
        fontSize: 14,
        fontWeight: FontWeight.w600,
        height: 1.2,
        color: primary,
      ),
      bodyLarge: TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.w400,
        height: 1.6,
        color: primary,
      ),
      bodyMedium: TextStyle(
        fontSize: 15,
        fontWeight: FontWeight.w400,
        height: 1.6,
        color: primary,
      ),
      bodySmall: TextStyle(
        fontSize: 13,
        fontWeight: FontWeight.w400,
        height: 1.6,
        color: secondary,
      ),
      labelLarge: TextStyle(
        fontSize: 14,
        fontWeight: FontWeight.w600,
        height: 1.2,
        color: primary,
      ),
      labelSmall: TextStyle(
        fontSize: 12,
        fontWeight: FontWeight.w400,
        height: 1.2,
        color: muted,
      ),
    ),
  );
}

const _expansionTileTheme = ExpansionTileThemeData(
  backgroundColor: Colors.transparent,
  collapsedBackgroundColor: Colors.transparent,
  shape: RoundedRectangleBorder(),
  childrenPadding: EdgeInsets.only(top: 24),
  expansionAnimationStyle: AnimationStyle(
    curve: Curves.easeOut,
    duration: Duration(milliseconds: 200),
  ),
);

// ---------------------------------------------------------------------------
// Theme builder
// ---------------------------------------------------------------------------

ThemeData _buildPremiumTheme(Brightness brightness) {
  final isDark = brightness == Brightness.dark;
  final s = isDark ? _darkSurfaces : _lightSurfaces;
  final textTheme =
      _buildTextTheme(s.textPrimary, s.textSecondary, s.textMuted);

  final colorScheme = ColorScheme(
    brightness: brightness,
    primary: AppColors.accentPrimary,
    secondary: AppColors.accentSecondary,
    surface: s.bgSecondary,
    error: AppColors.accentDanger,
    onPrimary: Colors.white,
    onSecondary: Colors.white,
    onSurface: s.textPrimary,
    onError: Colors.white,
    surfaceContainerHighest: s.bgTertiary,
    outline: s.border,
  );

  return ThemeData(
    useMaterial3: true,
    brightness: brightness,
    fontFamily: GoogleFonts.inter().fontFamily,
    primaryColor: AppColors.accentPrimary,
    primaryColorLight: s.textPrimary,
    primaryColorDark: AppColors.accentSecondary,
    secondaryHeaderColor: s.bgSecondary,
    disabledColor: s.textMuted,
    scaffoldBackgroundColor: s.bgPrimary,
    canvasColor: s.bgPrimary,
    cardColor: s.bgCard,
    dividerColor: s.border,
    hintColor: s.textMuted,
    focusColor: s.borderHover,
    hoverColor: s.bgGlass,
    shadowColor: s.shadow,
    splashColor: Colors.transparent,
    highlightColor: Colors.transparent,
    textTheme: textTheme,
    colorScheme: colorScheme,
    appBarTheme: AppBarTheme(
      backgroundColor: s.bgPrimary,
      foregroundColor: s.textPrimary,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      centerTitle: true,
      iconTheme: IconThemeData(size: 24, color: s.textPrimary),
      titleTextStyle: TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.w600,
        color: s.textPrimary,
      ),
    ),
    bottomNavigationBarTheme: BottomNavigationBarThemeData(
      backgroundColor: s.bgSecondary,
      selectedItemColor: AppColors.accentPrimary,
      unselectedItemColor: s.textMuted,
      selectedLabelStyle:
          const TextStyle(fontSize: 10, fontWeight: FontWeight.w600),
      unselectedLabelStyle:
          const TextStyle(fontSize: 10, fontWeight: FontWeight.w400),
      elevation: 0,
      type: BottomNavigationBarType.fixed,
    ),
    progressIndicatorTheme: ProgressIndicatorThemeData(
      color: AppColors.accentPrimary,
      linearTrackColor: s.bgTertiary,
      circularTrackColor: s.bgTertiary,
    ),
    sliderTheme: SliderThemeData(
      thumbShape: const RoundSliderThumbShape(),
      activeTrackColor: AppColors.accentPrimary,
      inactiveTrackColor: s.bgTertiary,
      thumbColor: AppColors.accentPrimary,
      overlayColor: AppColors.accentPrimary.withValues(alpha: 0.2),
    ),
    dividerTheme: DividerThemeData(
      color: s.border,
      thickness: 1,
      space: 1,
    ),
    floatingActionButtonTheme: const FloatingActionButtonThemeData(
      backgroundColor: AppColors.accentPrimary,
      foregroundColor: Colors.white,
    ),
    iconTheme: IconThemeData(size: 20, color: s.textPrimary),
    cardTheme: CardThemeData(
      color: s.bgCard,
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: s.border),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: s.bgTertiary,
      selectedColor: AppColors.accentPrimary,
      disabledColor: s.bgTertiary,
      secondarySelectedColor: AppColors.accentPrimary,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(6),
        side: BorderSide(color: s.border),
      ),
      labelStyle: textTheme.bodySmall?.copyWith(color: s.textPrimary),
      secondaryLabelStyle: textTheme.bodySmall?.copyWith(color: Colors.white),
      brightness: brightness,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: s.bgTertiary,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      hintStyle: textTheme.bodyMedium?.copyWith(color: s.textMuted),
      labelStyle: textTheme.bodyMedium?.copyWith(color: s.textSecondary),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: s.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.accentPrimary),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.accentDanger),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.accentDanger),
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        minimumSize: const Size.fromHeight(48),
        backgroundColor: AppColors.accentPrimary,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        textStyle: textTheme.labelLarge,
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        minimumSize: const Size.fromHeight(48),
        foregroundColor: AppColors.accentPrimary,
        side: const BorderSide(color: AppColors.accentPrimary),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        textStyle: textTheme.labelLarge,
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: s.textLink,
        textStyle: textTheme.labelLarge,
      ),
    ),
    snackBarTheme: SnackBarThemeData(
      backgroundColor: s.bgTertiary,
      contentTextStyle: textTheme.bodyMedium,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
      ),
    ),
    bottomSheetTheme: BottomSheetThemeData(
      backgroundColor: s.bgSecondary,
      surfaceTintColor: Colors.transparent,
      modalBackgroundColor: s.bgSecondary,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
    ),
    dialogTheme: DialogThemeData(
      backgroundColor: s.bgSecondary,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: s.border),
      ),
    ),
    expansionTileTheme: _expansionTileTheme,
    searchBarTheme: SearchBarThemeData(
      backgroundColor: WidgetStatePropertyAll(s.bgTertiary),
      surfaceTintColor: const WidgetStatePropertyAll(Colors.transparent),
      elevation: const WidgetStatePropertyAll(0),
      side: WidgetStatePropertyAll(BorderSide(color: s.border)),
      shape: WidgetStatePropertyAll(
        RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      textStyle: WidgetStatePropertyAll(textTheme.bodyMedium),
      hintStyle: WidgetStatePropertyAll(
        textTheme.bodyMedium?.copyWith(color: s.textMuted),
      ),
    ),
    tabBarTheme: TabBarThemeData(
      labelColor: s.textPrimary,
      unselectedLabelColor: s.textSecondary,
      labelStyle: textTheme.titleSmall,
      unselectedLabelStyle: textTheme.bodySmall,
      indicator: UnderlineTabIndicator(
        borderSide: const BorderSide(color: AppColors.accentPrimary, width: 2),
        borderRadius: BorderRadius.circular(9999),
      ),
      dividerColor: s.border,
    ),
  );
}

// ---------------------------------------------------------------------------
// Public theme instances
// ---------------------------------------------------------------------------

final ThemeData light = _buildPremiumTheme(Brightness.light);
final ThemeData dark = _buildPremiumTheme(Brightness.dark);

class AppTheme {
  AppTheme._();

  static ThemeData get lightTheme => light;
  static ThemeData get darkTheme => dark;
}

extension ThemeContextExtension on BuildContext {
  bool get isDarkMode => Theme.of(this).brightness == Brightness.dark;
}
