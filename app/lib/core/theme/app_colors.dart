import 'package:flutter/material.dart';

/// App-wide semantic colors that do NOT change with light/dark theme.
///
/// For theme-dependent colors, use `Theme.of(context).colorScheme`.
/// See convention: `.github/instructions/flutter-convention.instructions.md` §6.
class AppColors {
  AppColors._();

  // ── Brand ──────────────────────────────────────────────────────────────
  static const Color primaryGreen = Color(0xFF667EEA);
  static const Color primaryGreenDark = Color(0xFF764BA2);
  static const Color primaryTeal = Color(0xFF4FACFE);
  static const Color primaryDarkBlue = Color(0xFF0D1117);

  // ── Status / Semantic ──────────────────────────────────────────────────
  static const Color success = Color(0xFF43E97B);
  static const Color warning = Color(0xFFFEE140);
  static const Color danger = Color(0xFFF5576C);
  static const Color info = Color(0xFF4FACFE);

  // ── Rating ─────────────────────────────────────────────────────────────
  static const Color ratingAmber = Color(0xFFFBBF24);

  // ══════════════════════════════════════════════════════════════════════
  // Design System — Dark Premium Theme (Phase 01)
  // See: .agent/docs/mobile/phase-01/design.md
  // ══════════════════════════════════════════════════════════════════════

  // ── Background ─────────────────────────────────────────────────────────
  static const Color bgPrimary = Color(0xFF0D1117);
  static const Color bgSecondary = Color(0xFF161B22);
  static const Color bgTertiary = Color(0xFF21262D);
  static const Color bgCard = Color(0xD9161B22);
  static const Color bgGlass = Color(0x0DFFFFFF);
  static const Color bgOverlay = Color(0xF20D1117);

  // ── Text ───────────────────────────────────────────────────────────────
  static const Color textPrimary = Color(0xFFE6EDF3);
  static const Color textSecondary = Color(0xFF8B949E);
  static const Color textMuted = Color(0xFF6E7681);
  static const Color textLink = Color(0xFF58A6FF);

  // ── Accent ─────────────────────────────────────────────────────────────
  static const Color accentPrimary = Color(0xFF667EEA);
  static const Color accentSecondary = Color(0xFF764BA2);
  static const Color accentSuccess = Color(0xFF43E97B);
  static const Color accentDanger = Color(0xFFF5576C);
  static const Color accentWarning = Color(0xFFFEE140);
  static const Color accentInfo = Color(0xFF4FACFE);
  static const Color accentStar = Color(0xFFFBBF24);
  static const Color accentZoom = Color(0xFF2D8CFF);

  // ── Border ─────────────────────────────────────────────────────────────
  static const Color border = Color(0x1AFFFFFF);
  static const Color borderHover = Color(0x33FFFFFF);

  // ── Gradients (as List<Color> for use in LinearGradient) ───────────────
  static const List<Color> gradientPrimary = [accentPrimary, accentSecondary];
  static const List<Color> gradientSuccess = [accentSuccess, Color(0xFF38F9D7)];
  static const List<Color> gradientInfo = [accentInfo, Color(0xFF00F2FE)];
  static const List<Color> gradientDanger = [Color(0xFFFA709A), accentWarning];
}
