import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/in_development.dart';
import 'package:flutter/material.dart';

class OrderHistoryScreen extends StatelessWidget {
  const OrderHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).order_history,
        bgColor: Theme.of(context).cardColor,
        titleColor: Theme.of(context).colorScheme.onSurface,
      ),
      body: const InDevelopment(),
    );
  }
}
