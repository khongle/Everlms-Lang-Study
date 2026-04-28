import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/coupon/data/models/coupon.dart';
import 'package:everlms/feature/coupon/presentation/providers/coupon_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CouponScreen extends ConsumerWidget {
  const CouponScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncState = ref.watch(couponProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).coupon,
        bgColor: Theme.of(context).cardColor,
        titleColor: Theme.of(context).colorScheme.onSurface,
      ),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (err, _) => Center(child: Text(err.toString())),
        data: (state) => state.coupons.isEmpty
            ? Center(child: Text(tl(context).no_data_found))
            : _couponList(context, ref, state.coupons),
      ),
    );
  }

  Widget _couponList(BuildContext context, WidgetRef ref, List<Coupon> list) {
    return Column(
      children: [
        SizedBox(
          height: 150,
          width: MediaQuery.of(context).size.width,
          child: Image.asset(Images.placeholder, fit: BoxFit.fitWidth),
        ),
        Expanded(
          child: ListView.builder(
            itemCount: list.length,
            shrinkWrap: true,
            itemBuilder: (context, index) =>
                _couponItem(index, context, ref, list[index]),
          ),
        ),
      ],
    );
  }

  Widget _couponItem(
    int index,
    BuildContext context,
    WidgetRef ref,
    Coupon coupon,
  ) {
    return Padding(
      padding: EdgeInsets.only(
        left: Dimensions.paddingSizeDefault,
        right: Dimensions.paddingSizeDefault,
        bottom: Dimensions.paddingSizeExtraSmall,
        top: index != 0
            ? Dimensions.paddingSizeExtraSmall
            : Dimensions.paddingSizeDefault,
      ),
      child: Container(
        alignment: Alignment.center,
        decoration: BoxDecoration(
          border: Border.all(color: Theme.of(context).primaryColorLight),
          color: Colors.white,
          borderRadius: const BorderRadius.all(
            Radius.circular(Dimensions.radiusSmall),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding:
                    const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
                child: ClipRRect(
                  borderRadius: const BorderRadius.all(Radius.circular(5)),
                  child: Image.network(
                    coupon.image ?? '',
                    height: 60,
                    width: 60,
                    errorBuilder: (_, __, ___) => Image.asset(
                      Images.placeholder,
                      height: 60,
                      width: 60,
                    ),
                  ),
                ),
              ),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          coupon.code ?? '',
                          style: robotoMedium.copyWith(
                            color: Theme.of(context).colorScheme.onSurface,
                            fontSize: Dimensions.fontSizeDefault,
                          ),
                        ),
                        Text(
                          coupon.discountType == 'flat'
                              ? '\$${coupon.discount}'
                              : '${coupon.discount}%',
                          style: robotoMedium.copyWith(
                            color: Colors.red,
                            fontSize: Dimensions.fontSizeSemiSmall,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: Dimensions.paddingSizeRadius),
                    Text(
                      coupon.title ?? '',
                      style: robotoMedium.copyWith(
                        color: Theme.of(context)
                            .textTheme
                            .bodyLarge!
                            .color!
                            .withValues(alpha: 0.6),
                        fontSize: Dimensions.fontSizeExtraSmall,
                      ),
                    ),
                    const SizedBox(height: Dimensions.paddingSizeRadius),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Ended: ${coupon.endDate}',
                          style: robotoRegular.copyWith(
                            color: Theme.of(context)
                                .textTheme
                                .bodyLarge!
                                .color!
                                .withValues(alpha: 0.6),
                            fontSize: Dimensions.fontSizeExtraSmall,
                          ),
                        ),
                        InkWell(
                          onTap: () {
                            ref
                                .read(couponProvider.notifier)
                                .copyCodeToClipBoard(coupon.code.toString());
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(tl(context).copied_to_clipboard),
                              ),
                            );
                          },
                          child: Text(
                            tl(context).copy,
                            style: robotoSemiBold.copyWith(
                              color: Theme.of(context).primaryColor,
                              fontSize: Dimensions.fontSizeExtraSmall,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
