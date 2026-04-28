import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/profile/presentation/widgets/user_logout.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class OtherBenefit extends StatelessWidget {
  const OtherBenefit({super.key});

  List _buildList(BuildContext context) => [
        {'icon': Images.changePassword, 'title': tl(context).changePassword},
        {'icon': Images.user, 'title': tl(context).membership},

        // {"icon": Images.favorite, "title": tl(context).favorite},
        {'icon': Images.profileNotification, 'title': tl(context).notification},
        {'icon': Images.orderHistory, 'title': tl(context).orderHistory},
        {'icon': Images.voucher, 'title': tl(context).voucher},
        {'icon': Images.meeting, 'title': tl(context).meeting},
        {'icon': Images.profileLogout, 'title': tl(context).logout},
      ];

  @override
  Widget build(BuildContext context) {
    final list = _buildList(context);
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Text(
              tl(context).otherBenefit,
              style:
                  robotoSemiBold.copyWith(fontSize: Dimensions.fontSizeDefault),
            ),
          ),
          Expanded(
            child: ListView.builder(
              padding: EdgeInsets.zero,
              itemCount: list.length,
              physics: const NeverScrollableScrollPhysics(),
              itemBuilder: (context, index) {
                return Column(
                  children: [
                    InkWell(
                      onTap: () {
                        if (index == 0) {
                          context.push(AppRoutes.changePassword);
                        } else if (index == 1) {
                          // context.push(AppRoutes.certificateScreen);
                          // Navigator.of(context).push(MaterialPageRoute(builder: (_) => PayPalPayment(
                          //       title: "palpay",
                          //     ));
                          context.push(AppRoutes.membershipScreen);
                        } else if (index == 2) {
                          context.push(AppRoutes.notificationScreen);
                        } else if (index == 3) {
                          context.push(AppRoutes.orderHistoryScreen);
                        } else if (index == 4) {
                          context.push(AppRoutes.couponScreen);
                        } else if (index == 5) {
                          context.push(AppRoutes.meetingScreen);
                        } else if (index == 6) {
                          showModalBottomSheet(
                            context: context,
                            backgroundColor: Colors.transparent,
                            isScrollControlled: true,
                            builder: (_) => const UserLogout(),
                          );
                        }
                      },
                      child: Padding(
                        padding: const EdgeInsets.symmetric(
                          vertical: Dimensions.paddingSizeRadius,
                        ),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: Dimensions.paddingSizeDefault,
                          ),
                          child: Row(
                            children: [
                              SvgPicture.asset(
                                list[index]['icon'],
                                height: 14,
                                width: 12,
                                colorFilter: ColorFilter.mode(
                                  Theme.of(context).primaryColor,
                                  BlendMode.srcIn,
                                ),
                              ),
                              const SizedBox(
                                width: 12,
                              ),
                              Text(
                                list[index]['title'],
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    if ((index + 1) == (list.length))
                      const SizedBox()
                    else
                      Divider(
                        thickness: 1,
                        color: Theme.of(context)
                            .primaryColor
                            .withValues(alpha: 0.2),
                      ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
