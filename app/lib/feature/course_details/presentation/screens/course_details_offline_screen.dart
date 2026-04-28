import 'dart:convert';
import 'dart:io';

import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/course_details/presentation/widgets/course_curriculum_offline.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

class CourseDetailsOfflineScreen extends StatefulWidget {
  const CourseDetailsOfflineScreen({this.courseArg, super.key});
  final Course? courseArg;

  @override
  State<CourseDetailsOfflineScreen> createState() =>
      _CourseDetailsOfflineScreenState();
}

class _CourseDetailsOfflineScreenState
    extends State<CourseDetailsOfflineScreen> {
  Course? course;
  bool _isLoadingLesson = true;

  @override
  void initState() {
    super.initState();
    course = widget.courseArg;
    if (course != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _getChapters(course!);
      });
    }
  }

  Future<Map<String, dynamic>> _convertFileToJson(String path) async {
    final file = File(path);
    if (file.existsSync()) {
      final data = file.readAsStringSync();
      return json.decode(data) as Map<String, dynamic>;
    }
    return {};
  }

  Future<void> _getChapters(Course course) async {
    setState(() => _isLoadingLesson = true);
    course.listChapters = [];
    final sharedPreferences = await SharedPreferences.getInstance();
    final uid = sharedPreferences.getString(AppConstants.uid);
    final appDocDir = await getApplicationDocumentsDirectory();

    final courseJson = await _convertFileToJson(
      '${appDocDir.path}/$uid/courses/${course.lmsUserCourseId}/jsonCourse.json',
    );
    final courseDescription = courseJson['field_body'];
    final listChapterFolderPath =
        '${appDocDir.path}/$uid/courses/${course.lmsUserCourseId}/chapters';
    final listChapterDir = Directory(listChapterFolderPath);

    if (listChapterDir.existsSync()) {
      final chapters = listChapterDir.listSync();
      chapters
          .sort((a, b) => a.statSync().changed.compareTo(b.statSync().changed));

      for (var idxC = 0; idxC < chapters.length; idxC++) {
        final chapterPath = chapters[idxC].path;
        final jsonChapter =
            await _convertFileToJson('${chapters[idxC].path}/jsonChapter.json');
        final chapter = Chapter.fromJsonOffline(jsonChapter);
        final chapterDir = Directory('$chapterPath/lessons');

        final lessons = chapterDir.listSync();
        lessons.sort(
          (a, b) => a.statSync().changed.compareTo(b.statSync().changed),
        );
        for (var idx = 0; idx < lessons.length; idx++) {
          final lastPath = lessons[idx].path.split('/').last;
          if (int.tryParse(lastPath) is int) {
            final lessonFolderPath = lessons[idx].path;
            final jsonLesson =
                await _convertFileToJson('$lessonFolderPath/jsonLesson.json');
            final lesson = Lesson.fromJsonGetCourse(jsonLesson);
            var urlVideo = '';
            if (jsonLesson['field_video_url'] != null &&
                (jsonLesson['field_video_url'] as List).isNotEmpty) {
              urlVideo =
                  jsonLesson['field_video_url'][0]['value'] as String? ?? '';
              final file = File('$lessonFolderPath/video.mp4');
              if (file.existsSync()) {
                urlVideo = file.path;
              }
            }
            lesson.description = courseDescription as String? ?? '';
            lesson.whatWillYouLearn = course.fieldWhatYouLearn ?? '';
            lesson.urlVideo = urlVideo;
            lesson.lessonFolderPath = lessonFolderPath;
            chapter.lessons?.add(lesson);
          }
        }
        course.listChapters?.add(chapter);
      }
    }
    setState(() => _isLoadingLesson = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: course?.title ?? 'Back',
        titleColor: Theme.of(context).colorScheme.onSurface,
        bgColor: Theme.of(context).canvasColor,
        actions: const [],
      ),
      body: _mainUI(context),
    );
  }

  Widget _mainUI(BuildContext context) {
    if (course == null) return const SizedBox();
    if (_isLoadingLesson) return const LoadingIndicator();
    if (course!.listChapters?.isNotEmpty ?? false) {
      return Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: SingleChildScrollView(
          child: CourseCurriculumOffline(
            chapters: course!.listChapters ?? [],
          ),
        ),
      );
    }
    return const SizedBox.shrink();
  }
}
