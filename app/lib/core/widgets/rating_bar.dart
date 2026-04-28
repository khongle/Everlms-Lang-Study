import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';

class CustomRatingBar extends StatelessWidget {
  const CustomRatingBar({
    required this.rating,
    super.key,
    this.ratingCount,
    this.size = 18,
    this.iconColor = Colors.yellow,
  });
  final double? rating;
  final double? size;
  final int? ratingCount;
  final Color iconColor;

  @override
  Widget build(BuildContext context) {
    final starList = <Widget>[];

    final realNumber = rating!.floor();
    final partNumber = ((rating! - realNumber) * 10).ceil();

    for (var i = 0; i < 5; i++) {
      if (i < realNumber) {
        starList.add(
          Icon(
            Icons.star,
            color: iconColor,
            size: size,
          ),
        );
      } else if (i == realNumber) {
        starList.add(
          SizedBox(
            height: size,
            width: size,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Icon(
                  Icons.star,
                  color: iconColor,
                  size: size,
                ),
                ClipRect(
                  clipper: _Clipper(part: partNumber),
                  child:
                      Icon(Icons.star, color: Colors.grey.shade300, size: size),
                ),
              ],
            ),
          ),
        );
      } else {
        starList.add(Icon(Icons.star, color: Colors.grey.shade300, size: size));
      }
    }
    if (ratingCount != null) {
      starList.add(
        Padding(
          padding:
              const EdgeInsets.only(left: Dimensions.paddingSizeExtraSmall),
          child: Text(
            '($ratingCount)',
            style: robotoRegular.copyWith(
              fontSize: size! * 0.8,
              color: Theme.of(context).disabledColor,
            ),
          ),
        ),
      );
    }

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: starList,
    );
  }
}

class _Clipper extends CustomClipper<Rect> {
  _Clipper({required this.part});
  final int part;

  @override
  Rect getClip(Size size) {
    return Rect.fromLTRB(
      (size.width / 10) * part,
      0,
      size.width,
      size.height,
    );
  }

  @override
  bool shouldReclip(CustomClipper<Rect> oldClipper) => true;
}
