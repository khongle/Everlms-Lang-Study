import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/membership/data/models/membership_model.dart';
import 'package:flutter/material.dart';

/// Card hiển thị một gói thành viên trong màn pricing.
///
/// Mockup ref: `.agent/docs/mobile/mockup/components.md` §MembershipPlanCard
class MembershipPlanCard extends StatelessWidget {
  const MembershipPlanCard({
    required this.plan,
    required this.onSelect,
    super.key,
    this.isBestValue = false,
    this.isSelected = false,
    this.isYearly = false,
  });

  final MembershipModel plan;
  final bool isBestValue;
  final bool isSelected;
  final bool isYearly;
  final VoidCallback onSelect;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final borderColor =
        isSelected ? colorScheme.primary : colorScheme.outlineVariant;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color: isSelected
            ? colorScheme.primaryContainer
            : colorScheme.surfaceContainerLow,
        borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        border: Border.all(color: borderColor, width: isSelected ? 2 : 1),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (isBestValue) _BestValueBadge(),
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  plan.title ?? '',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w700,
                        color: isSelected
                            ? colorScheme.primary
                            : colorScheme.onSurface,
                      ),
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                _PriceRow(plan: plan, isYearly: isYearly),
                if (plan.fieldSubtitle != null) ...[
                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                  Text(
                    plan.fieldSubtitle!,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: colorScheme.onSurfaceVariant,
                        ),
                  ),
                ],
                const SizedBox(height: Dimensions.paddingSizeDefault),
                if (plan.body != null) _FeatureList(body: plan.body!),
                const SizedBox(height: Dimensions.paddingSizeLarge),
                CustomButton(
                  buttonText: isSelected ? 'Đang chọn' : 'Chọn gói',
                  onPressed: isSelected ? null : onSelect,
                  bgColor: isSelected ? colorScheme.primary : null,
                  radius: Dimensions.radiusDefault,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _BestValueBadge extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.primary,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(Dimensions.radiusLarge),
          topRight: Radius.circular(Dimensions.radiusLarge),
        ),
      ),
      padding: const EdgeInsets.symmetric(vertical: 6),
      alignment: Alignment.center,
      child: Text(
        '★ BEST VALUE',
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: Theme.of(context).colorScheme.onPrimary,
              fontWeight: FontWeight.w700,
              letterSpacing: 1,
            ),
      ),
    );
  }
}

class _PriceRow extends StatelessWidget {
  const _PriceRow({required this.plan, required this.isYearly});

  final MembershipModel plan;
  final bool isYearly;

  @override
  Widget build(BuildContext context) {
    final price = plan.priceNumber ?? '0';
    final listPrice = plan.listPriceNumber;
    final hasDiscount =
        listPrice != null && listPrice != price && listPrice != '0';

    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Text(
          '\$$price',
          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.w800,
                color: Theme.of(context).colorScheme.primary,
              ),
        ),
        const SizedBox(width: 4),
        Padding(
          padding: const EdgeInsets.only(bottom: 3),
          child: Text(
            isYearly ? '/năm' : '/tháng',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
          ),
        ),
        if (hasDiscount) ...[
          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
          Padding(
            padding: const EdgeInsets.only(bottom: 3),
            child: Text(
              '\$$listPrice',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    decoration: TextDecoration.lineThrough,
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
            ),
          ),
        ],
      ],
    );
  }
}

class _FeatureList extends StatelessWidget {
  const _FeatureList({required this.body});

  final String body;

  @override
  Widget build(BuildContext context) {
    // body thường chứa HTML; tách từng dòng thành feature item
    final lines = body
        .replaceAll(RegExp('<[^>]+>'), '')
        .split('\n')
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: lines.map((line) {
        return Padding(
          padding: const EdgeInsets.symmetric(
            vertical: Dimensions.paddingSizeMint,
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(
                Icons.check_circle_outline,
                size: 16,
                color: Theme.of(context).colorScheme.primary,
              ),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Expanded(
                child: Text(
                  line,
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}
