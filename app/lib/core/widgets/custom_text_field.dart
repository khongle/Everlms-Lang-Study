import 'package:country_code_picker/country_code_picker.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class CustomTextField extends StatefulWidget {
  const CustomTextField({
    super.key,
    this.hintText = '',
    this.controller,
    this.focusNode,
    this.nextFocus,
    this.isEnabled = true,
    this.inputType = TextInputType.text,
    this.inputAction = TextInputAction.next,
    this.maxLines = 1,
    this.minLines = 1,
    this.isShowSuffixIcon = false,
    this.onSubmit,
    this.capitalization = TextCapitalization.none,
    this.isPassword = false,
    this.isShowBorder = true,
    this.isAutoFocus = false,
    this.isReadOnly = false,
    this.isFucusBorder = false,
    this.countryDialCode,
    this.onCountryChanged,
    this.suffixIcon,
    this.onChanged,
    this.onValidate,
    this.prefixIcon,
    this.borderRadius = Dimensions.radiusSmall,
    this.fillColor,
    this.title,
  });
  final String? hintText;
  final String? title;
  final TextEditingController? controller;
  final FocusNode? focusNode;
  final FocusNode? nextFocus;
  final TextInputType? inputType;
  final TextInputAction? inputAction;
  final bool? isPassword;
  final bool isShowBorder;
  final bool? isAutoFocus;
  final bool isReadOnly;
  final bool isFucusBorder;
  final Function(String)? onSubmit;
  final bool? isEnabled;
  final int? maxLines;
  final int? minLines;
  final bool? isShowSuffixIcon;
  final TextCapitalization? capitalization;
  final Function(String text)? onChanged;
  final String? countryDialCode;
  final Function(CountryCode countryCode)? onCountryChanged;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final double? borderRadius;
  final Color? fillColor;
  final String? Function(String?)? onValidate;

  @override
  CustomTextFieldState createState() => CustomTextFieldState();
}

class CustomTextFieldState extends State<CustomTextField> {
  bool _obscureText = true;
  late FocusNode _focusNode;
  bool _showClearIcon = false;

  @override
  void initState() {
    _focusNode = widget.focusNode ?? FocusNode();
    _focusNode.addListener(_toggleClearIcon);
    super.initState();
  }

  @override
  void dispose() {
    _focusNode.removeListener(_toggleClearIcon);
    if (widget.focusNode == null) {
      _focusNode.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      maxLines: widget.maxLines,
      minLines: widget.minLines,
      controller: widget.controller,
      focusNode: widget.focusNode ?? _focusNode,
      style: robotoRegular.copyWith(
        fontSize: Dimensions.fontSizeLarge,
        color: widget.isEnabled == false
            ? AppColors.textMuted
            : Theme.of(context).colorScheme.onSurface,
      ),
      textInputAction: widget.inputAction,
      keyboardType: widget.inputType,
      cursorColor: Theme.of(context).hintColor,
      textCapitalization: widget.capitalization!,
      enabled: widget.isEnabled,
      autofocus: widget.isAutoFocus!,
      autovalidateMode: AutovalidateMode.disabled,
      readOnly: widget.isReadOnly,
      autofillHints: widget.inputType == TextInputType.name
          ? [AutofillHints.name]
          : widget.inputType == TextInputType.emailAddress
              ? [AutofillHints.email]
              : widget.inputType == TextInputType.phone
                  ? [AutofillHints.telephoneNumber]
                  : widget.inputType == TextInputType.streetAddress
                      ? [AutofillHints.fullStreetAddress]
                      : widget.inputType == TextInputType.url
                          ? [AutofillHints.url]
                          : widget.inputType == TextInputType.visiblePassword
                              ? [AutofillHints.password]
                              : null,
      obscureText: widget.isPassword! && _obscureText,
      decoration: InputDecoration(
        hintText: widget.hintText,
        label: widget.title != null ? Text(widget.title!) : null,
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(widget.borderRadius!),
          borderSide: BorderSide(
            color: widget.isShowBorder ? AppColors.border : Colors.transparent,
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(widget.borderRadius!),
          borderSide: BorderSide(
            color: widget.isShowBorder
                ? AppColors.accentPrimary
                : Colors.transparent,
          ),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(widget.borderRadius!),
          borderSide: const BorderSide(color: AppColors.accentDanger),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(widget.borderRadius!),
          borderSide: const BorderSide(color: AppColors.accentDanger),
        ),
        isDense: true,
        filled: true,
        fillColor: widget.fillColor ?? AppColors.bgTertiary,
        contentPadding: EdgeInsets.all(
          Directionality.of(context) == TextDirection.ltr
              ? Dimensions.paddingSizeSmall
              : Dimensions.paddingSizeExtraSmall,
        ),
        hintStyle: Theme.of(context).textTheme.bodyLarge!.copyWith(
              color: AppColors.textMuted,
            ),
        prefixIcon: widget.prefixIcon,
        suffixIcon: widget.isPassword!
            ? IconButton(
                splashRadius: 20,
                highlightColor: Colors.transparent,
                icon: Icon(
                  _obscureText ? Icons.visibility_off : Icons.visibility,
                  size: Dimensions.iconSizeLarge,
                  color: AppColors.textMuted,
                ),
                onPressed: _toggle,
              )
            : ValueListenableBuilder<TextEditingValue>(
                valueListenable: widget.controller!,
                builder: (context, value, child) {
                  if (value.text.isNotEmpty &&
                      _showClearIcon &&
                      !widget.isReadOnly) {
                    return InkWell(
                      onTap: () => widget.controller?.clear(),
                      child: const Icon(
                        Icons.clear,
                        color: AppColors.textMuted,
                      ),
                    );
                  }

                  return const SizedBox.shrink();
                },
              ),
      ),
      onFieldSubmitted: (text) => widget.nextFocus != null
          ? FocusScope.of(context).requestFocus(widget.nextFocus)
          : widget.onSubmit?.call(text),
      onChanged: widget.onChanged,
      validator: widget.onValidate,
      onTap: () {
        if (widget.isReadOnly) {
          _focusNode.unfocus();
        }
      },
    );
  }

  void _toggle() {
    setState(() {
      _obscureText = !_obscureText;
    });
  }

  void _toggleClearIcon() {
    setState(() {
      if (_focusNode.hasFocus) {
        _showClearIcon = true;
      } else {
        _showClearIcon = false;
      }
    });
  }
}
