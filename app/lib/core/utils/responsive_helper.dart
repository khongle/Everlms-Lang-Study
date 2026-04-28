import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';

class ResponsiveHelper {
  static bool isMobilePhone() => !kIsWeb;

  static bool isMobile(BuildContext context) =>
      MediaQuery.of(context).size.width < 650 || !kIsWeb;

  static bool isTab(BuildContext context) {
    final size = MediaQuery.of(context).size.width;
    return size >= 650 && size < 1300;
  }
}
