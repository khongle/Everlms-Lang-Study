import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/main.dart' show navigatorKey;
import 'package:flutter/material.dart';

void customSnackBar(
  String? message, {
  bool isError = true,
  BuildContext? context,
}) {
  final ctx = context ?? navigatorKey.currentContext;
  if (message != null && message.isNotEmpty && ctx != null) {
    ScaffoldMessenger.of(ctx).showSnackBar(
      SnackBar(
        backgroundColor: isError ? Colors.red : Colors.green,
        content: Text(message),
        duration: const Duration(seconds: 2),
        margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
        dismissDirection: DismissDirection.horizontal,
      ),
    );
  }
}
