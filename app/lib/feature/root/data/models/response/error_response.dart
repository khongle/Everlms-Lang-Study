class ErrorResponse {
  ErrorResponse({
    required List<Errors> errors,
  }) {
    _errors = errors;
  }

  ErrorResponse.fromJson(dynamic json) {
    if (json['errors'] != null) {
      _errors = [];
      json['errors'].forEach((v) {
        _errors!.add(Errors.fromJson(v));
      });
    }
  }
  List<Errors>? _errors;

  List<Errors>? get errors => _errors;

  Map<String, dynamic> toJson() {
    final map = <String, dynamic>{};
    if (_errors != null) {
      map['errors'] = _errors!.map((v) => v.toJson()).toList();
    }
    return map;
  }
}

/// code : "l_name"
/// message : "The last name field is required."

class Errors {
  Errors({this.errorCode, this.message});

  Errors.fromJson(Map<String, dynamic> json) {
    errorCode = json['error_code'];
    message = json['message'];
  }
  String? errorCode;
  String? message;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['error_code'] = errorCode;
    data['message'] = message;
    return data;
  }
}
