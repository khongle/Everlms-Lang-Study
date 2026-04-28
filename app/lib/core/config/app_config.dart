import 'package:flutter_dotenv/flutter_dotenv.dart';

class Config {
  // Default account (set dynamically at runtime)
  static String defaultAccountMail = '';
  static String defaultAccountName = '';
  static String defaultAccountPass = '';

  // Default domain
  static const String defaultDomain = 'https://training.everlms.com/';
  static String baseUrl = 'https://everlms-pro.dev.weebpal.com/';

  static const String baseUrlCertificate = 'https://dev-product.everlms.com/';

  static const double appVersion = 1.1;
  static String oneSignalAppID = dotenv.env['ONESIGNAL_APP_ID'] ?? '';

  // Shield of Drupal
  static const String usernameLMS = 'weebpal';
  static const String passwordLMS = 'weebpal';
}
