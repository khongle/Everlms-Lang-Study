import 'package:everlms/core/widgets/custom_image.dart';
import 'package:flutter/material.dart';

class PagerContent extends StatelessWidget {
  const PagerContent({required this.image, super.key});

  final String image;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const Spacer(),
        CustomImage(image: image),
        const SizedBox(height: 80),
      ],
    );
  }
}
