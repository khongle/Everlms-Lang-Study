class ErrorsModel {
  ErrorsModel({this.responseCode, this.message, this.errors});

  ErrorsModel.fromJson(Map<String, dynamic> json) {
    responseCode = json['response_code'];
    message = json['message'];
    if (json['errors'] != null) {
      errors = <ApiFieldError>[];
      json['errors'].forEach((v) {
        errors!.add(ApiFieldError.fromJson(v));
      });
    }
  }

  String? responseCode;
  String? message;
  List<ApiFieldError>? errors;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['response_code'] = responseCode;
    data['message'] = message;
    if (errors != null) {
      data['errors'] = errors!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class ApiFieldError {
  ApiFieldError({this.errorCode, this.message});

  ApiFieldError.fromJson(Map<String, dynamic> json) {
    errorCode = json['error_code'];
    message = json['message'];
  }

  String? errorCode;
  String? message;

  Map<String, dynamic> toJson() => {
        'error_code': errorCode,
        'message': message,
      };
}
