import 'package:flutter/material.dart';

class TabBarWidget extends StatelessWidget {
  const TabBarWidget({
    required this.tabController,
    required this.listTabLabel,
    required this.onTap,
    super.key,
  });
  final TabController? tabController;
  final List<String>? listTabLabel;
  final Function(int value)? onTap;

  @override
  Widget build(BuildContext context) {
    return TabBar(
      controller: tabController,
      onTap: (value) => onTap?.call(value),
      dividerHeight: 0.3,
      indicatorColor: Theme.of(context).primaryColor,
      indicatorSize: TabBarIndicatorSize.tab,
      labelColor: Theme.of(context).primaryColor,
      overlayColor: WidgetStateProperty.all(Colors.transparent),
      tabAlignment: TabAlignment.fill,
      labelStyle: Theme.of(context).textTheme.bodyMedium,
      tabs: List.generate(listTabLabel?.length ?? 0, (index) {
        return Tab(text: listTabLabel?[index] ?? '');
      }),
    );
  }
}
