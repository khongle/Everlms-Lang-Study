import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class CustomButton extends StatelessWidget {
  const CustomButton({
    required this.buttonText,
    super.key,
    this.onPressed,
    this.transparent = false,
    this.bgColor,
    this.margin,
    this.width,
    this.height,
    this.fontSize,
    this.radius = 5,
    this.icon,
    this.isActive,
    this.isRightIcon = false,
  });
  final Function()? onPressed;
  final bool transparent;
  final Color? bgColor;
  final String? buttonText;
  final EdgeInsets? margin;
  final double? height;
  final double? width;
  final double? fontSize;
  final double? radius;
  final IconData? icon;
  final bool isRightIcon;
  final bool? isActive;

  @override
  Widget build(BuildContext context) {
    final flatButtonStyle = TextButton.styleFrom(
      backgroundColor: transparent
          ? Colors.transparent
          : bgColor ??
              ((isActive ?? true)
                  ? Theme.of(context).primaryColor
                  : Theme.of(context).primaryColor.withValues(alpha: 0.6)),
      // backgroundColor: onPressed != null
      //     ? bgColor ?? Theme.of(context).primaryColor
      //     : Theme.of(context).primaryColor,
      minimumSize: Size(
        width != null ? width! : MediaQuery.of(context).size.width,
        height != null ? height! : 45,
      ),
      padding: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        side: BorderSide(
          color: transparent
              ? Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6)
              : Colors.transparent,
        ),
        borderRadius: BorderRadius.circular(radius!),
      ),
    );

    return SizedBox(
      width: width ?? MediaQuery.of(context).size.width,
      child: Padding(
        padding: margin ??
            const EdgeInsets.fromLTRB(
              Dimensions.paddingSizeLarge,
              0,
              Dimensions.paddingSizeLarge,
              Dimensions.paddingSizeExtraLarge,
            ),
        child: TextButton(
          onPressed: (isActive ?? true) ? onPressed : null,
          style: flatButtonStyle,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: isRightIcon
                ? [
                    titleBtn(context),
                    if (icon != null) iconBtn(context) else const SizedBox(),
                  ]
                : [
                    if (icon != null) iconBtn(context) else const SizedBox(),
                    titleBtn(context),
                  ],
          ),
        ),
      ),
    );
  }

  Widget iconBtn(BuildContext context) {
    return Icon(
      icon,
      color: Colors.white,
      size: 18,
    );
  }

  Widget titleBtn(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeExtraSmall,
      ),
      child: Text(
        buttonText ?? '',
        textAlign: TextAlign.center,
        style: Theme.of(context).textTheme.titleMedium!.copyWith(
              color: transparent
                  ? Theme.of(context).textTheme.titleMedium!.color
                  : Colors.white,
            ),
      ),
    );
  }
}
