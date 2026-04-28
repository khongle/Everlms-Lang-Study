/// Typed exception for all API errors.
///
/// Thrown by [ApiClient] interceptors and repositories when an HTTP call fails.
class ApiException implements Exception {
  const ApiException({
    required this.message,
    this.statusCode,
    this.data,
  });

  final String message;
  final int? statusCode;
  final dynamic data;

  bool get isUnauthorized => statusCode == 401;
  bool get isNotFound => statusCode == 404;
  bool get isServerError =>
      statusCode != null && statusCode! >= 500 && statusCode! < 600;

  @override
  String toString() => 'ApiException($statusCode): $message';
}
