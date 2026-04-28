import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';

class EmptyStateWidget extends StatelessWidget {
  const EmptyStateWidget({
    required this.message,
    this.onReset,
    this.resetLabel,
    super.key,
  });

  final String message;
  final VoidCallback? onReset;
  final String? resetLabel;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.search_off_rounded,
              size: 64,
              color: AppColors.textMuted,
            ),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 15,
                color: AppColors.textSecondary,
              ),
            ),
            if (onReset != null) ...[
              const SizedBox(height: 20),
              TextButton(
                onPressed: onReset,
                child: Text(
                  resetLabel ?? 'Reset filters',
                  style: const TextStyle(color: AppColors.accentPrimary),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
