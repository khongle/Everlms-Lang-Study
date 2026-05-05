import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';

// AppColors.accentPrimary is kept for brand icon color.

/// Three-counter stats strip: enrolled / completed / certificates.
class StatsRowWidget extends StatelessWidget {
  const StatsRowWidget({
    required this.enrolledCount,
    required this.completedCount,
    required this.certificatesCount,
    super.key,
  });

  final int enrolledCount;
  final int completedCount;
  final int certificatesCount;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          _StatItem(
            value: enrolledCount,
            label: 'Enrolled',
            icon: Icons.school_outlined,
          ),
          const SizedBox(width: 12),
          _StatItem(
            value: completedCount,
            label: 'Completed',
            icon: Icons.check_circle_outline,
          ),
          const SizedBox(width: 12),
          _StatItem(
            value: certificatesCount,
            label: 'Certs',
            icon: Icons.workspace_premium_outlined,
          ),
        ],
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  const _StatItem({
    required this.value,
    required this.label,
    required this.icon,
  });

  final int value;
  final String label;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cs = theme.colorScheme;
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        decoration: BoxDecoration(
          color: cs.surface,
          border: Border.all(color: cs.outline),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: AppColors.accentPrimary, size: 22),
            const SizedBox(height: 6),
            Text(
              '$value',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: cs.onSurface,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: theme.hintColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
