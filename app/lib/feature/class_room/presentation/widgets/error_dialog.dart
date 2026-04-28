import 'package:everlms/core/widgets/custom_button.dart';
import 'package:flutter/material.dart';

class ErrorDialog {
  static bool _isShow = false;
  static void show(
    BuildContext? context,
    dynamic Function()? onPressed,
  ) {
    if (context != null && !_isShow) {
      _isShow = true;
      showDialog(
        context: context,
        barrierDismissible:
            false, // Prevents the dialog from being dismissed by tapping outside
        builder: (context) {
          return Dialog(
            backgroundColor: Colors.transparent,
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Align(
                    alignment: Alignment.topRight,
                    child: CloseButton(),
                  ),
                  const Text(
                    'Something went error, please try again!',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 18),
                  ),
                  const SizedBox(height: 7),
                  CustomButton(
                    buttonText: 'Try Again',
                    onPressed: onPressed,
                  ),
                ],
              ),
            ),
          );
        },
      );
    }
  }

  static void hide(BuildContext context) {
    _isShow = false;
    Navigator.of(context, rootNavigator: true).pop();
  }
}
