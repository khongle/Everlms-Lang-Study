import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

enum NoDataType {
  cart,
  notification,
  booking,
  coupon,
  others,
  home,
  offers,
  address,
  search,
  course,
  inbox,
  category,
  certificate
}

class NoDataScreen extends StatelessWidget {
  const NoDataScreen({
    required this.text,
    super.key,
    this.type,
    this.isShowHomePage = true,
  });
  final NoDataType? type;
  final String? text;
  final bool isShowHomePage;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (type != NoDataType.certificate)
          Container(
            padding: EdgeInsets.all(MediaQuery.of(context).size.height * 0.03),
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
            ),
            child: SvgPicture.asset(
              (type == NoDataType.cart || type == NoDataType.booking)
                  ? Images.cart
                  : type == NoDataType.notification
                      ? Images.notification
                      : type == NoDataType.search
                          ? Images.notification
                          : type == NoDataType.inbox
                              ? Images.chat
                              : type == NoDataType.offers
                                  ? Images.search
                                  : Images.search,
              width: type == NoDataType.coupon
                  ? 50.0
                  : MediaQuery.of(context).size.height * 0.12,
              height: type == NoDataType.coupon
                  ? 50.0
                  : MediaQuery.of(context).size.height * 0.12,
              colorFilter: ColorFilter.mode(
                (Theme.of(context).brightness == Brightness.dark) &&
                        (type == NoDataType.booking ||
                            type == NoDataType.home ||
                            type == NoDataType.course ||
                            type == NoDataType.category ||
                            type == NoDataType.notification)
                    ? Theme.of(context).primaryColorLight
                    : Theme.of(context)
                        .textTheme
                        .bodyLarge!
                        .color!
                        .withValues(alpha: .6),
                BlendMode.srcIn,
              ),
            ),
          ),
        Text(
          type == NoDataType.cart
              ? tl(context).your_cart_is_empty
              : type == NoDataType.booking
                  ? tl(context).sorry_your_order_history_is_Empty
                  : type == NoDataType.notification
                      ? tl(context).empty_notifications
                      : text!,
          style: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeDefault,
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: .6),
          ),
          textAlign: TextAlign.center,
        ),
        SizedBox(height: MediaQuery.of(context).size.height * 0.04),
        if (type != NoDataType.category &&
            type != NoDataType.inbox &&
            type != NoDataType.booking &&
            type != NoDataType.course &&
            type != NoDataType.search &&
            type != NoDataType.offers &&
            type != NoDataType.cart &&
            type != NoDataType.home &&
            type != NoDataType.coupon &&
            isShowHomePage)
          CustomButton(
            height: 40,
            width: 200,
            buttonText: tl(context).back_to_homepage,
            onPressed: () =>
                context.go(AppRoutes.getMainRoute('no-data-screen')),
          ),
      ],
    );
  }
}
