import 'dart:async';
import 'dart:io';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/class_room/presentation/providers/better_video_player.dart';
import 'package:everlms/feature/class_room/presentation/providers/classroom_provider.dart';
import 'package:everlms/feature/class_room/presentation/widgets/classroom_button.dart';
import 'package:everlms/feature/class_room/presentation/widgets/lesson_body_section.dart';
import 'package:everlms/feature/class_room/presentation/widgets/lesson_information.dart';
import 'package:everlms/feature/class_room/presentation/widgets/video_player.dart';
import 'package:everlms/feature/class_room/presentation/widgets/web_view.dart';
import 'package:everlms/feature/my_learning_course/presentation/widgets/scorm_view.dart';
import 'package:everlms/feature/profile/presentation/providers/user_profile_provider.dart';
import 'package:everlms/feature/quiz/presentation/providers/quiz_provider.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:vimeo_video_player/vimeo_video_player.dart';
import 'package:xml/xml.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';

class LearningScreen extends ConsumerStatefulWidget {
  const LearningScreen({
    required this.lesson,
    required this.isOnline,
    required this.fromPage,
    super.key,
    this.fullscreenNotifier,
  });
  final Lesson lesson;
  final bool isOnline;
  final String? fromPage;
  final ValueNotifier<bool>? fullscreenNotifier;

  @override
  ConsumerState<LearningScreen> createState() => _LearningScreenState();
}

class _LearningScreenState extends ConsumerState<LearningScreen>
    with TickerProviderStateMixin {
  YoutubePlayerController? _youtubePlayerController;
  late InAppWebViewController webView;
  bool isShow = true;
  bool isLoading = true;
  Widget? _scorm;
  Future<File?>? _futureDownloadFile;
  late Lesson lesson;
  final pageNumberPDF = ValueNotifier<int>(0);
  late final MyBetterVideoPlayerController _videoController;

  @override
  void initState() {
    super.initState();
    _videoController = MyBetterVideoPlayerController();
    lesson = widget.lesson;
    ref.read(quizProvider.notifier).getQuizDataByLessonId(lesson.id.toString());
    if (lesson.urlVideoEmbedded.isNotEmpty &&
        lesson.urlVideoEmbedded.contains('youtube') &&
        widget.isOnline) {
      final videoId =
          YoutubePlayer.convertUrlToId(lesson.urlVideoEmbedded) ?? '';

      _youtubePlayerController = YoutubePlayerController(
        initialVideoId: videoId,
        flags: const YoutubePlayerFlags(autoPlay: false),
      );
      if (_youtubePlayerController != null) {
        _youtubePlayerController!.addListener(_youtubeListener);
      }
    }
    if (lesson.urlVideo.isNotEmpty) {
      _videoController.playVideo(lesson.urlVideo, isOnline: widget.isOnline);
    }
    if (lesson.listScorm.isNotEmpty) {
      goToScorm(
        lesson.listScorm.first,
        lesson.urlScorm,
        lesson.title,
        lesson.id.toString(),
      );
    }
    final classroomNotifier = ref.read(classroomProvider.notifier);
    if (lesson.urlPdf.isNotEmpty && widget.isOnline) {
      _futureDownloadFile = classroomNotifier.downloadLessonPdf(lesson.urlPdf);
    }
    if (lesson.type == LessonType.pdf.value && !widget.isOnline) {
      _futureDownloadFile =
          classroomNotifier.fetchOfflineFilePDF(lesson.lessonFolderPath);
    }
    if (lesson.type == LessonType.microsoftDocs.value && !widget.isOnline) {
      _futureDownloadFile =
          classroomNotifier.fetchOfflineFilePPTX(lesson.lessonFolderPath);
    }
  }

  void _youtubeListener() {
    final isFs = _youtubePlayerController!.value.isFullScreen;
    if (widget.fullscreenNotifier?.value != isFs) {
      widget.fullscreenNotifier?.value = isFs;
    }
  }

  Future<void> goToScorm(
    LessonScorm scorm,
    String urlScorm,
    String title,
    String lessonId,
  ) async {
    if (lesson.lessonFolderPath.isNotEmpty) {
      await ref
          .read(classroomProvider.notifier)
          .startLocalServer(lesson.lessonFolderPath);
      final folder = Directory(lesson.lessonFolderPath);
      if (folder.existsSync()) {
        final manifestPath = '${lesson.lessonFolderPath}/imsmanifest.xml';
        final manifestFile = File(manifestPath);
        final document = XmlDocument.parse(manifestFile.readAsStringSync());
        final scoItem = document.findAllElements('resource').firstWhere(
              (element) =>
                  element.getAttribute('adlcp:scormType') == 'sco' &&
                  element.getAttribute('href') != null,
            );

        final url = '${scoItem.getAttribute('href')}';
        _scorm = ScormViewer(
          title: title,
          lessonId: lessonId,
          scormContentPath: 'http://127.0.0.1:8080/$url',
          isOnline: widget.isOnline,
        );
        setState(() {});
      }
    } else {
      if (urlScorm.isNotEmpty) {
        setState(() {
          _scorm = ScormViewer(
            title: title,
            lessonId: lessonId,
            scormContentPath: urlScorm,
            isOnline: widget.isOnline,
          );
        });
      }
    }
  }

  @override
  void dispose() {
    _videoController.stopPlayer();
    _videoController.dispose();
    _youtubePlayerController?.dispose();
    ref.read(classroomProvider.notifier).stopLocalServer();
    _youtubePlayerController?.removeListener(_youtubeListener);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!isShow) {
      return const Scaffold();
    }
    if (_youtubePlayerController == null) {
      return body(context);
    }
    return YoutubePlayerBuilder(
      player: YoutubePlayer(
        controller: _youtubePlayerController!,
        showVideoProgressIndicator: true,
        progressIndicatorColor: Colors.amber,
        progressColors: const ProgressBarColors(
          playedColor: Colors.red,
          handleColor: Colors.redAccent,
        ),
        onReady: () {
          // _controller?.addListener(listener);
          setState(() {
            isLoading = false;
          });
        },
      ),
      builder: (contextVideo, player) {
        return body(context, player: player);
      },
    );
  }

  Widget body(BuildContext context, {Widget? player}) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final classroomState = ref.watch(classroomProvider).valueOrNull;
    return Scaffold(
      backgroundColor: isDark ? Colors.grey.shade900 : Colors.grey.shade200,
      appBar: widget.fromPage == AppRoutes.courseDetailsScreenNotPurchase
          ? CustomAppBar(
              title: tl(context).back,
              centerTitle: false,
            )
          : null,
      body: (classroomState?.isLoading ?? false)
          ? const LoadingIndicator()
          : mainUI(context, player: player),
    );
  }

  Widget mainUI(BuildContext context, {Widget? player}) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        mainAxisSize: MainAxisSize.min,
        children: [
          lessonResource(
            player: player,
          ),
          LessonBodySection(lesson: lesson),
          LessonInformation(
            lesson: lesson,
          ),
        ],
      ),
    );
  }

  Widget lessonResource({Widget? player}) {
    // Video upload
    if (lesson.urlVideo.isNotEmpty &&
        lesson.type == LessonType.uploadVideo.value) {
      return videoUpload();
    }
    // Scorm
    if (lesson.listScorm.isNotEmpty && lesson.type == LessonType.scorm.value) {
      return scormOnly(context);
    }
    // Video H5P upload
    if (lesson.urlVideoH5p.isNotEmpty && lesson.type == LessonType.h5p.value) {
      return h5pOnly(context);
    }
    if (lesson.urlGoogleSlides.isNotEmpty &&
        lesson.type == LessonType.ggSlides.value) {
      return googleSlides(lesson.urlGoogleSlides);
    }
    if (lesson.urlMicrosoftDocs.isNotEmpty &&
        lesson.type == LessonType.microsoftDocs.value) {
      return microsoftDocs(context, lesson.urlMicrosoftDocs);
    }
    if (lesson.urlPdf.isNotEmpty && lesson.type == LessonType.pdf.value) {
      return pdfOnly();
    }
    // Video youtube
    if (lesson.urlVideoEmbedded.isNotEmpty &&
        lesson.urlVideoEmbedded.contains('youtube') &&
        lesson.type == LessonType.embeddedVideo.value) {
      return embeddedVideo(context, 'youtube', player);
    }
    if (lesson.urlVideoEmbedded.isNotEmpty &&
        lesson.urlVideoEmbedded.contains('vimeo') &&
        lesson.type == LessonType.embeddedVideo.value) {
      return embeddedVideo(context, 'vimeo', null);
    }
    // Zoom item
    if (lesson.zoomClassData != null && lesson.type == LessonType.zoom.value) {
      return zoomClass(lesson);
    }
    return scormOnly(context);
  }

  Widget pdfOnly() {
    return FutureBuilder(
      future: _futureDownloadFile,
      builder: (context, snapshot) {
        final isWaiting = snapshot.connectionState == ConnectionState.waiting;
        return Stack(
          alignment: Alignment.bottomLeft,
          children: [
            SizedBox(
              height: (ref.watch(classroomProvider).valueOrNull?.isFullScreen ??
                      false)
                  ? MediaQuery.of(context).size.height - kToolbarHeight
                  : MediaQuery.of(context).size.width * 1.2941,
              child: isWaiting
                  ? const LoadingIndicator()
                  : renderPdfWidget(snapshot.data?.path ?? ''),
            ),
            ValueListenableBuilder(
              valueListenable: pageNumberPDF,
              builder: (context, index, _) {
                return Positioned(
                  left: 20,
                  bottom: 20,
                  child: Text(
                    (index + 1).toString(),
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                );
              },
            ),
          ],
        );
      },
    );
  }

  Widget renderPdfWidget(String path) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
      child: PDFView(
        gestureRecognizers: {
          const Factory<OneSequenceGestureRecognizer>(
            EagerGestureRecognizer.new,
          ),
        }.toSet(),
        filePath: path,
        onPageChanged: (page, total) {
          pageNumberPDF.value = page ?? 1;
        },
      ),
    );
  }

  Widget microsoftDocs(BuildContext context, String fileUrl) {
    final viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?'
        'src=${Uri.encodeComponent(fileUrl)}';
    if (widget.isOnline) {
      return WebViewWidget(
        url: viewerUrl,
      );
    }
    return FutureBuilder(
      future: _futureDownloadFile,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const LoadingIndicator();
        }
        if (snapshot.hasError || !snapshot.hasData) {
          return Text(tl(context).something_wrong);
        }
        return Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Column(
            children: [
              Text(
                'This file type isn’t supported for offline. Please open it on your device.',
                style: Theme.of(context).textTheme.bodyLarge,
                textAlign: TextAlign.center,
              ),
              const SizedBox(
                height: Dimensions.paddingSizeDefault,
              ),
              ElevatedButton(
                onPressed: () {
                  ref
                      .read(classroomProvider.notifier)
                      .openMicrosoftFile(snapshot.data?.path ?? '');
                },
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeExtraMoreLarge,
                    vertical: Dimensions.paddingSizeSmall,
                  ),
                  backgroundColor: Theme.of(context).hoverColor,
                ),
                child: Text(
                  'Open on Device',
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeLarge,
                    color: Theme.of(context).colorScheme.onSurface,
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget h5pOnly(BuildContext context) {
    return WebViewWidget(
      url: lesson.urlVideoH5p,
    );
  }

  Widget scormOnly(BuildContext context) {
    if (_scorm == null) return const SizedBox.shrink();
    return SizedBox(
      height: (ref.watch(classroomProvider).valueOrNull?.isFullScreen ?? false)
          ? MediaQuery.of(context).size.height - kToolbarHeight
          : MediaQuery.of(context).size.height * 0.6,
      child: _scorm,
    );
  }

  Widget videoUpload() {
    return Container(
      alignment: Alignment.center,
      color: AppColors.warning.withValues(alpha: 0.2),
      child: CourseVideoPlayer(
        videoUrl: lesson.urlVideo,
        videoController: _videoController,
      ),
    );
  }

  Widget googleSlides(String url) {
    if (widget.isOnline) {
      return WebViewWidget(url: url);
    }
    return noAvailableWidget('Google Slides');
  }

  Widget embeddedVideo(BuildContext context, String videoType, Widget? player) {
    if (!widget.isOnline) {
      final capitalized = videoType[0].toUpperCase() + videoType.substring(1);
      return noAvailableWidget('$capitalized video');
    }
    return GestureDetector(
      onVerticalDragEnd: (drag) => context.pop(),
      child: player == null
          ? LayoutBuilder(
              builder: (context, constraint) => SizedBox(
                height: constraint.maxWidth * 0.6,
                child: VimeoVideoPlayer(
                  videoId: lesson.urlVideoEmbedded.split('/').last,
                  backgroundColor: Colors.transparent,
                  onInAppWebViewCreated: (controller) {
                    webView = controller;
                  },
                ),
              ),
            )
          : Builder(
              builder: (context) {
                if (_youtubePlayerController != null) {
                  return player;
                }
                return Container();
              },
            ),
    );
  }

  Future<void> openZoomMeeting(String zoomUrl) async {
    final uri = Uri.parse(zoomUrl);
    final success = await launchUrl(
      uri,
      mode: LaunchMode.externalApplication,
    );
    if (!success) {
      throw Exception('Could not open Zoom link');
    }
  }

  Widget zoomClass(Lesson lesson) {
    isLoading = false;
    final user = ref.read(userProfileProvider).valueOrNull?.userData;
    if (!widget.isOnline) {
      return noAvailableWidget('Zoom');
    }
    return LayoutBuilder(
      builder: (context, constraint) => Padding(
        padding:
            const EdgeInsets.only(top: Dimensions.paddingSizeExtraMoreLarge),
        child: InkWell(
          onTap: () {
            openZoomMeeting(
              lesson.urlZoomClass,
            );
          },
          child: Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            margin: EdgeInsets.symmetric(
              vertical: Dimensions.fontSizeDefault,
              horizontal: constraint.maxWidth * 0.2,
            ),
            decoration: BoxDecoration(
              color: AppColors.info,
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                SvgPicture.asset(
                  Images.meeting,
                  colorFilter: ColorFilter.mode(
                    Theme.of(context).colorScheme.surface,
                    BlendMode.srcIn,
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  user?.roles == AppConstants.student
                      ? 'Join meeting'
                      : 'Start meeting',
                  overflow: TextOverflow.ellipsis,
                  softWrap: false,
                  maxLines: 1,
                  style: robotoMedium.copyWith(
                    color: Theme.of(context).colorScheme.surface,
                    fontSize: Dimensions.fontSizeDefault,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
