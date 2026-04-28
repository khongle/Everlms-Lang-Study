import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class PagerDot extends StatelessWidget {
  const PagerDot({required this.index, required this.currentIndex, super.key});
  final int index;
  final int currentIndex;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      margin: const EdgeInsets.only(right: 8),
      height: Dimensions.paddingSizeExtraSmall,
      width: currentIndex == index
          ? Dimensions.paddingSizeLarge
          : Dimensions.paddingSizeExtraSmall,
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor,
        borderRadius: BorderRadius.circular(5),
      ),
    );
  }
}
