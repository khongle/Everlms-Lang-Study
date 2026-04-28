import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class CustomTabBarHeader extends StatelessWidget {
  const CustomTabBarHeader({
    required this.controller,
    required this.listTabs,
    super.key,
    this.scrollable,
    this.onTap,
    this.alignment = TabAlignment.fill,
  });

  final List<Widget> listTabs;
  final TabController? controller;
  final bool? scrollable;
  final TabAlignment? alignment;
  final ValueChanged<int>? onTap;

  @override
  Widget build(BuildContext context) {
    return TabBar(
      isScrollable: scrollable ?? true,
      controller: controller,
      onTap: onTap,
      tabAlignment: alignment,
      indicator: BoxDecoration(
        borderRadius:
            const BorderRadius.all(Radius.circular(Dimensions.radiusSmall)),
        color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.06),
      ),
      indicatorSize: TabBarIndicatorSize.tab,
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeExtraMoreLarge,
      ),
      dividerHeight: 0,
      indicatorAnimation: TabIndicatorAnimation.elastic,
      unselectedLabelColor:
          Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
      labelColor: Theme.of(context).colorScheme.onSurface,
      labelStyle: robotoBold.copyWith(
        color: Theme.of(context).cardColor,
        fontSize: Dimensions.fontSizeSmall,
      ),
      tabs: listTabs,
    );
  }
}
