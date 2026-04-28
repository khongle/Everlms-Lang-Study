import 'dart:math';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:html/parser.dart' as html_parser;

const klogTag = '[LMS-Faculty]';
const klogEnabled = true;
DateTime? loginClickTime;

void printLog(dynamic data) {
  if (klogEnabled && kDebugMode) {
    debugPrint('$klogTag$data');
  }
}

String convertCurrencyCodeToSymbol(String code) {
  switch (code) {
    case 'USD':
      return r'$';
    case 'EUR':
    case 'GBP':
      return '€';
    case 'VND':
      return '₫';
    default:
      return code;
  }
}

bool isRedundentClick(DateTime currentTime) {
  if (loginClickTime == null) {
    loginClickTime = currentTime;
    return false;
  }
  if (currentTime.difference(loginClickTime!).inSeconds < 3) {
    return true;
  }

  loginClickTime = currentTime;
  return false;
}

Color randomColorPicker() {
  return Colors.primaries[Random().nextInt(Colors.primaries.length)];
}

Duration parseDuration(String s) {
  var hours = 0;
  var minutes = 0;
  int micros;
  final parts = s.split(':');
  if (parts.length > 2) {
    hours = int.parse(parts[parts.length - 3]);
  }
  if (parts.length > 1) {
    minutes = int.parse(parts[parts.length - 2]);
  }
  micros = (double.parse(parts[parts.length - 1]) * 1000000).round();
  return Duration(hours: hours, minutes: minutes, microseconds: micros);
}

String htmlToString(String html) {
  final document = html_parser.parse(html);
  return document.body?.text ?? '';
}

String htmlToImageSrc(String html) {
  final document = html_parser.parse(html);
  final imgElement = document.querySelector('img');
  final srcAttribute = imgElement?.attributes['src'];
  return srcAttribute ?? '';
}

DateTime epochToDateTime(String epoch) {
  return DateTime.fromMillisecondsSinceEpoch(int.parse(epoch) * 1000);
}

dynamic parseJsonDrupal(dynamic field, {String subKey = 'value'}) {
  if (field is List && field.isNotEmpty) {
    return field[0][subKey];
  }
  if (field is Map) {
    return field[subKey];
  }
  return null;
}

int generateRandomOTP() {
  return Random().nextInt(900000) + 100000;
}

Map<String, dynamic> templateEmailVerification(
  String to,
  String otp,
  int timeout,
) {
  final minutes = Duration(seconds: timeout).inMinutes;
  final template = {
    'to': to,
    'subject': 'Email Verification',
    'otp': otp,
    'timeout': minutes,
  };
  return template;
}
