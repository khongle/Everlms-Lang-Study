import 'dart:io';

import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:everlms/feature/class_room/data/models/classroom.dart';
import 'package:everlms/feature/class_room/data/repositories/classroom_repository.dart';
import 'package:everlms/feature/class_room/presentation/widgets/error_dialog.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';
import 'package:everlms/main.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:http_server/http_server.dart' as http_server;
import 'package:open_filex/open_filex.dart';
import 'package:path/path.dart' as path;
import 'package:url_launcher/url_launcher.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final classroomRepositoryProvider = Provider<ClassroomRepository>((ref) {
  return ClassroomRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class ClassroomState {
  const ClassroomState({
    this.classroom,
    this.sections = const [],
    this.isLoading = false,
    this.selectedVideoUrl,
    this.isFullScreen = false,
    this.filePdf,
  });

  final Classroom? classroom;
  final List<Chapter> sections;
  final bool isLoading;
  final String? selectedVideoUrl;
  final bool isFullScreen;
  final File? filePdf;

  ClassroomState copyWith({
    Classroom? classroom,
    List<Chapter>? sections,
    bool? isLoading,
    String? selectedVideoUrl,
    bool? isFullScreen,
    File? filePdf,
  }) {
    return ClassroomState(
      classroom: classroom ?? this.classroom,
      sections: sections ?? this.sections,
      isLoading: isLoading ?? this.isLoading,
      selectedVideoUrl: selectedVideoUrl ?? this.selectedVideoUrl,
      isFullScreen: isFullScreen ?? this.isFullScreen,
      filePdf: filePdf ?? this.filePdf,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class ClassroomNotifier extends AsyncNotifier<ClassroomState> {
  @override
  Future<ClassroomState> build() async {
    return const ClassroomState();
  }

  Future<void> loadSections(int courseID) async {
    final current = state.valueOrNull ?? const ClassroomState();
    state = AsyncData(current.copyWith(isLoading: true));

    final repo = ref.read(classroomRepositoryProvider);
    final response = await repo.getClassroomData(courseID: courseID);

    final sections = <Chapter>[];
    if (response != null && response.statusCode == 200) {
      final data = response.body['data'];
      if (data is Map<String, dynamic>) {
        for (final entry in data.values) {
          sections.add(Chapter.fromJson(entry as Map<String, dynamic>));
        }
      }
    }

    state = AsyncData(
      current.copyWith(
        sections: sections,
        isLoading: false,
      ),
    );
  }

  Future<void> saveProgress({
    required int courseId,
    required String lessonID,
    required String sectionID,
    required String totalSpentTime,
    required String totalDuration,
  }) async {
    if (!ref.read(authProvider.notifier).isLoggedIn()) return;
    final repo = ref.read(classroomRepositoryProvider);
    await repo.saveProgress(
      id: courseId,
      lessonID: lessonID,
      sectionID: sectionID,
      totalSpentTime: totalSpentTime,
      totalDuration: totalDuration,
    );
  }

  void updateVideoUrl(String? url) {
    final current = state.valueOrNull;
    if (current == null) return;
    state = AsyncData(current.copyWith(selectedVideoUrl: url));
  }

  // ---- Full screen ----

  void toggleFullScreen() {
    final current = state.valueOrNull;
    if (current == null) return;
    final newValue = !current.isFullScreen;
    if (newValue) {
      SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
    } else {
      SystemChrome.setEnabledSystemUIMode(
        SystemUiMode.manual,
        overlays: SystemUiOverlay.values,
      );
    }
    state = AsyncData(current.copyWith(isFullScreen: newValue));
  }

  void exitFullScreen() {
    final current = state.valueOrNull;
    if (current == null) return;
    if (current.isFullScreen) {
      SystemChrome.setEnabledSystemUIMode(
        SystemUiMode.manual,
        overlays: SystemUiOverlay.values,
      );
      state = AsyncData(current.copyWith(isFullScreen: false));
    }
  }

  // ---- File helpers ----

  Future<File?> downloadLessonPdf(String url) async {
    final file = await downloadUrlToCache(url);
    final current = state.valueOrNull;
    if (current != null) {
      state = AsyncData(current.copyWith(filePdf: file));
    }
    return file;
  }

  Future<File?> fetchOfflineFilePDF(String lessonFolderPath) async {
    final dir = Directory(lessonFolderPath);
    for (final file in dir.listSync()) {
      if (path.extension(file.path).contains('pdf')) {
        return File(file.path);
      }
    }
    return null;
  }

  Future<File?> fetchOfflineFilePPTX(String lessonFolderPath) async {
    final dir = Directory(lessonFolderPath);
    for (final file in dir.listSync()) {
      if (path.extension(file.path).contains('pptx')) {
        return File(file.path);
      }
    }
    return null;
  }

  Future<void> openDocUrl(String url) async {
    if (url.contains('pptx')) {
      final plainUrl = url.split('/').last;
      final filename = Uri.decodeFull(plainUrl);
      final file = await downloadPDF(url, filename);
      await openMicrosoftFile(file.path);
    } else if (url.contains('pdf')) {
      final current = state.valueOrNull;
      if (current?.filePdf == null) {
        await downloadLessonPdf(url);
      }
      await openPdfFile(state.valueOrNull?.filePdf?.path ?? '');
    }
  }

  Future<void> openPdfFile(String filePath) async {
    if (Platform.isAndroid) {
      final result = await OpenFilex.open(filePath);
      if (result.type == ResultType.noAppToOpen) {
        final url = Uri.parse(
          'https://play.google.com/store/search?q=pdf%20viewer&c=apps',
        );
        if (await canLaunchUrl(url)) {
          await launchUrl(url, mode: LaunchMode.externalApplication);
        }
      }
    }
  }

  Future<void> openMicrosoftFile(String filePath) async {
    if (Platform.isAndroid) {
      final result = await OpenFilex.open(filePath);
      if (result.type == ResultType.noAppToOpen) {
        const url =
            'https://play.google.com/store/apps/details?id=com.microsoft.office.powerpoint';
        if (await canLaunchUrl(Uri.parse(url))) {
          await launchUrl(
            Uri.parse(url),
            mode: LaunchMode.externalApplication,
          );
        }
      }
    } else if (Platform.isIOS) {
      await OpenFilex.open(filePath);
    }
  }

  // ---- Local server (SCORM) ----

  Future<void> startLocalServer(String serverPath) async {
    try {
      if (server != null) {
        await server?.close(force: true);
        server = null;
      }
      final scormPath = Directory(serverPath);
      server = await HttpServer.bind(
        InternetAddress.loopbackIPv4,
        8080,
        shared: true,
      );
      final virDir = http_server.VirtualDirectory(scormPath.path)
        ..allowDirectoryListing = true
        ..jailRoot = false
        ..followLinks = true
        ..errorPageHandler = (request) {
          request.response
            ..statusCode = HttpStatus.notFound
            ..write('Not Found')
            ..close();
        };
      server?.listen(virDir.serveRequest);
    } on Exception {
      ErrorDialog.show(navigatorKey.currentContext, () {
        startLocalServer(serverPath);
      });
    }
  }

  Future<void> stopLocalServer() async {
    if (server != null) {
      await server?.close(force: true);
      server = null;
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final classroomProvider =
    AsyncNotifierProvider<ClassroomNotifier, ClassroomState>(
  ClassroomNotifier.new,
);
