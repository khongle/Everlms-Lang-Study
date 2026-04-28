import 'package:flutter/material.dart';

const robotoLight = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w300,
);

const robotoRegular = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w400,
);

const robotoMedium = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w500,
);

const robotoBold = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w700,
);

const robotoSemiBold = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w600,
);

List<BoxShadow>? searchBoxShadow(BuildContext context) =>
    Theme.of(context).brightness == Brightness.dark
        ? null
        : [
            const BoxShadow(
              offset: Offset(0, 3),
              color: Color(0x208F94FB),
              blurRadius: 5,
              spreadRadius: 2,
            ),
          ];

List<BoxShadow>? cardShadow(BuildContext context) =>
    Theme.of(context).brightness == Brightness.dark
        ? null
        : [
            BoxShadow(
              offset: const Offset(0, 1),
              blurRadius: 2,
              color: Colors.black.withValues(alpha: 0.15),
            ),
          ];

List<BoxShadow> shadow = [
  BoxShadow(
    offset: const Offset(0, 3),
    color: Colors.grey[100]!,
    blurRadius: 1,
    spreadRadius: 2,
  ),
];
