import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/cart/data/models/cart.dart';
import 'package:everlms/feature/cart/data/models/cart_list_data.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class CartItem extends ConsumerStatefulWidget {
  const CartItem({
    required this.cart,
    required this.variation,
    required this.index,
    required this.course,
    super.key,
  });
  final Cart cart;
  final int index;
  final Course course;
  final Variation? variation;

  @override
  ConsumerState<CartItem> createState() => _CartItemState();
}

class _CartItemState extends ConsumerState<CartItem> {
  late final TextEditingController _couponInputController;

  @override
  void initState() {
    super.initState();
    _couponInputController = TextEditingController();
  }

  @override
  void dispose() {
    _couponInputController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider).valueOrNull ?? const CartState();
    return item(context, cartState);
  }

  Widget item(BuildContext context, CartState cartState) {
    return Card(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (widget.course.fieldImage != null)
                Padding(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  child: ClipRRect(
                    borderRadius: const BorderRadius.all(
                      Radius.circular(Dimensions.radiusSmall),
                    ),
                    child: CustomImage(
                      image: widget.course.fieldImage ?? '',
                      height: 70,
                      width: 100,
                      fit: BoxFit.contain,
                    ),
                  ),
                ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        spacing: Dimensions.paddingSizeDefault,
                        children: [
                          Expanded(
                            child: Text(
                              widget.course.title ?? '',
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              textScaler: MediaQuery.of(context).textScaler,
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(
                        height: Dimensions.paddingSizeExtraSmall,
                      ),
                      Wrap(
                        spacing: 50,
                        runSpacing: Dimensions.paddingSizeSmall,
                        children: [
                          textCustom(
                            context,
                            widget.course.fieldTotalHours ?? '',
                            tl(context).hours,
                            Images.timer,
                          ),
                          textCustom(
                            context,
                            widget.course.totalQuizzes ?? '',
                            tl(context).quizzes,
                            Images.quiz,
                          ),
                          textCustom(
                            context,
                            widget.course.totalLessons?.toString() ?? '',
                            tl(context).lessons,
                            Images.book,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          if (cartState.selectedCartItemIndex == widget.index)
            widget.cart.couponApplied == false
                ? Column(
                    children: [
                      Divider(
                        thickness: 1,
                        color: Theme.of(context)
                            .textTheme
                            .bodyLarge!
                            .color!
                            .withValues(alpha: 0.06),
                      ),
                      Padding(
                        padding: const EdgeInsets.only(
                          bottom: Dimensions.paddingSizeDefault,
                          left: Dimensions.paddingSizeDefault,
                          right: Dimensions.paddingSizeDefault,
                          top: Dimensions.paddingSizeExtraSmall,
                        ),
                        child: Container(
                          decoration: BoxDecoration(
                            border: Border.all(
                              color: Theme.of(context)
                                  .textTheme
                                  .bodyLarge!
                                  .color!
                                  .withValues(alpha: 0.06),
                            ),
                            borderRadius: const BorderRadius.all(
                              Radius.circular(Dimensions.radiusSmall),
                            ),
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: _couponInputController,
                                  style: const TextStyle(
                                    fontSize: Dimensions.paddingSizeDefault,
                                  ),
                                  decoration: InputDecoration(
                                    border: InputBorder.none,
                                    hintText: 'Coupon Code',
                                    filled: true,
                                    fillColor: Theme.of(context).cardColor,
                                  ),
                                ),
                              ),
                              InkWell(
                                onTap: () =>
                                    ref.read(cartProvider.notifier).applyCoupon(
                                          code: _couponInputController.text,
                                          type: widget.cart.type ?? '',
                                          id: widget.cart.id.toString(),
                                        ),
                                child: Container(
                                  width: 88,
                                  height: 47,
                                  alignment: Alignment.center,
                                  decoration: BoxDecoration(
                                    color: Theme.of(context).primaryColor,
                                    borderRadius: const BorderRadius.only(
                                      topRight: Radius.circular(
                                        Dimensions.radiusSmall,
                                      ),
                                      bottomRight: Radius.circular(
                                        Dimensions.radiusSmall,
                                      ),
                                    ),
                                  ),
                                  child: cartState.isApplyCouponLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: LoadingIndicator(
                                            color: Colors.white,
                                          ),
                                        )
                                      : Text(
                                          tl(context).apply,
                                          style: robotoRegular.copyWith(
                                            color: Theme.of(context)
                                                .primaryColorLight,
                                            fontSize: Dimensions.fontSizeSmall,
                                          ),
                                        ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  )
                : const SizedBox(),
        ],
      ),
    );
  }

  Widget textCustom(
    BuildContext context,
    String value,
    String label,
    String img,
  ) {
    return value.isNotEmpty
        ? Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              SvgPicture.asset(
                img,
                width: 15,
                height: 15,
                fit: BoxFit.cover,
                colorFilter: ColorFilter.mode(
                  Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: 0.6),
                  BlendMode.srcIn,
                ),
              ),
              const SizedBox(
                width: Dimensions.paddingSizeExtraSmall,
              ),
              Text(
                '$value $label',
                style: Theme.of(context).textTheme.labelSmall,
              ),
            ],
          )
        : Container();
  }
}
