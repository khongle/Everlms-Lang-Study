import 'package:audioplayers/audioplayers.dart';
import 'package:everlms/feature/class_room/presentation/providers/better_video_player.dart';
import 'package:flutter/foundation.dart';

typedef SaveProgressCallback = Future<void> Function({
  required int courseId,
  required String lessonID,
  required String sectionID,
  required String totalSpentTime,
  required String totalDuration,
});

/// Tracks audio playback progress state for [ProgressBar].
class ProgressBarState {
  ProgressBarState({
    required this.current,
    required this.buffered,
    required this.total,
  });
  final Duration? current;
  final Duration? buffered;
  final Duration? total;
}

/// A [ChangeNotifier] that manages [AudioPlayer] lifecycle for a single lesson.
///
/// Instantiated per-widget — create in [State.initState], dispose in
/// [State.dispose]. Not a GetX controller.
class MyAudioPlayerController extends ChangeNotifier {
  MyAudioPlayerController({
    this.videoPlayerController,
    this.onSaveProgress,
  }) {
    videoPlayerController?.saveProgressCalled = false;
    listenProgressState();
  }

  final MyBetterVideoPlayerController? videoPlayerController;
  final SaveProgressCallback? onSaveProgress;

  final player = AudioPlayer();
  UrlSource? _playerSource;
  Duration? _audioDuration;
  late Duration _oneThirdDuration;
  late int _courseId;
  String? _lessonId;
  String? _sectionId;

  final progressNotifier = ValueNotifier<ProgressBarState>(
    ProgressBarState(
      current: Duration.zero,
      buffered: Duration.zero,
      total: Duration.zero,
    ),
  );

  Future<void> initPlayer(
    String url,
    int courseId,
    String lessonId,
    String sectionId,
  ) async {
    _courseId = courseId;
    _lessonId = lessonId;
    _sectionId = sectionId;
    _playerSource = UrlSource(url);
    await player.setSourceUrl(url);
    await playAudio();
    notifyListeners();
  }

  Future<void> playOrPause() async {
    if (player.state == PlayerState.playing) {
      await pauseAudio();
    } else {
      await playAudio();
    }
    notifyListeners();
  }

  Future<void> listenProgressState() async {
    player.onDurationChanged.listen((duration) {
      final oldState = progressNotifier.value;
      _audioDuration = duration;
      _oneThirdDuration = duration * (1 / 3);
      progressNotifier.value = ProgressBarState(
        current: oldState.current,
        buffered: oldState.buffered,
        total: duration,
      );
    }).asFuture<void>();

    player.onPositionChanged.listen((position) {
      if (_audioDuration != null && position > _oneThirdDuration) {
        onSaveProgress?.call(
          courseId: _courseId,
          lessonID: _lessonId ?? '',
          sectionID: _sectionId ?? '',
          totalSpentTime: position.inSeconds.toString(),
          totalDuration: _audioDuration!.inSeconds.toString(),
        );
      }
      final oldState = progressNotifier.value;
      progressNotifier.value = ProgressBarState(
        current: position,
        buffered: oldState.buffered,
        total: oldState.total,
      );
    });
    notifyListeners();
  }

  Future<void> playAudio() async {
    if (_playerSource != null) {
      await player.play(_playerSource!);
      await listenProgressState();
      notifyListeners();
    }
  }

  Future<void> pauseAudio() async {
    await player.pause();
    notifyListeners();
  }

  Future<void> seekTo(Duration duration) async {
    await player.seek(duration);
    notifyListeners();
  }

  Future<void> stopPlayer() async {
    await player.stop();
    await player.release();
    videoPlayerController?.saveProgressCalled = false;
    notifyListeners();
  }

  @override
  void dispose() {
    stopPlayer();
    progressNotifier.dispose();
    super.dispose();
  }
}
