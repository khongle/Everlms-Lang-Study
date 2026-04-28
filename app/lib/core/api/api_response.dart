/// Backward-compatible response wrapper.
///
/// Wraps a Dio [Response] and exposes `.body` (like the old GetX Response)
/// so that existing controllers can keep using `response.body['key']`
/// without changes during the migration period.
///
/// Will be removed once all controllers migrate to Riverpod providers
/// (Phase 4), at which point repositories will return typed data directly.
class ApiResponse {
  const ApiResponse({
    required this.body,
    this.statusCode,
    this.statusText,
    this.headers,
  });

  /// Parsed response body (typically `Map<String, dynamic>` or `List`).
  final dynamic body;

  /// HTTP status code.
  final int? statusCode;

  /// HTTP reason phrase / status text.
  final String? statusText;

  /// Response headers.
  final Map<String, String>? headers;

  bool get isOk => statusCode == 200;
}
