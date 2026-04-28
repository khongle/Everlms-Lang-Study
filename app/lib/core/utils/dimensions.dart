import 'package:everlms/main.dart' show navigatorKey;
import 'package:flutter/widgets.dart';

class Dimensions {
  static double get _screenWidth {
    final ctx = navigatorKey.currentContext;
    if (ctx != null) return MediaQuery.of(ctx).size.width;
    return 0;
  }

  static double get fontSizeExtraSmall => _screenWidth >= 1300 ? 14 : 10;
  static double get fontSizeSmall => _screenWidth >= 1300 ? 15 : 12;
  static double get fontSizeSemiSmall => _screenWidth >= 1300 ? 15 : 13;
  static double get fontSizeDefault => _screenWidth >= 1300 ? 17 : 14;
  static double get fontSizeLarge => _screenWidth >= 1300 ? 20 : 16;
  static double get fontSizeExtraLarge => _screenWidth >= 1300 ? 20 : 18;
  static double get fontSizeOverLarge => _screenWidth >= 1300 ? 28 : 24;
  static double get fontSizeForReview => 36;

  //cart item height
  static double get cartItemHeight => _screenWidth >= 1300 ? 150 : 110;

  static const double paddingSizeMint = 2;
  static const double paddingSizeExtraSmall = 5;
  static const double paddingSizeRadius = 8;
  static const double paddingSizeSmall = 10;
  static const double paddingSizeDefault = 15;
  static const double paddingSizeLarge = 20;
  static const double paddingSizeExtraLarge = 25;
  static const double paddingSizeExtraMoreLarge = 40;

  static const double pagesBottomPadding = 100;
  static const double logoSize = 200;
  static const double cartWidgetSize = 30;
  static const double customAppBarSize = 70;
  static const double preferredSize = 50;
  static const double addressItemHeight = 100;

  static const double radiusSmall = 5;
  static const double radiusDefault = 10;
  static const double radiusLarge = 15;
  static const double radiusExtraLarge = 20;
  static const double radiusExtraMoreLarge = 50;

  // training
  static const double iconSizeDefault = 16;
  static const double iconSizeLarge = 20;
  static const double iconSizeExtraLarge = 24;

  // static const double WEB_MAX_WIDTH = 1170;
  static const double tabMinimumSize = 650;

  static const double searchBerSize = 55;
  static const double floatingButtonHeight = 35;
  static const double cartDialogPadding = 80;
  static const double addAddressHeight = 60;
  static const double addAddressWidth = 160;
  static const double supportLogoHeight = 242;
  static const double supportLogoWidth = 200;
}
