import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:everlms/core/api/api_exception.dart';
import 'package:everlms/core/api/api_response.dart';
import 'package:everlms/core/api/errors_model.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:flutter/foundation.dart' as foundation;
import 'package:image_picker/image_picker.dart';
import 'package:path/path.dart' as p;
import 'package:shared_preferences/shared_preferences.dart';

/// Dio-based HTTP client — plain Dart class (no GetxService).
///
/// Drop-in replacement for the old `client_api.dart`.  Every public method
/// keeps the **same signature** (name + parameters) so that controllers and
/// repositories can switch imports without further changes.
class ApiClient {
  ApiClient({
    required this.appBaseUrl,
    required this.sharedPreferences,
    required this.auth,
  }) {
    token = sharedPreferences.getString(AppConstants.token);

    _dio = Dio(
      BaseOptions(
        baseUrl: appBaseUrl ?? '',
        connectTimeout: Duration(seconds: timeoutInSeconds),
        receiveTimeout: Duration(seconds: timeoutInSeconds),
        sendTimeout: Duration(seconds: timeoutInSeconds),
      ),
    );

    _dio.interceptors.add(_AuthInterceptor());
    _dio.interceptors.add(_ErrorInterceptor());
    if (foundation.kDebugMode) {
      _dio.interceptors.add(
        LogInterceptor(
          requestHeader: false,
          responseHeader: false,
          logPrint: (obj) => foundation.debugPrint(obj.toString()),
        ),
      );
    }

    updateHeader(
      token,
      sharedPreferences.getString(AppConstants.languageCode),
    );
  }

  final String? appBaseUrl;
  final SharedPreferences sharedPreferences;
  final String? auth;
  static const String noInternetMessage = 'Connection to API server failed';
  final int timeoutInSeconds = 30;

  String? token;
  late final Dio _dio;
  late Map<String, String> _mainHeaders;

  /// Expose the raw Dio instance for edge-case callers.
  Dio get dio => _dio;

  void updateHeader(String? token, String? languageCode) {
    // Keep this.token in sync so interceptors see the latest state.
    this.token = token;

    // Authorization always carries Basic Auth credentials so the server-level
    // HTTP auth gate (nginx / Drupal Shield) is satisfied on every request.
    // Bearer token is sent separately in X-Authorization so it reaches the
    // Drupal custom auth controller without conflicting with Basic Auth.
    _mainHeaders = {
      'Content-Type': 'application/json; charset=UTF-8',
      'Cache-Control': 'no-cache',
      'Authorization': auth ?? '',
      AppConstants.localizationKey:
          languageCode ?? AppConstants.languages[0].languageCode!,
    };

    if (token != null && token.isNotEmpty) {
      _mainHeaders['X-Authorization'] = 'Bearer $token';
    }

    final cookie = sharedPreferences.getString(AppConstants.cookie);
    if (cookie != null) {
      _mainHeaders['Cookie'] = cookie;
    }
    final csrfToken = sharedPreferences.getString(AppConstants.csrfToken);
    if (csrfToken != null) {
      _mainHeaders['X-CSRF-Token'] = csrfToken;
    }
  }

  // ---------------------------------------------------------------------------
  // Public HTTP methods — same API surface as old ApiClient.
  // Returns [ApiResponse] with `.body` for backward compat with controllers.
  // ---------------------------------------------------------------------------

  ApiResponse _wrap(Response<dynamic> r) => ApiResponse(
        body: r.data,
        statusCode: r.statusCode,
        statusText: r.statusMessage,
        headers: r.headers.map.map((k, v) => MapEntry(k, v.join(', '))),
      );

  Future<ApiResponse> getData(
    String uri, {
    Map<String, dynamic>? query,
    Map<String, String>? headers,
    Map<String, dynamic>? body,
  }) async {
    try {
      final response = await _dio.request<dynamic>(
        uri,
        data: body != null ? jsonEncode(body) : null,
        queryParameters: query,
        options: Options(
          method: 'GET',
          headers: {..._mainHeaders, ...?headers},
        ),
      );
      return _wrap(response);
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> postData(
    String uri,
    dynamic body, {
    Map<String, String>? headers,
  }) async {
    try {
      return _wrap(
        await _dio.post<dynamic>(
          uri,
          data: jsonEncode(body),
          options: Options(headers: {..._mainHeaders, ...?headers}),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> patchData(
    String uri,
    dynamic body, {
    Map<String, String>? headers,
  }) async {
    try {
      return _wrap(
        await _dio.patch<dynamic>(
          uri,
          data: jsonEncode(body),
          options: Options(headers: {..._mainHeaders, ...?headers}),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> putData(
    String? uri,
    dynamic body, {
    Map<String, String>? headers,
  }) async {
    try {
      return _wrap(
        await _dio.put<dynamic>(
          uri ?? '',
          data: jsonEncode(body),
          options: Options(headers: {..._mainHeaders, ...?headers}),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> deleteData(
    String? uri, {
    Map<String, String>? headers,
  }) async {
    try {
      return _wrap(
        await _dio.delete<dynamic>(
          uri ?? '',
          options: Options(headers: {..._mainHeaders, ...?headers}),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> postMultipartData(
    String? uri,
    Map<String, String> body,
    List<MultipartBody>? multipartBody,
    File? otherFile, {
    Map<String, String>? headers,
  }) async {
    try {
      final formData = FormData.fromMap(body);

      if (otherFile != null) {
        formData.files.add(
          MapEntry(
            'submitted_file',
            await MultipartFile.fromFile(
              otherFile.path,
              filename: p.basename(otherFile.path),
            ),
          ),
        );
      }

      if (multipartBody != null) {
        for (final part in multipartBody) {
          final file = File(part.file.path);
          formData.files.add(
            MapEntry(
              part.key ?? 'file',
              await MultipartFile.fromFile(
                file.path,
                filename: file.path.split('/').last,
              ),
            ),
          );
        }
      }

      return _wrap(
        await _dio.post<dynamic>(
          uri ?? '',
          data: formData,
          options: Options(headers: {..._mainHeaders, ...?headers}),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<ApiResponse> postMultipartDataConversation(
    String? uri,
    File file,
    String receiverId,
  ) async {
    try {
      final formData = FormData.fromMap({
        'receiver_id': receiverId,
        'file': await MultipartFile.fromFile(
          file.path,
          filename: p.basename(file.path),
        ),
      });

      return _wrap(
        await _dio.post<dynamic>(
          uri ?? '',
          data: formData,
          options: Options(headers: _mainHeaders),
        ),
      );
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }

  Future<void> refreshAccessToken() async {
    // Clear all session data and reset headers back to Basic Auth so that the
    // very next request (e.g. re-login to /oauth/token) does not carry a stale
    // Bearer token in the Authorization header.
    await sharedPreferences.remove(AppConstants.token);
    await sharedPreferences.remove(AppConstants.cookie);
    await sharedPreferences.remove(AppConstants.csrfToken);
    await sharedPreferences.remove(AppConstants.oauthRefreshToken);
    updateHeader(null, sharedPreferences.getString(AppConstants.languageCode));
  }

  /// GET request without Bearer token — for public endpoints that do not
  /// require user authentication (anonymous access).
  ///
  /// Strips the X-Authorization Bearer header so Drupal doesn't attempt to
  /// validate a user session on a public route.
  Future<ApiResponse> getDataPublic(
    String uri, {
    Map<String, dynamic>? query,
  }) async {
    final lang = sharedPreferences.getString(AppConstants.languageCode) ??
        AppConstants.languages[0].languageCode!;
    final publicHeaders = <String, String>{
      'Content-Type': 'application/json; charset=UTF-8',
      'Cache-Control': 'no-cache',
      'Authorization': auth ?? '',
      AppConstants.localizationKey: lang,
      // Explicitly clear X-Authorization so no Bearer token is forwarded.
      'X-Authorization': '',
    };
    return getData(uri, query: query, headers: publicHeaders);
  }

  /// [application/x-www-form-urlencoded] — required by OAuth2 token endpoints.
  Future<ApiResponse> postFormUrlEncoded(
    String uri,
    Map<String, dynamic> body,
  ) async {
    try {
      final response = await _dio.post<dynamic>(
        uri,
        data: body,
        options: Options(
          headers: {
            ..._mainHeaders,
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          contentType: 'application/x-www-form-urlencoded',
        ),
      );
      return _wrap(response);
    } on DioException {
      rethrow;
    } on Exception {
      throw const ApiException(
        statusCode: 1,
        message: noInternetMessage,
      );
    }
  }
}

// ---------------------------------------------------------------------------
// Interceptors
// ---------------------------------------------------------------------------

/// Injects Authorization, Cookie, CSRF, and Language headers into every request.
/// Handles 401 by clearing session — but only for authenticated requests (when token exists).
/// For unauthenticated endpoints like /oauth/token (login), 401 means invalid credentials.
class _AuthInterceptor extends Interceptor {
  _AuthInterceptor();

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401) {
      // Do not auto-clear local auth state on any single 401 response.
      // Some endpoints can return 401 for temporary upstream auth mismatch
      // (e.g. Basic Auth gate) and should not force an app-wide logout.
      return handler.next(err);
    }
    return handler.next(err);
  }
}

/// Maps [DioException] into [ApiException] and handles response body parsing
/// (replicating the old `handleResponse` logic).
class _ErrorInterceptor extends Interceptor {
  @override
  void onResponse(
    Response<dynamic> response,
    ResponseInterceptorHandler handler,
  ) {
    // Parse error bodies the same way the old ApiClient did.
    if (response.statusCode != 200 &&
        response.data != null &&
        response.data is! String) {
      final body = response.data;
      if (body is Map<String, dynamic>) {
        if (body.containsKey('response_code')) {
          final errorResponse = ErrorsModel.fromJson(body);
          throw ApiException(
            statusCode: response.statusCode,
            message: errorResponse.responseCode ?? 'Unknown error',
            data: body,
          );
        } else if (body.containsKey('message')) {
          throw ApiException(
            statusCode: response.statusCode,
            message: body['message']?.toString() ?? 'Unknown error',
            data: body,
          );
        }
      }
    } else if (response.statusCode != 200 && response.data == null) {
      throw const ApiException(
        statusCode: 0,
        message: ApiClient.noInternetMessage,
      );
    }
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    // If already handled as ApiException by onResponse, pass through.
    if (err.error is ApiException) {
      return handler.next(err);
    }

    // Convert DioException → ApiException for the rest of the app.
    final statusCode = err.response?.statusCode;

    // Extract message from response body first (covers OAuth2 error responses,
    // Drupal JSON errors, etc.) before falling back to the HTTP status text.
    String? bodyMessage(dynamic data) {
      if (data is Map<String, dynamic>) {
        // OAuth2: { "error_description": "..." }
        final desc = data['error_description'];
        if (desc is String && desc.isNotEmpty) return desc;
        // Generic: { "message": "..." }
        final msg = data['message'];
        if (msg is String && msg.isNotEmpty) return msg;
      }
      return null;
    }

    final message = switch (err.type) {
      DioExceptionType.connectionTimeout ||
      DioExceptionType.sendTimeout ||
      DioExceptionType.receiveTimeout =>
        'Connection timed out',
      DioExceptionType.connectionError => ApiClient.noInternetMessage,
      DioExceptionType.badResponse => bodyMessage(err.response?.data) ??
          err.response?.statusMessage ??
          'Bad response',
      _ => err.message ?? ApiClient.noInternetMessage,
    };

    handler.next(
      DioException(
        requestOptions: err.requestOptions,
        response: err.response,
        type: err.type,
        error: ApiException(
          statusCode: statusCode ?? 1,
          message: message,
          data: err.response?.data,
        ),
      ),
    );
  }
}

class MultipartBody {
  MultipartBody(this.key, this.file);
  String? key;
  XFile file;
}
