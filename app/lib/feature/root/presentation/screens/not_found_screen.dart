import 'package:everlms/core/localization/tl.dart';
import 'package:flutter/material.dart';

class NotFoundScreen extends StatelessWidget {
  const NotFoundScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: TweenAnimationBuilder(
          curve: Curves.bounceOut,
          duration: const Duration(seconds: 2),
          tween: Tween<double>(begin: 12, end: 30),
          builder: (context, dynamic value, child) {
            return Text(
              tl(context).page_not_found,
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: value),
            );
          },
        ),
      ),
    );
  }
}
