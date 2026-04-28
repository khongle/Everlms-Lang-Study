import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/cart_widget.dart';
import 'package:flutter/material.dart';

class CustomAppBar extends StatelessWidget implements PreferredSizeWidget {
  const CustomAppBar({
    super.key,
    this.title = '',
    this.isBackButtonExist = true,
    this.onBackPressed,
    this.showCart = false,
    this.centerTitle = true,
    this.bgColor,
    this.titleColor,
    this.actions = const [SizedBox()],
  });
  final String? title;
  final bool isBackButtonExist;
  final Function()? onBackPressed;
  final bool? showCart;
  final bool? centerTitle;
  final Color? bgColor;
  final Color? titleColor;
  final List<Widget>? actions;

  @override
  Widget build(BuildContext context) {
    return AppBar(
      title: Padding(
        padding: const EdgeInsets.only(right: Dimensions.paddingSizeDefault),
        child: Text(
          title!,
          overflow: TextOverflow.clip,
          softWrap: true,
          style: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeLarge,
            color: titleColor ?? Theme.of(context).colorScheme.onSurface,
          ),
        ),
      ),
      centerTitle: centerTitle,
      titleSpacing: isBackButtonExist ? 5 : 10,
      leading: isBackButtonExist
          ? IconButton(
              highlightColor: Colors.transparent,
              icon: const Icon(Icons.arrow_back),
              style: IconButton.styleFrom(
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              onPressed: () => onBackPressed != null
                  ? onBackPressed!()
                  : Navigator.pop(context),
            )
          : Container(),
      backgroundColor: bgColor ?? Theme.of(context).colorScheme.surface,
      elevation: 0,
      actions: showCart!
          ? [
              IconButton(
                onPressed: () {},
                icon: CartWidget(
                  color: Theme.of(context).brightness == Brightness.dark
                      ? Theme.of(context).primaryColorLight
                      : Colors.white,
                  size: Dimensions.cartWidgetSize,
                ),
              ),
            ]
          : actions,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(Dimensions.preferredSize);
}
