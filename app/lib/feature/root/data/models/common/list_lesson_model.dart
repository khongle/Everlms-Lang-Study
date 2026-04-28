class ListLessonModel {
  ListLessonModel({this.jsonapi, this.data, this.links});

  ListLessonModel.fromJson(Map<String, dynamic> json) {
    jsonapi =
        json['jsonapi'] != null ? Jsonapi.fromJson(json['jsonapi']) : null;
    if (json['data'] != null) {
      data = <DataLesson>[];
      json['data'].forEach((v) {
        data!.add(DataLesson.fromJson(v));
      });
    }
    links = json['links'] != null ? Links.fromJson(json['links']) : null;
  }
  Jsonapi? jsonapi;
  List<DataLesson>? data;
  Links? links;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (jsonapi != null) {
      data['jsonapi'] = jsonapi!.toJson();
    }
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    if (links != null) {
      data['links'] = links!.toJson();
    }
    return data;
  }
}

class Jsonapi {
  Jsonapi({this.version, this.meta});

  Jsonapi.fromJson(Map<String, dynamic> json) {
    version = json['version'];
    meta = json['meta'] != null ? Meta.fromJson(json['meta']) : null;
  }
  String? version;
  Meta? meta;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['version'] = version;
    if (meta != null) {
      data['meta'] = meta!.toJson();
    }
    return data;
  }
}

class Meta {
  Meta({this.links});

  Meta.fromJson(Map<String, dynamic> json) {
    links = json['links'] != null ? Links.fromJson(json['links']) : null;
  }
  Links? links;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (links != null) {
      data['links'] = links!.toJson();
    }
    return data;
  }
}

class Links {
  Links({this.self});

  Links.fromJson(Map<String, dynamic> json) {
    self = json['self'] != null ? Self.fromJson(json['self']) : null;
  }
  Self? self;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (self != null) {
      data['self'] = self!.toJson();
    }
    return data;
  }
}

class Self {
  Self({this.href});

  Self.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  String? href;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['href'] = href;
    return data;
  }
}

class DataLesson {
  DataLesson({
    this.type,
    this.id,
    this.links,
    this.attributes,
    this.relationships,
  });

  DataLesson.fromJson(Map<String, dynamic> json) {
    type = json['type'];
    id = json['id'];
    links = json['links'] != null ? Links.fromJson(json['links']) : null;
    attributes = json['attributes'] != null
        ? Attributes.fromJson(json['attributes'])
        : null;
    relationships = json['relationships'] != null
        ? Relationships.fromJson(json['relationships'])
        : null;
  }
  String? type;
  String? id;
  Links? links;
  Attributes? attributes;
  Relationships? relationships;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['type'] = type;
    data['id'] = id;
    if (links != null) {
      data['links'] = links!.toJson();
    }
    if (attributes != null) {
      data['attributes'] = attributes!.toJson();
    }
    if (relationships != null) {
      data['relationships'] = relationships!.toJson();
    }
    return data;
  }
}

class Attributes {
  Attributes({
    this.drupalInternalLmsLessonId,
    this.langcode,
    this.name,
    this.created,
    this.changed,
    this.status,
    this.defaultLangcode,
    this.fieldBody,
    this.fieldClassTime,
    this.fieldComments,
    this.fieldEmbeddedVideo,
    this.fieldFree,
    this.fieldGoogleMeetData,
    this.fieldGoogleMeetLink,
    this.fieldZoomClassData,
    this.fieldZoomClassLink,
  });

  Attributes.fromJson(Map<String, dynamic> json) {
    drupalInternalLmsLessonId = json['drupal_internal__lms_lesson_id'];
    langcode = json['langcode'];
    name = json['name'];
    created = json['created'];
    changed = json['changed'];
    status = json['status'];
    defaultLangcode = json['default_langcode'];
    fieldBody = json['field_body'];
    fieldClassTime = json['field_class_time'];
    fieldComments = json['field_comments'] != null
        ? FieldComments.fromJson(json['field_comments'])
        : null;
    fieldEmbeddedVideo = json['field_embedded_video'];
    fieldFree = json['field_free'];
    fieldGoogleMeetData = json['field_google_meet_data'];
    fieldGoogleMeetLink = json['field_google_meet_link'];
    fieldZoomClassData = json['field_zoom_class_data'];
    fieldZoomClassLink = json['field_zoom_class_link'];
  }
  int? drupalInternalLmsLessonId;
  String? langcode;
  String? name;
  String? created;
  String? changed;
  bool? status;
  bool? defaultLangcode;
  dynamic fieldBody;
  dynamic fieldClassTime;
  FieldComments? fieldComments;
  String? fieldEmbeddedVideo;
  bool? fieldFree;
  dynamic fieldGoogleMeetData;
  dynamic fieldGoogleMeetLink;
  dynamic fieldZoomClassData;
  dynamic fieldZoomClassLink;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['drupal_internal__lms_lesson_id'] = drupalInternalLmsLessonId;
    data['langcode'] = langcode;
    data['name'] = name;
    data['created'] = created;
    data['changed'] = changed;
    data['status'] = status;
    data['default_langcode'] = defaultLangcode;
    data['field_body'] = fieldBody;
    data['field_class_time'] = fieldClassTime;
    if (fieldComments != null) {
      data['field_comments'] = fieldComments!.toJson();
    }
    data['field_embedded_video'] = fieldEmbeddedVideo;
    data['field_free'] = fieldFree;
    data['field_google_meet_data'] = fieldGoogleMeetData;
    data['field_google_meet_link'] = fieldGoogleMeetLink;
    data['field_zoom_class_data'] = fieldZoomClassData;
    data['field_zoom_class_link'] = fieldZoomClassLink;
    return data;
  }
}

class FieldComments {
  FieldComments({
    this.status,
    this.cid,
    this.lastCommentTimestamp,
    this.lastCommentName,
    this.lastCommentUid,
    this.commentCount,
  });

  FieldComments.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    cid = json['cid'];
    lastCommentTimestamp = json['last_comment_timestamp'];
    lastCommentName = json['last_comment_name'];
    lastCommentUid = json['last_comment_uid'];
    commentCount = json['comment_count'];
  }
  int? status;
  int? cid;
  int? lastCommentTimestamp;
  dynamic lastCommentName;
  int? lastCommentUid;
  int? commentCount;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['status'] = status;
    data['cid'] = cid;
    data['last_comment_timestamp'] = lastCommentTimestamp;
    data['last_comment_name'] = lastCommentName;
    data['last_comment_uid'] = lastCommentUid;
    data['comment_count'] = commentCount;
    return data;
  }
}

class Relationships {
  Relationships({
    this.lmsLessonType,
    this.uid,
    this.fieldLessonType,
    this.fieldScorm,
    this.fieldZoomClass,
  });

  Relationships.fromJson(Map<String, dynamic> json) {
    lmsLessonType = json['lms_lesson_type'] != null
        ? LmsLessonType.fromJson(json['lms_lesson_type'])
        : null;
    uid = json['uid'] != null ? LmsLessonType.fromJson(json['uid']) : null;
    fieldLessonType = json['field_lesson_type'] != null
        ? FieldLessonType.fromJson(json['field_lesson_type'])
        : null;
    fieldScorm = json['field_scorm'] != null
        ? FieldLessonType.fromJson(json['field_scorm'])
        : null;
    fieldZoomClass = json['field_zoom_class'] != null
        ? FieldLessonType.fromJson(json['field_zoom_class'])
        : null;
  }
  LmsLessonType? lmsLessonType;
  LmsLessonType? uid;
  FieldLessonType? fieldLessonType;
  FieldLessonType? fieldScorm;
  FieldLessonType? fieldZoomClass;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (lmsLessonType != null) {
      data['lms_lesson_type'] = lmsLessonType!.toJson();
    }
    if (uid != null) {
      data['uid'] = uid!.toJson();
    }
    if (fieldLessonType != null) {
      data['field_lesson_type'] = fieldLessonType!.toJson();
    }
    if (fieldScorm != null) {
      data['field_scorm'] = fieldScorm!.toJson();
    }
    if (fieldZoomClass != null) {
      data['field_zoom_class'] = fieldZoomClass!.toJson();
    }
    return data;
  }
}

class LmsLessonType {
  LmsLessonType({this.data, this.links});

  LmsLessonType.fromJson(Map<String, dynamic> json) {
    data = json['data'] != null ? DataLesson.fromJson(json['data']) : null;
    links = json['links'] != null ? Links.fromJson(json['links']) : null;
  }
  DataLesson? data;
  Links? links;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    if (links != null) {
      data['links'] = links!.toJson();
    }
    return data;
  }
}

class FieldLessonType {
  FieldLessonType({this.data, this.links});

  FieldLessonType.fromJson(Map<String, dynamic> json) {
    data = json['data'];
    links = json['links'] != null ? Links.fromJson(json['links']) : null;
  }
  dynamic data;
  Links? links;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['data'] = this.data;
    if (links != null) {
      data['links'] = links!.toJson();
    }
    return data;
  }
}
