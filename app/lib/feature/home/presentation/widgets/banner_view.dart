import 'package:flutter/material.dart';

// NOTE: BannerView depends on homeModel which was dead code in old HomeController.
// Converted to a simple stateful widget. Will not display until homeModel is re-enabled.
class BannerView extends StatefulWidget {
  const BannerView({required this.bannerIndex, super.key});
  final int bannerIndex;

  @override
  State<BannerView> createState() => _BannerViewState();
}

class _BannerViewState extends State<BannerView> {
  @override
  Widget build(BuildContext context) {
    return const SizedBox();
  }
}
