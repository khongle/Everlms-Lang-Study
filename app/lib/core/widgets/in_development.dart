import 'package:everlms/core/theme/text_styles.dart';
import 'package:flutter/material.dart';

class InDevelopment extends StatelessWidget {
  const InDevelopment({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text(
        'Feature under development. Please check back later.',
        style: robotoRegular,
      ),
    );
  }
}
