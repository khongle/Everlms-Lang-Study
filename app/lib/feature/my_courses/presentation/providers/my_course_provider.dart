import 'dart:convert';
import 'dart:io';

import 'package:archive/archive.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/my_courses/data/repositories/my_course_repository.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:http/http.dart' as http;
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final myCourseRepositoryProvider = Provider<MyCourseRepository>((ref) {
  return MyCourseRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class MyCourseState {
  const MyCourseState({
    this.courses = const [],
    this.isLoading = false,
    this.downloadedCourseIDs = const {},
    this.isDownloading = false,
    this.progressDownloadCourse = 0,
    this.statusDownload = '',
  });

  final List<LMSMyCourseModel> courses;
  final bool isLoading;
  final Set<String> downloadedCourseIDs;
  final bool isDownloading;
  final double progressDownloadCourse;
  final String statusDownload;

  MyCourseState copyWith({
    List<LMSMyCourseModel>? courses,
    bool? isLoading,
    Set<String>? downloadedCourseIDs,
    bool? isDownloading,
    double? progressDownloadCourse,
    String? statusDownload,
  }) {
    return MyCourseState(
      courses: courses ?? this.courses,
      isLoading: isLoading ?? this.isLoading,
      downloadedCourseIDs: downloadedCourseIDs ?? this.downloadedCourseIDs,
      isDownloading: isDownloading ?? this.isDownloading,
      progressDownloadCourse:
          progressDownloadCourse ?? this.progressDownloadCourse,
      statusDownload: statusDownload ?? this.statusDownload,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class MyCourseNotifier extends AsyncNotifier<MyCourseState> {
  @override
  Future<MyCourseState> build() async {
    return const MyCourseState();
  }

  Future<void> loadMyCourses() async {
    final current = state.valueOrNull ?? const MyCourseState();
    state = AsyncData(current.copyWith(isLoading: true));

    final repo = ref.read(myCourseRepositoryProvider);
    final prefs = ref.read(sharedPreferencesProvider);
    final uid = prefs.getString(AppConstants.uid) ?? '';

    final response = await repo.getMyCourseList(uid);

    final courses = <LMSMyCourseModel>[];
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      if (data is Map<String, dynamic>) {
        for (final entry in data.values) {
          courses.add(
            LMSMyCourseModel.fromJson(entry as Map<String, dynamic>),
          );
        }
      }
    }

    state = AsyncData(
      current.copyWith(
        courses: courses,
        isLoading: false,
      ),
    );
  }

  void updateProgress(double progress, String courseId) {
    final current = state.valueOrNull;
    if (current == null) return;
    final updated = current.courses.map((c) {
      if (c.id == courseId) {
        return c..progress = progress;
      }
      return c;
    }).toList();
    state = AsyncData(current.copyWith(courses: updated));
  }

  Future<void> getDownloadedCourseIDs() async {
    final prefs = ref.read(sharedPreferencesProvider);
    final uid = prefs.getString(AppConstants.uid);
    final appDocDir = await getApplicationDocumentsDirectory();
    final listCourseFolderPath = '${appDocDir.path}/$uid/courses/';
    final listCourseDir = Directory(listCourseFolderPath);
    final ids = <String>{};
    if (listCourseDir.existsSync()) {
      final entities = listCourseDir.listSync();
      for (final entity in entities) {
        ids.add(entity.path.split('/').last);
      }
    }
    final current = state.valueOrNull ?? const MyCourseState();
    state = AsyncData(current.copyWith(downloadedCourseIDs: ids));
  }

  Future<void> downloadAndExtractZip(LMSMyCourseModel course) async {
    try {
      var current = state.valueOrNull ?? const MyCourseState();
      state = AsyncData(
        current.copyWith(
          statusDownload: 'Downloading',
          isDownloading: true,
          progressDownloadCourse: 0,
        ),
      );

      final prefs = ref.read(sharedPreferencesProvider);
      final uid = prefs.getString(AppConstants.uid) ?? '';
      final apiClient = ref.read(apiClientProvider);

      final appDocDir = await getApplicationDocumentsDirectory();
      final courseFolderPath =
          '${appDocDir.path}/$uid/courses/${course.lmsUserCourseId}';
      await _createIfNotExist(courseFolderPath);
      await _writeAsString(
        '$courseFolderPath/jsonCourse.json',
        course.toJson(),
      );
      _saveImageCourse(
        course.fieldImage ?? '',
        courseFolderPath,
        course.id ?? '',
      );

      final repo = ref.read(myCourseRepositoryProvider);
      final responseChapters =
          await repo.getListChapter((course.id ?? 0).toString());
      final data = responseChapters?.body['data'];

      var chapters = <Chapter>[];
      if (data != null) {
        chapters = List<Chapter>.from(
          (data as Map<String, dynamic>).values.map((e) {
            return Chapter.fromJson(e);
          }),
        );
      }
      final capacityCourse =
          await _calculateCapacityCourse(chapters, apiClient.auth);
      double capacityLesson = 0;
      final listChapterJson = List.from((data as Map<String, dynamic>).values);

      for (final chapterJson in listChapterJson) {
        final lessonsJson = chapterJson['field_lesson'];
        final lessons = List.from((lessonsJson as Map<String, dynamic>).values);
        final chapter = Chapter.fromJson(chapterJson);
        final chapterFolderPath = '$courseFolderPath/chapters/${chapter.id}';
        await _createIfNotExist(chapterFolderPath);
        await _writeAsString(
          '$chapterFolderPath/jsonChapter.json',
          chapter.toJson(),
        );
        for (final lessonJson in lessons) {
          final lesson = Lesson.fromJsonGetCourse(lessonJson);
          final lessonFolderPath = '$chapterFolderPath/lessons/${lesson.id}';
          await _createIfNotExist(lessonFolderPath);
          await _writeAsString(
            '$lessonFolderPath/jsonLesson.json',
            lessonJson,
          );
          capacityLesson += await _downloadLesson(
            lesson,
            lessonFolderPath,
            apiClient.auth,
            prefs,
          );
          if (capacityCourse != 0) {
            current = state.valueOrNull ?? const MyCourseState();
            state = AsyncData(
              current.copyWith(
                progressDownloadCourse: capacityLesson / capacityCourse,
              ),
            );
          }
        }
      }

      current = state.valueOrNull ?? const MyCourseState();
      final updatedIDs = {...current.downloadedCourseIDs};
      updatedIDs.add(course.lmsUserCourseId ?? '');
      state = AsyncData(
        current.copyWith(
          statusDownload: 'Done',
          isDownloading: false,
          downloadedCourseIDs: updatedIDs,
        ),
      );
    } on Exception catch (e) {
      if (kDebugMode) print(e);
      final current = state.valueOrNull ?? const MyCourseState();
      state = AsyncData(
        current.copyWith(
          statusDownload: 'Failed to download',
          isDownloading: false,
        ),
      );
    }
  }

  Future<void> removeOfflineCourse(LMSMyCourseModel course) async {
    try {
      final prefs = ref.read(sharedPreferencesProvider);
      final uid = prefs.getString(AppConstants.uid) ?? '';
      final appDocDir = await getApplicationDocumentsDirectory();
      final courseFolderPath =
          '${appDocDir.path}/$uid/courses/${course.lmsUserCourseId}';
      final courseDir = Directory(courseFolderPath);

      if (courseDir.existsSync()) {
        courseDir.deleteSync(recursive: true);
      }
      final current = state.valueOrNull ?? const MyCourseState();
      final updatedIDs = {...current.downloadedCourseIDs};
      updatedIDs.remove(course.lmsUserCourseId);
      state = AsyncData(
        current.copyWith(
          statusDownload: 'Done',
          downloadedCourseIDs: updatedIDs,
        ),
      );
    } on Exception {
      final current = state.valueOrNull ?? const MyCourseState();
      state = AsyncData(
        current.copyWith(
          statusDownload: 'Fail to remove',
        ),
      );
    }
  }

  // ---- Private helpers for download ----

  Future<void> _createIfNotExist(String path) async {
    final dir = Directory(path);
    if (!dir.existsSync()) {
      dir.createSync(recursive: true);
    }
  }

  Future<void> _writeAsString(
    String filePath,
    Map<String, dynamic> data,
  ) async {
    final file = File(filePath);
    await Future.delayed(
      const Duration(milliseconds: 200),
      () => file.writeAsStringSync(json.encode(data)),
    );
  }

  Future<double> _downloadVideo(
    List<String> list,
    String lessonPath,
    String? auth,
  ) async {
    double capacity = 0;
    for (var i = 0; i < list.length; i++) {
      final urlVideo = list[i];
      final response = await http.get(
        Uri.parse(urlVideo),
        headers: {'Authorization': auth ?? ''},
      );
      if (response.statusCode == 200) {
        final file = File('$lessonPath/video.mp4');
        await file.writeAsBytes(response.bodyBytes);
        capacity += response.bodyBytes.length.toDouble();
      }
    }
    return capacity;
  }

  Future<double> _downloadAndExtractScorm(
    List<LessonScorm> listScorm,
    String lessonPath,
    String? auth,
  ) async {
    double capacity = 0;
    for (var i = 0; i < listScorm.length; i++) {
      final urlScorm = listScorm[i].url ?? '';
      if (listScorm.isNotEmpty) {
        final response = await http.get(
          Uri.parse(urlScorm),
          headers: {'Authorization': auth ?? ''},
        );
        if (response.statusCode == 200) {
          capacity += response.bodyBytes.length.toDouble();
          final appDocDir = await getApplicationDocumentsDirectory();
          final name = p.basenameWithoutExtension(urlScorm);
          final zipFilePath = '${appDocDir.path}/$name.zip';
          final zipFile = File(zipFilePath);

          await zipFile.writeAsBytes(response.bodyBytes);
          final bytes = zipFile.readAsBytesSync();
          final archive = ZipDecoder().decodeBytes(bytes);

          for (final file in archive) {
            final filename = '$lessonPath/${file.name}';
            if (file.isFile) {
              final data = file.content as List<int>;
              File(filename)
                ..createSync(recursive: true)
                ..writeAsBytesSync(data);
            } else {
              await Directory(filename).create(recursive: true);
            }
          }
          await zipFile.delete();
        }
      }
    }
    return capacity;
  }

  Future<double> _downloadDocument(
    List<String> listUrl,
    String lessonPath,
    SharedPreferences prefs,
  ) async {
    double capacity = 0;
    final listDocumentUrl = listUrl.where((url) => url.isNotEmpty).toList();
    final cookie = prefs.getString(AppConstants.cookie);
    final basicAuth = prefs.getString(AppConstants.basicAuth);
    for (var i = 0; i < listDocumentUrl.length; i++) {
      final url = listDocumentUrl[i];
      final response = await http.get(
        Uri.parse(url),
        headers: {'Authorization': basicAuth ?? '', 'Cookie': cookie ?? ''},
      );
      final uri = url.split('/').last;
      final fileName = Uri.decodeFull(uri);
      final filePath = '$lessonPath/$fileName';
      if (response.statusCode == 200) {
        final file = File(filePath);
        await file.writeAsBytes(response.bodyBytes);
        capacity += response.bodyBytes.length.toDouble();
      }
    }
    return capacity;
  }

  Future<double> _calculateCapacityCourse(
    List<Chapter>? chapters,
    String? auth,
  ) async {
    double totalCapacityCourse = 0;
    final listUrl = <String>[];
    for (final chapter in chapters ?? []) {
      final listLesson = chapter.lessons ?? [];
      for (final lesson in listLesson) {
        if (lesson.urlVideo.isNotEmpty) {
          listUrl.add(lesson.urlVideo);
        }
        for (var i = 0; i < lesson.listScorm.length; i++) {
          final urlScorm = lesson.listScorm[i].url ?? '';
          if (urlScorm.isNotEmpty) {
            listUrl.add(urlScorm);
          }
        }
        if (lesson.urlMicrosoftDocs.isNotEmpty) {
          listUrl.add(lesson.urlMicrosoftDocs);
        }
        if (lesson.urlPdf.isNotEmpty) {
          listUrl.add(lesson.urlPdf);
        }
      }
    }
    for (final url in listUrl) {
      final response = await http.head(Uri.parse(url));
      final len = response.headers['content-length'];
      totalCapacityCourse += len != null ? double.parse(len) : 0;
    }
    return totalCapacityCourse;
  }

  Future<void> _saveImageCourse(
    String imageUrl,
    String coursePath,
    String courseID,
  ) async {
    if (imageUrl.isEmpty) return;
    final response = await http.get(Uri.parse(imageUrl));
    final imageExtension = p.extension(imageUrl);
    final fileName = 'thumbnail_course_$courseID$imageExtension';
    final file = File('$coursePath/$fileName');
    await file.writeAsBytes(response.bodyBytes);
  }

  Future<double> _downloadLesson(
    Lesson lesson,
    String path,
    String? auth,
    SharedPreferences prefs,
  ) async {
    double capacity = 0;
    if (lesson.urlVideo.isNotEmpty) {
      capacity += await _downloadVideo([lesson.urlVideo], path, auth);
    }
    if (lesson.listScorm.isNotEmpty) {
      capacity += await _downloadAndExtractScorm(lesson.listScorm, path, auth);
    }
    final listUrlDocument = <String>[lesson.urlMicrosoftDocs, lesson.urlPdf];
    return capacity += await _downloadDocument(listUrlDocument, path, prefs);
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final myCourseProvider = AsyncNotifierProvider<MyCourseNotifier, MyCourseState>(
  MyCourseNotifier.new,
);
