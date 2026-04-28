class Profile {
  Profile({
    required this.profileId,
    required this.uuid,
    required this.revisionId,
    required this.type,
    required this.revisionCreated,
    required this.revisionUser,
    required this.status,
    required this.uid,
    required this.isDefault,
    required this.created,
    required this.changed,
    required this.fieldDescription,
    required this.fieldFirstName,
    required this.fieldFullName,
    required this.fieldLastName,
  });

  factory Profile.fromJson(Map<String, dynamic> json) {
    return Profile(
      profileId: ProfileField<int>.fromJson(json['profile_id'][0]).value,
      uuid: ProfileField<String>.fromJson(json['uuid'][0]).value,
      revisionId: ProfileField<int>.fromJson(json['revision_id'][0]).value,
      type: ProfileType.fromJson(json['type'][0]),
      revisionCreated: DateTime.parse(
        ProfileField<String>.fromJson(json['revision_created'][0]).value,
      ),
      revisionUser: RevisionUser.fromJson(json['revision_user'][0]),
      status: ProfileField<bool>.fromJson(json['status'][0]).value,
      uid: RevisionUser.fromJson(json['uid'][0]),
      isDefault: ProfileField<bool>.fromJson(json['is_default'][0]).value,
      created: DateTime.parse(
        ProfileField<String>.fromJson(json['created'][0]).value,
      ),
      changed: DateTime.parse(
        ProfileField<String>.fromJson(json['changed'][0]).value,
      ),
      fieldDescription: FieldDescription.fromJson(json['field_description'][0]),
      fieldFirstName:
          ProfileField<String>.fromJson(json['field_first_name'][0]).value,
      fieldFullName:
          ProfileField<String>.fromJson(json['field_full_name'][0]).value,
      fieldLastName:
          ProfileField<String>.fromJson(json['field_last_name'][0]).value,
    );
  }
  int profileId;
  String uuid;
  int revisionId;
  ProfileType type;
  DateTime revisionCreated;
  RevisionUser revisionUser;
  bool status;
  RevisionUser uid;
  bool isDefault;
  DateTime created;
  DateTime changed;
  FieldDescription fieldDescription;
  String fieldFirstName;
  String fieldFullName;
  String fieldLastName;
}

class ProfileField<T> {
  ProfileField({required this.value});

  factory ProfileField.fromJson(Map<String, dynamic> json) {
    return ProfileField(
      value: json['value'],
    );
  }
  T value;
}

class ProfileType {
  ProfileType({
    required this.targetId,
    required this.targetType,
    required this.targetUuid,
  });

  factory ProfileType.fromJson(Map<String, dynamic> json) {
    return ProfileType(
      targetId: json['target_id'],
      targetType: json['target_type'],
      targetUuid: json['target_uuid'],
    );
  }
  String targetId;
  String targetType;
  String targetUuid;
  Map<String, dynamic> toJson() {
    return {
      'target_id': targetId,
      'target_type': targetType,
      'target_uuid': targetUuid,
    };
  }
}

class RevisionUser {
  RevisionUser({
    required this.targetId,
    required this.targetType,
    required this.targetUuid,
    required this.url,
  });

  factory RevisionUser.fromJson(Map<String, dynamic> json) {
    return RevisionUser(
      targetId: json['target_id'],
      targetType: json['target_type'],
      targetUuid: json['target_uuid'],
      url: json['url'],
    );
  }
  int targetId;
  String targetType;
  String targetUuid;
  String url;
}

class FieldDescription {
  FieldDescription({
    required this.value,
    required this.format,
    required this.processed,
  });

  factory FieldDescription.fromJson(Map<String, dynamic> json) {
    return FieldDescription(
      value: json['value'],
      format: json['format'],
      processed: json['processed'],
    );
  }
  String value;
  String format;
  String processed;
}
