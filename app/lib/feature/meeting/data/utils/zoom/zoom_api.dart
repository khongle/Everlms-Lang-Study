import 'dart:convert';

import 'package:everlms/feature/meeting/data/utils/zoom/config.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

Future<dynamic> generateZoomAccessToken() async {
  final url = Uri.https('zoom.us', '/oauth/token');
  final response = await http.post(
    url,
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Authorization': 'Basic $kBase64Key',
    },
    body: {
      'grant_type': 'account_credentials',
      'account_id': kAccountId,
    },
  );
  if (kDebugMode) {
    debugPrint('Response status: ${response.statusCode}');
    debugPrint('Response body: ${response.body}');
  }

  return jsonDecode(response.body);
}

Future<dynamic> createMeeting(String zoomAccessToken) async {
  final url = Uri.https('api.zoom.us', '/v2/users/me/meetings');
  final response = await http.post(
    url,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $zoomAccessToken',
    },
    body: jsonEncode({
      'topic': 'My New Meeting',
      'type': 2,
      'start_time': '2024-01-15T12:03:00Z',
      'duration': 60,
      'password': '123456',
      'timezone': 'UTC',
      'settings': {
        //"auto_recording": "cloud"
      },
    }),
  );
  if (kDebugMode) {
    debugPrint('Response status: ${response.statusCode}');
    debugPrint('Response body: ${response.body}');
  }

  return jsonDecode(response.body);
}

Future<dynamic> getZak(String zoomAccessToken, String userId) async {
  final url =
      Uri.https('api.zoom.us', '/v2/users/$userId/token', {'type': 'zak'});
  final response = await http.get(
    url,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $zoomAccessToken',
    },
  );
  if (kDebugMode) {
    debugPrint('Response status: ${response.statusCode}');
    debugPrint('Response body: ${response.body}');
  }

  return jsonDecode(response.body);
}
