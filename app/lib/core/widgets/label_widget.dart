import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class LabelWidget extends StatelessWidget {
  const LabelWidget({
    required this.label,
    super.key,
    this.color,
    this.isCourseBestseller = false,
  });
  final String? label;
  final Color? color;
  final bool isCourseBestseller;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsetsDirectional.symmetric(
        vertical: Dimensions.paddingSizeMint,
        horizontal: Dimensions.paddingSizeExtraSmall,
      ),
      margin: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        color: isCourseBestseller
            ? Colors.red.shade400
            : color ?? Theme.of(context).primaryColor,
      ),
      child: Text(
        label!.toUpperCase(),
        style: Theme.of(context).textTheme.labelMedium!.copyWith(
              color: Theme.of(context).colorScheme.surface,
              fontWeight: FontWeight.bold,
            ),
      ),
    );
  }
}
