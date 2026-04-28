import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/widgets/custom_alert_dialog.dart';
import 'package:flutter/material.dart';

class DialogManager {
  DialogManager({required this.context});
  BuildContext context;

  Future<void> dialogInvalidEmail() {
    return customAlertDialog(
      context,
      tl(context).invalid_email,
      tl(context).invalid_email_message,
    );
  }

  Future<void> dialogPasswordMismatch() {
    return customAlertDialog(
      context,
      tl(context).password_mismatch,
      tl(context).password_mismatch_message,
    );
  }

  Future<void> dialogUserNotFound() {
    return customAlertDialog(
      context,
      tl(context).account_not_found,
      tl(context).account_not_found_message,
    );
  }

  Future<void> dialogUserFoundSignUp() {
    return customAlertDialog(
      context,
      tl(context).account_exists,
      tl(context).account_exists_message,
    );
  }

  Future<void> dialogUserBlocked() {
    return customAlertDialog(
      context,
      tl(context).account_block,
      tl(context).account_block_message,
    );
  }
}
