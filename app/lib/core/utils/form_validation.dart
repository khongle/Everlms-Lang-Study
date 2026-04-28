import 'package:email_validator/email_validator.dart';
import 'package:everlms/core/localization/tl.dart';

class FormValidation {
  String? _validateMinLength(String value, int minLength, String message) =>
      value.length <= minLength ? message : null;

  String? isValidLength(String value) =>
      _validateMinLength(value, 2, tlCtx.enter_valid_information);

  String? isValidFirstName(String value) =>
      _validateMinLength(value, 2, tlCtx.enter_your_first_name);

  String? isValidLastName(String value) =>
      _validateMinLength(value, 2, tlCtx.enter_your_last_name);

  String? isValidUserName(String value) =>
      _validateMinLength(value, 2, tlCtx.enter_your_user_name);

  String? isValidPassword(String value) =>
      _validateMinLength(value, 7, tlCtx.password_should_be);

  bool isValidEmail(String? email) {
    return EmailValidator.validate(email ?? '');
  }
}
