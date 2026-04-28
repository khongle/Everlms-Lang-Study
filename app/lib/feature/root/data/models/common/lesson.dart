import 'dart:convert';
import 'package:everlms/core/utils/help_me.dart';

import 'package:everlms/feature/meeting/data/models/zoom_class_data.dart';
import 'package:html/parser.dart' as html_parser;

class Lesson {
  Lesson({
    required this.id,
    required this.title,
    required this.description,
    required this.whatWillYouLearn,
    required this.type,
    required this.isFree,
    required this.thumbnail,
    required this.link,
    required this.lessonFolderPath,
    required this.listScorm,
    required this.body,
    required this.urlScorm,
    required this.urlVideoEmbedded,
    required this.urlVideo,
    required this.urlVideoH5p,
    required this.urlGoogleSlides,
    required this.urlMicrosoftDocs,
    required this.urlPdf,
    required this.urlZoomClass,
    required this.zoomClassData,
    required this.isCompleted,
    required this.hasPermission,
    this.duration,
    this.detail,
  });

  factory Lesson.fromJson(Map<String, dynamic> json) {
    final listScormFromJson = json['list_scorm'] as List;
    final listScormList = listScormFromJson
        .map((e) => LessonScorm.fromJson(e as Map<String, dynamic>))
        .toList();
    final urlZoomList = json['field_zoom_class_link'];
    var urlZoom = '';
    if (urlZoomList != null && urlZoomList.isNotEmpty) {
      urlZoom = urlZoomList[0]['uri'] ?? '';
    }
    return Lesson(
      id: json['id'] as int,
      title: json['title'] as String,
      description:
          json['description'] != null ? json['description'] as String : '',
      whatWillYouLearn: '',
      type: json['type'] as String,
      duration: json['duration'] as String?,
      isFree: json['is_free'] as bool,
      thumbnail: json['thumbnail'] as String,
      link: json['link'] as String,
      lessonFolderPath: json['lessonFolderPath'] as String,
      listScorm: listScormList,
      body: json['field_body'][0]['value'],
      urlScorm: json['field_scorm'][0]['url_app_play_scorme'] ?? '',
      urlVideoEmbedded: json['field_embedded_video'][0]['value'] ?? '',
      urlVideo: json['field_video_url'][0]['value'] ?? '',
      zoomClassData: json['field_zoom_class_data'][0]['value'] ?? '',
      urlVideoH5p: json['field_h5p_content'][0]['h5p_url'] ?? '',
      urlGoogleSlides: json['field_google_slide'][0]['value'] ?? '',
      urlMicrosoftDocs: json['field_google_docs'][0]['url'] ?? '',
      urlPdf: json['field_pdf'][0]['url'] ?? '',
      urlZoomClass: urlZoom,
      detail: json['field_overview'][0]['value'],
      isCompleted: false,
      hasPermission: false,
    );
  }

  factory Lesson.fromJsonGetCourse(Map<String, dynamic> json) {
    final id = int.tryParse(json['lms_lesson_id'][0]['value'] ?? '0') ?? 0;
    final name = json['name'][0]['value'] ?? 0;
    final fieldFree = json['field_free'][0]['value'] ?? '0';
    var urlScorm = '';
    if (json['field_scorm'] != null && json['field_scorm'].isNotEmpty) {
      urlScorm = json['field_scorm'][0]['url_app_play_scorme'] ?? '';
    }
    var urlVideoYoutube = '';
    if (json['field_embedded_video'] != null &&
        json['field_embedded_video'].isNotEmpty) {
      urlVideoYoutube = json['field_embedded_video'][0]['value'] ?? '';
    }
    var urlVideo = '';
    if (json['field_video_url'] != null && json['field_video_url'].isNotEmpty) {
      urlVideo = json['field_video_url'][0]['value'] ?? '';
    }
    final List listScorm = json['field_scorm'];
    ZoomClassData? zoomClassData;
    if (json['field_zoom_class_data'] != null &&
        json['field_zoom_class_data'].isNotEmpty) {
      zoomClassData = ZoomClassData.fromJson(
        jsonDecode(json['field_zoom_class_data'][0]['value']),
      );
    }
    final listH5p = json['field_h5p_content'];
    var urlVideoH5p = '';
    if (listH5p != null && listH5p.isNotEmpty) {
      urlVideoH5p = listH5p[0]['h5p_url'] ?? '';
    }
    final htmlBody = json['field_body'];
    var body = '';
    if (htmlBody != null && (htmlBody as List).isNotEmpty) {
      body = json['field_body'][0]['value'] ?? '';
    }

    final String lessonType = (json['field_lesson_type'] as List).isNotEmpty
        ? json['field_lesson_type'][0]
        : LessonType.scorm.value;

    var urlSlides = '';
    if (json['field_google_slide'] != null &&
        (json['field_google_slide'] as List).isNotEmpty) {
      final url = json['field_google_slide'][0]['value'];
      final fragment = html_parser.parseFragment(url);
      final iframe = fragment.querySelector('iframe');
      urlSlides = iframe?.attributes['src'] ?? '';
    }
    final urlZoomList = json['field_zoom_class_link'];
    var urlZoom = '';
    if (urlZoomList != null && urlZoomList.isNotEmpty) {
      urlZoom = urlZoomList[0]['uri'] ?? '';
    }
    return Lesson(
      id: id,
      title: name,
      description: '',
      whatWillYouLearn: '',
      type: lessonType,
      isFree: fieldFree == '1',
      thumbnail: '',
      link: '',
      lessonFolderPath: '',
      urlScorm: urlScorm,
      urlVideoEmbedded: urlVideoYoutube,
      listScorm: listScorm
          .map((e) => LessonScorm.fromJson(e as Map<String, dynamic>))
          .toList(),
      body: body,
      zoomClassData: zoomClassData,
      urlVideo: urlVideo,
      urlVideoH5p: urlVideoH5p,
      urlGoogleSlides: urlSlides,
      urlMicrosoftDocs:
          parseJsonDrupal(json['field_google_docs'], subKey: 'url') ?? '',
      urlPdf: parseJsonDrupal(json['field_pdf'], subKey: 'url') ?? '',
      urlZoomClass: urlZoom,
      isCompleted: json['lesson_complete'] ?? false,
      hasPermission: false,
      detail: parseJsonDrupal(json['field_overview']),
    );
  }
  late int id;
  late String title;
  late String description;
  late String whatWillYouLearn;
  late String type;
  String? duration;
  String? detail;
  late bool isFree;
  late String thumbnail;
  late dynamic link;
  late String lessonFolderPath;
  late List<LessonScorm> listScorm;
  late String body;
  late String urlScorm;
  late String urlVideoEmbedded;
  late String urlVideo;
  late String urlVideoH5p;
  late String urlGoogleSlides;
  late String urlMicrosoftDocs;
  late String urlPdf;
  late String urlZoomClass;
  late ZoomClassData? zoomClassData;
  late bool isCompleted = false;
  late bool hasPermission = false;

  Map<String, Object?> toJson() => {
        'lms_lesson_id': id,
        'name': title,
        'description': description,
        'what_will_you_learn': whatWillYouLearn,
        'field_lesson_type': type,
        'duration': duration,
        'is_free': isFree,
        'thumbnail': thumbnail,
        'link': link,
        'body': body,
        'field_scorm': urlScorm,
        'lessonFolderPath': lessonFolderPath,
        'lesson_complete': isCompleted,
        'list_scorm': listScorm.map((e) => e.toJson()).toList(),
        'field_video_url': urlVideo,
        'field_google_slide': urlGoogleSlides,
        'field_google_docs': urlMicrosoftDocs,
        'field_pdf': urlPdf,
      };
}

enum LessonType {
  embeddedVideo('Embedded video'),
  uploadVideo('Video Upload'),
  meet('Google meet'),
  ggSlides('Google Slide'),
  pdf('PDF'),
  microsoftDocs('Microsoft Docs'),
  h5p('H5P Only'),
  scorm('Scorm Only'),
  zoom('Zoom class');

  const LessonType(this.value);
  final String value;
}

class LessonScorm {
  LessonScorm({
    this.targetId,
    // this.display,
    this.description,
    this.targetType,
    this.targetUuid,
    this.url,
  });

  LessonScorm.fromJson(Map<String, dynamic> json) {
    if (json['target_id'] is String) {
      targetId = int.tryParse(json['target_id']);
    } else {
      targetId = json['target_id'];
    }
    // display = json['display'];
    description = json['description'];
    targetType = json['target_type'];
    targetUuid = json['target_uuid'];
    url = json['url'];
  }
  int? targetId;
  // bool? display;
  String? description;
  String? targetType;
  String? targetUuid;
  String? url;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['target_id'] = targetId;
    // data['display'] = display;
    data['description'] = description;
    data['target_type'] = targetType;
    data['target_uuid'] = targetUuid;
    data['url'] = url;
    return data;
  }
}
