import 'dart:async';

import 'package:better_player_plus/better_player_plus.dart';
import 'package:everlms/main.dart';
import 'package:flutter/material.dart';

/// A [ChangeNotifier] that manages a [BetterPlayerController] lifecycle.
///
/// Instantiated per-screen — create in [State.initState], dispose in
/// [State.dispose]. Not a GetX controller.
class MyBetterVideoPlayerController extends ChangeNotifier {
  BetterPlayerController? _betterPlayerController;
  List<BetterPlayerEvent> events = [];
  bool saveProgressCalled = false;
  final StreamController<DateTime> _eventStreamController =
      StreamController.broadcast();

  BetterPlayerController? get betterPlayerController => _betterPlayerController;

  Future<void> playVideo(String url, {bool isOnline = true}) async {
    const betterPlayerConfiguration = BetterPlayerConfiguration(
      aspectRatio: 16 / 9,
      fit: BoxFit.contain,
      controlsConfiguration: BetterPlayerControlsConfiguration(
        enableSkips: false,
        enableRetry: false,
        progressBarBufferedColor: Colors.white30,
        progressBarBackgroundColor: Colors.white12,
      ),
    );
    final dataSource = BetterPlayerDataSource(
      isOnline
          ? BetterPlayerDataSourceType.network
          : BetterPlayerDataSourceType.file,
      url,
      headers: {'Authorization': basicAuthWeb},
    );
    _betterPlayerController = BetterPlayerController(betterPlayerConfiguration);
    _betterPlayerController?.setupDataSource(dataSource);
    _betterPlayerController?.addEventsListener(_handleEvent);
    if (!saveProgressCalled) {
      _betterPlayerController?.addEventsListener(
        (event) {
          if (event.betterPlayerEventType == BetterPlayerEventType.finished) {
            // saveCourseProgress callback can be wired here if needed.
          }
        },
      );
    }
    notifyListeners();
  }

  void _handleEvent(BetterPlayerEvent event) {
    events.insert(0, event);
    _eventStreamController.add(DateTime.now());
  }

  Future<void> stopPlayer() async {
    saveProgressCalled = false;
    if (_betterPlayerController != null &&
        _betterPlayerController?.isPlaying() == true) {
      _betterPlayerController?.pause();
      await _eventStreamController.close();
      _betterPlayerController?.removeEventsListener(_handleEvent);
      _betterPlayerController?.dispose();
    }
  }

  @override
  void dispose() {
    stopPlayer();
    super.dispose();
  }
}
