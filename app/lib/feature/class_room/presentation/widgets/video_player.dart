import 'package:better_player_plus/better_player_plus.dart';
import 'package:everlms/feature/class_room/presentation/providers/better_video_player.dart';
import 'package:flutter/material.dart';

class CourseVideoPlayer extends StatelessWidget {
  const CourseVideoPlayer({
    required this.videoUrl,
    required this.videoController,
    super.key,
  });
  final String videoUrl;
  final MyBetterVideoPlayerController videoController;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: videoController,
      builder: (context, _) {
        return AspectRatio(
          aspectRatio: 16 / 9,
          child: videoController.betterPlayerController != null
              ? BetterPlayer(
                  controller: videoController.betterPlayerController!,
                )
              : const SizedBox(),
        );
      },
    );
  }
}
