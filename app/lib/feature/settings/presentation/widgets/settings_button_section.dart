import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';

class SettingsButtonSection extends StatelessWidget {
  const SettingsButtonSection({
    super.key,
    this.leadingBtn,
    this.leadingSize,
    this.titleBtn,
    this.color,
    this.onTap,
    this.trailing,
    this.isShowTrailing = true,
  });
  final String? leadingBtn;
  final double? leadingSize;
  final bool isShowTrailing;
  final IconData? trailing;
  final String? titleBtn;
  final Color? color;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 60,
      child: ListTile(
        onTap: onTap,
        contentPadding: EdgeInsets.zero,
        splashColor: Colors.transparent,
        selectedTileColor: Colors.transparent,
        leading: SvgPicture.asset(
          leadingBtn ?? '',
          height: leadingSize ?? 35,
          colorFilter: ColorFilter.mode(
            color ?? Theme.of(context).colorScheme.onSurface,
            BlendMode.srcIn,
          ),
        ),
        title: Text(
          titleBtn ?? '',
          style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                color: color ?? Theme.of(context).colorScheme.onSurface,
              ),
        ),
        trailing: isShowTrailing
            ? trailing != null
                ? Icon(
                    trailing,
                    size: Dimensions.iconSizeExtraLarge,
                  )
                : const Icon(
                    Icons.arrow_forward_ios,
                    size: Dimensions.iconSizeDefault,
                  )
            : null,
      ),
    );
  }
}
