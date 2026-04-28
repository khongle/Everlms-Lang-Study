class NotificationModel {
  NotificationModel({this.success, this.message, this.data});

  NotificationModel.fromJson(Map<String, dynamic> json) {
    success = json['success'];
    message = json['message'];
    if (json['data'] != null) {
      data = <CustomNotification>[];
      json['data'].forEach((v) {
        data!.add(CustomNotification.fromJson(v));
      });
    }
  }
  bool? success;
  String? message;
  List<CustomNotification>? data;
  //
  // Map<String, dynamic> toJson() {
  //   final Map<String, dynamic> data = <String, dynamic>{};
  //   data['success'] = success;
  //   data['message'] = message;
  //   if (this.data != null) {
  //     data['data'] = this.data!.map((v) => v.toJson()).toList();
  //   }
  //   return data;
  // }
}

class CustomNotification {
  CustomNotification({this.id, this.name, this.created});

  factory CustomNotification.fromJson(Map<String, dynamic> json) {
    return CustomNotification(
      id: json['uuid'],
      name: json['name'],
      created: json['created'],
    );
  }
  String? id;
  String? name;
  String? created;
  //
  // Map<String, dynamic> toJson() {
  //   final Map<String, dynamic> data = <String, dynamic>{};
  //   data['id'] = id;
  //   data['title'] = name;
  //   data['date'] = created;
  //   return data;
  // }
}
