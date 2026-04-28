import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

/// Rating summary widget: shows average star + distribution bars.
///
/// Mockup ref: `.agent/docs/mobile/mockup/components.md` §RatingSummary
class RatingSummary extends StatelessWidget {
  const RatingSummary({
    required this.average,
    required this.counts,
    required this.total,
    super.key,
  });

  /// Average score 0.0–5.0.
  final double average;

  /// Per-star counts: index 0 = 1-star, index 4 = 5-star.
  final List<int> counts;

  /// Total review count.
  final int total;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _AverageBlock(average: average, total: total),
        const SizedBox(width: Dimensions.paddingSizeLarge),
        Expanded(child: _DistributionBars(counts: counts, total: total)),
      ],
    );
  }
}

class _AverageBlock extends StatelessWidget {
  const _AverageBlock({required this.average, required this.total});

  final double average;
  final int total;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          average.toStringAsFixed(1),
          style: Theme.of(context).textTheme.displaySmall?.copyWith(
                fontWeight: FontWeight.w800,
                color: AppColors.ratingAmber,
              ),
        ),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(5, (i) {
            final filled = i < average.floor();
            final half = !filled && i == average.floor() && (average - average.floor()) >= 0.5;
            return Icon(
              filled
                  ? Icons.star
                  : half
                      ? Icons.star_half
                      : Icons.star_border,
              color: AppColors.ratingAmber,
              size: 14,
            );
          }),
        ),
        const SizedBox(height: 4),
        Text(
          '$total đánh giá',
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }
}

class _DistributionBars extends StatelessWidget {
  const _DistributionBars({required this.counts, required this.total});

  final List<int> counts;
  final int total;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (i) {
        final star = 5 - i; // 5 → 4 → 3 → 2 → 1
        final count = counts.length > (star - 1) ? counts[star - 1] : 0;
        final ratio = total > 0 ? count / total : 0.0;
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 2),
          child: Row(
            children: [
              Text(
                '$star',
                style: Theme.of(context).textTheme.bodySmall,
              ),
              const SizedBox(width: 4),
              const Icon(Icons.star, size: 12, color: AppColors.ratingAmber),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Expanded(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  child: LinearProgressIndicator(
                    value: ratio,
                    minHeight: 8,
                    backgroundColor:
                        Theme.of(context).colorScheme.surfaceContainerHigh,
                    valueColor: const AlwaysStoppedAnimation<Color>(
                      AppColors.ratingAmber,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              SizedBox(
                width: 30,
                child: Text(
                  '$count',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color:
                            Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                  textAlign: TextAlign.end,
                ),
              ),
            ],
          ),
        );
      }),
    );
  }
}
