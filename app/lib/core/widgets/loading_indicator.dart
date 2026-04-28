import 'package:flutter/material.dart';

class LoadingIndicator extends StatelessWidget {
  const LoadingIndicator({
    super.key,
    this.color,
    this.strokeWidth = 4,
    this.height,
  });
  final Color? color;
  final double strokeWidth;
  final double? height;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SizedBox(
        width: height,
        height: height,
        child: CircularProgressIndicator(
          strokeWidth: strokeWidth,
          strokeCap: StrokeCap.round,
          color: color,
          backgroundColor: Theme.of(context).highlightColor,
        ),
      ),
    );
  }
}
