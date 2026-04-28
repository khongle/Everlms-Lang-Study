import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:flutter/material.dart';

/// Card hiển thị gói thành viên đang hoạt động của user.
///
/// Mockup ref: `.agent/docs/mobile/mockup/components.md` §ActiveMembershipCard
class ActiveMembershipCard extends StatelessWidget {
  const ActiveMembershipCard({
    required this.planName,
    required this.expiryDate,
    required this.totalDays,
    required this.remainingDays,
    required this.onRenew,
    required this.onCancel,
    super.key,
  });

  final String planName;

  /// Formatted date string, e.g. "15/06/2026".
  final String expiryDate;

  /// Total days in the billing period (e.g. 365 or 30).
  final int totalDays;

  /// Days remaining until expiry.
  final int remainingDays;

  final VoidCallback onRenew;
  final VoidCallback onCancel;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final progress = totalDays > 0
        ? ((totalDays - remainingDays) / totalDays).clamp(0.0, 1.0)
        : 0.0;
    final isExpiringSoon = remainingDays <= 7;

    return Container(
      decoration: BoxDecoration(
        color: colorScheme.surfaceContainerLow,
        borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        border: Border.all(
          color: isExpiringSoon
              ? colorScheme.error.withValues(alpha: 0.5)
              : colorScheme.outlineVariant,
        ),
      ),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  'Gói $planName',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                ),
              ),
              _StatusChip(isExpiringSoon: isExpiringSoon),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Text(
            'Hết hạn: $expiryDate',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: isExpiringSoon
                      ? colorScheme.error
                      : colorScheme.onSurfaceVariant,
                ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          ClipRRect(
            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 8,
              backgroundColor: colorScheme.surfaceContainerHigh,
              valueColor: AlwaysStoppedAnimation<Color>(
                isExpiringSoon ? colorScheme.error : colorScheme.primary,
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '$remainingDays/$totalDays ngày còn lại',
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  color: colorScheme.onSurfaceVariant,
                ),
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Row(
            children: [
              Expanded(
                child: CustomButton(
                  buttonText: 'Gia hạn',
                  onPressed: onRenew,
                  radius: Dimensions.radiusDefault,
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              TextButton(
                onPressed: onCancel,
                child: Text(
                  'Huỷ gói',
                  style: TextStyle(color: colorScheme.error),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.isExpiringSoon});

  final bool isExpiringSoon;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: isExpiringSoon
            ? colorScheme.errorContainer
            : colorScheme.primaryContainer,
        borderRadius: BorderRadius.circular(Dimensions.radiusExtraMoreLarge),
      ),
      child: Text(
        isExpiringSoon ? 'Sắp hết hạn' : 'Đang hoạt động',
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: isExpiringSoon
                  ? colorScheme.onErrorContainer
                  : colorScheme.onPrimaryContainer,
              fontWeight: FontWeight.w600,
            ),
      ),
    );
  }
}
