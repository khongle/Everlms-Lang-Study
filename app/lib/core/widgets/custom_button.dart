import 'package:everlms/core/theme/app_colors.dart';
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
    final enabled = isActive ?? true;
    final buttonHeight = height ?? 48;
    final buttonWidth = width ?? MediaQuery.of(context).size.width;
    final buttonRadius = BorderRadius.circular(radius ?? 8);
    final borderColor =
        transparent ? AppColors.accentPrimary : Colors.transparent;
    final decoration = BoxDecoration(
      color:
          transparent ? Colors.transparent : bgColor ?? AppColors.accentPrimary,
      gradient: transparent || bgColor != null
          ? null
          : LinearGradient(
              colors: enabled
                  ? AppColors.gradientPrimary
                  : AppColors.gradientPrimary
                      .map((color) => color.withValues(alpha: 0.45))
                      .toList(),
            ),
      borderRadius: buttonRadius,
      border: Border.all(color: borderColor),
      boxShadow: transparent
          ? null
          : const [
              BoxShadow(
                color: Color(0x66000000),
                blurRadius: 12,
                offset: Offset(0, 4),
              ),
            ],
    );
    final flatButtonStyle = TextButton.styleFrom(
      backgroundColor: Colors.transparent,
      minimumSize: Size(buttonWidth, buttonHeight),
      padding: EdgeInsets.zero,
      shape: RoundedRectangleBorder(borderRadius: buttonRadius),
    );

    return SizedBox(
      width: buttonWidth,
      child: Padding(
        padding: margin ??
            const EdgeInsets.fromLTRB(
              Dimensions.paddingSizeLarge,
              0,
              Dimensions.paddingSizeLarge,
              Dimensions.paddingSizeExtraLarge,
            ),
        child: DecoratedBox(
          decoration: decoration,
          child: TextButton(
            onPressed: enabled ? onPressed : null,
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
              color: transparent ? AppColors.accentPrimary : Colors.white,
            ),
      ),
    );
  }
}
