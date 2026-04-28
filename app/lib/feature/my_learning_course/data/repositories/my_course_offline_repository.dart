import 'dart:convert';
import 'dart:io';

import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';
import 'package:everlms/feature/root/data/models/common/my_course_offline.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:xml/xml.dart';

abstract interface class MyCourseOfflineRepository {
  /// Returns courses downloaded as JSON (new format — used in [MyCourseOfflineScreen]).
  Future<List<MyCourseOffline>> getDownloadedCourseList();

  /// Returns SCORM courses from local storage (used in [DownloadedCourseList]).
  Future<List<MyCourseOffline>> getScormCourseList();
}

class MyCourseOfflineRepositoryImpl implements MyCourseOfflineRepository {
  const MyCourseOfflineRepositoryImpl(this._prefs);

  final SharedPreferences _prefs;

  @override
  Future<List<MyCourseOffline>> getDownloadedCourseList() async {
    final uid = _prefs.getString(AppConstants.uid);
    if (uid == null) return [];

    final appDocDir = await getApplicationDocumentsDirectory();
    final listCourseFolderPath = '${appDocDir.path}/$uid/courses/';
    final listCourseDir = Directory(listCourseFolderPath);

    if (!listCourseDir.existsSync()) return [];

    final entities = listCourseDir.listSync();
    final result = <MyCourseOffline>[];

    for (final entity in entities) {
      final courseFolderPath = entity.path;
      final jsonFile = File('$courseFolderPath/jsonCourse.json');
      if (!jsonFile.existsSync()) continue;

      final data = await jsonFile.readAsString();
      final jsonCourse = json.decode(data) as Map<String, dynamic>;
      final course = LMSMyCourseModel.fromJsonOffline(jsonCourse);
      final thumbnailCourse =
          File('$courseFolderPath/thumbnail_course_${course.id}.png');

      result.add(
        MyCourseOffline(
          id: course.lmsUserCourseId,
          thumbnail: thumbnailCourse.path,
          title: course.courseName,
          course: course,
        ),
      );
    }

    return result;
  }

  @override
  Future<List<MyCourseOffline>> getScormCourseList() async {
    final directory = await getApplicationDocumentsDirectory();
    final folderPath = '${directory.path}/scorm';
    final folder = Directory(folderPath);

    if (!folder.existsSync()) return [];

    final entities = folder.listSync();
    final result = <MyCourseOffline>[];

    for (final entity in entities) {
      if (entity is! Directory) continue;

      final manifestPath = '${entity.path}/imsmanifest.xml';
      final manifestFile = File(manifestPath);
      if (!manifestFile.existsSync()) continue;

      final document = XmlDocument.parse(manifestFile.readAsStringSync());

      final scoItems = document.findAllElements('resource').where(
            (e) =>
                e.getAttribute('adlcp:scormType') == 'sco' &&
                e.getAttribute('href') != null,
          );
      if (scoItems.isEmpty) continue;
      final scoItem = scoItems.first;

      final idString = scoItem.getAttribute('identifier');
      final url =
          '${entity.path.split('/').last}/${scoItem.getAttribute('href')}';

      final assetItems = document.findAllElements('resource').where(
            (e) =>
                e.getAttribute('adlcp:scormType') == 'asset' &&
                e.getAttribute('href') != null,
          );
      if (assetItems.isEmpty) continue;
      final assetItem = assetItems.first;
      final thumbnailString =
          '${entity.path}/${assetItem.getAttribute('href')}';

      final descriptionElement =
          document.findAllElements('description').firstOrNull;
      final stringElement =
          descriptionElement?.findElements('string').firstOrNull;
      final descriptionText = stringElement?.innerText ?? '';

      result.add(
        MyCourseOffline(
          id: idString,
          thumbnail: thumbnailString,
          title: descriptionText,
          url: url,
        ),
      );
    }

    return result;
  }
}
