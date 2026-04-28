import 'dart:io';

import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/main.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';

String formatTimeAgoComment(String value) {
  final created = DateTime.fromMillisecondsSinceEpoch(int.parse(value) * 1000);
  final diff = DateTime.now().difference(created);
  final diffHours = diff.inHours;
  final diffMinutes = diff.inMinutes;
  final diffSeconds = diff.inSeconds;
  final diffDay = diff.inDays;
  final diffWeeks = diffDay ~/ 7;

  if (diffWeeks > 0) {
    return '${diffWeeks}w ago';
  } else if (diffDay > 0) {
    return '${diffDay}d ago';
  } else if (diffHours > 0) {
    return '${diffHours}h ago';
  } else if (diffMinutes > 0) {
    return '${diffMinutes}min ago';
  } else if (diffSeconds > 0) {
    return '${diffSeconds}s ago';
  } else {
    return 'now';
  }
}

String formatTimeAgo(String value) {
  final now = DateTime.now();

  final created = DateTime.fromMillisecondsSinceEpoch(int.parse(value) * 1000);
  final diff = DateTime.now().difference(created);
  final diffHours = diff.inHours;
  final diffMinutes = diff.inMinutes;
  final diffSeconds = diff.inSeconds;

  final dynamic dayData = {
    1: 'Mon',
    2: 'Tue',
    3: 'Wed',
    4: 'Thu',
    5: 'Fri',
    6: 'Sat',
    7: 'Sun',
  };
  final dynamic monthData = {
    1: 'Jan',
    2: 'Feb',
    3: 'Mar',
    4: 'Apr',
    5: 'May',
    6: 'June',
    7: 'Jul',
    8: 'Aug',
    9: 'Sep',
    10: 'Oct',
    11: 'Nov',
    12: 'Dec',
  };
  if (created.year != now.year) {
    return '${monthData[created.month]} ${created.day} ${created.year}'.trim();
  }
  if (diffSeconds < 60) {
    return 'Now'.trim();
  }
  if (diffMinutes < 60) {
    return '${diffMinutes}m ago'.trim();
  }
  if (diffHours < 24) {
    return '${diffHours}h ago'.trim();
  }
  if (created.day + 1 == now.day) {
    return 'Yesterday ${created.hour}:${created.minute}'.trim();
  }
  if (created.weekday < now.weekday && created.month == now.month) {
    return '${dayData[created.weekday]} ${created.hour}:${created.minute}'
        .trim();
  }
  return '${monthData[created.month]} ${created.day}'.trim();
}

int diffInMonths(DateTime from, DateTime to) {
  var months = (to.year - from.year) * 12 + (to.month - from.month);

  // Nếu ngày của "to" nhỏ hơn ngày của "from" → chưa đủ 1 tháng
  if (to.day < from.day) {
    months--;
  }

  return months;
}

Future<File> downloadPDF(String url, String fileName) async {
  try {
    // Get the directory to save the file
    final directory = await getApplicationDocumentsDirectory();
    final filePath = '${directory.path}/$fileName';

    // Download the file
    final sharedPreferences = await SharedPreferences.getInstance();
    final cookie = sharedPreferences.getString(AppConstants.cookie);
    // get with new domain , eg : url/user-course/53/download
    final response = await http.get(
      Uri.parse(url),
      headers: {'Authorization': basicAuthWeb, 'Cookie': cookie ?? ''},
    );
    // Check if the download was successful
    if (response.statusCode == 200) {
      final file = File(filePath);
      await file.writeAsBytes(response.bodyBytes);
      return file;
    } else {
      throw Exception('Failed to download file');
    }
  } on Exception catch (e) {
    throw Exception('Error downloading file: $e');
  }
}

Future<void> downloadAndSaveFile(String url, String fileName) async {
  try {
    if (!kIsWeb) {
      final status = await Permission.storage.request();
      if (!status.isGranted) {
        customSnackBar('Failed to download file');
        return;
      }
    }

    final sharedPreferences = await SharedPreferences.getInstance();
    final basicAuth = sharedPreferences.getString(AppConstants.basicAuth);
    final cookie = sharedPreferences.getString(AppConstants.cookie);
    // get with new domain , eg : url/user-course/53/download
    final response = await http.get(
      Uri.parse(url),
      headers: {'Authorization': basicAuth ?? '', 'Cookie': cookie ?? ''},
    );
    if (response.statusCode == 200) {
      final bytes = response.bodyBytes;

      if (Platform.isAndroid) {
        const downloadsPath = '/storage/emulated/0/Download';
        var fullPath = '$downloadsPath/$fileName.pdf';
        var file = File(fullPath);
        var count = 1;
        while (file.existsSync()) {
          fullPath = '$downloadsPath/$fileName ($count).pdf';
          file = File(fullPath);
          count++;
        }
        await file.writeAsBytes(bytes);
        customSnackBar('Download success', isError: false);
      } else if (Platform.isIOS) {
        final documentsDir = await getApplicationDocumentsDirectory();
        final selectedDirectory = documentsDir.path;
        var fullPath = '$selectedDirectory/$fileName.pdf';
        var file = File(fullPath);
        var count = 1;
        while (file.existsSync()) {
          fullPath = '$selectedDirectory/$fileName ($count).pdf';
          file = File(fullPath);
          count++;
        }
        await file.writeAsBytes(bytes);
        customSnackBar('Download success', isError: false);
      }
    } else {
      throw Exception('Failed to download file');
    }
  } on Exception catch (e) {
    customSnackBar('Error: $e');
  }
}

Future<File?> downloadUrlToCache(String url) async {
  try {
    final dirCache = await getApplicationCacheDirectory();
    final uri = Uri.parse(url);
    final filename = uri.pathSegments.last;
    final downloadPath = '${dirCache.path}/$filename';
    final file = File(downloadPath);
    final sharedPreferences = await SharedPreferences.getInstance();
    final cookie = sharedPreferences.getString(AppConstants.cookie);
    final response = await http.get(
      uri,
      headers: {'Authorization': basicAuthWeb, 'Cookie': cookie ?? ''},
    );
    if (!file.existsSync()) {
      file.writeAsBytesSync(response.bodyBytes);
    }
    return file;
  } on Exception {
    rethrow;
  }
}
