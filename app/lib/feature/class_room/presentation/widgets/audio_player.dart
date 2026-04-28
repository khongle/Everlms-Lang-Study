import 'package:audio_video_progress_bar/audio_video_progress_bar.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/class_room/presentation/providers/audio_player.dart';
import 'package:everlms/feature/class_room/presentation/providers/better_video_player.dart';
import 'package:everlms/feature/class_room/presentation/providers/classroom_provider.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MyAudioPlayer extends ConsumerStatefulWidget {
  const MyAudioPlayer({
    required this.lesson,
    required this.courseID,
    required this.sectionID,
    this.videoController,
    super.key,
  });
  final Lesson lesson;
  final int courseID;
  final String sectionID;
  final MyBetterVideoPlayerController? videoController;

  @override
  ConsumerState<MyAudioPlayer> createState() => _MyAudioPlayerState();
}

class _MyAudioPlayerState extends ConsumerState<MyAudioPlayer> {
  late final MyAudioPlayerController _controller;

  @override
  void initState() {
    super.initState();
    _controller = MyAudioPlayerController(
      videoPlayerController: widget.videoController,
      onSaveProgress: ({
        required courseId,
        required lessonID,
        required sectionID,
        required totalSpentTime,
        required totalDuration,
      }) =>
          ref.read(classroomProvider.notifier).saveProgress(
                courseId: courseId,
                lessonID: lessonID,
                sectionID: sectionID,
                totalSpentTime: totalSpentTime,
                totalDuration: totalDuration,
              ),
    );
    _controller.initPlayer(
      widget.lesson.link,
      widget.courseID,
      widget.lesson.id.toString(),
      widget.sectionID,
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: ListenableBuilder(
        listenable: _controller,
        builder: (context, _) {
          return Container(
            padding: const EdgeInsets.all(10),
            height: MediaQuery.of(context).size.height / 4 - 50,
            decoration: const BoxDecoration(
              borderRadius: BorderRadius.all(Radius.circular(5)),
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeDefault,
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(
                          widget.lesson.thumbnail,
                          height: 44,
                          width: 44,
                          errorBuilder: (context, error, stackTrace) =>
                              Image.asset(
                            Images.placeholder,
                            height: 44,
                            width: 44,
                          ),
                        ),
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              widget.lesson.title,
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                              style: robotoRegular.copyWith(
                                color: Theme.of(context)
                                    .textTheme
                                    .bodyLarge!
                                    .color,
                                fontSize: Dimensions.fontSizeSmall,
                              ),
                            ),
                            Text(
                              widget.lesson.description,
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                              style: robotoRegular.copyWith(
                                color: Theme.of(context)
                                    .textTheme
                                    .bodyLarge!
                                    .color!
                                    .withValues(alpha: 0.6),
                                fontSize: Dimensions.fontSizeExtraSmall,
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () {
                          _controller.stopPlayer();
                          context.pop();
                        },
                        icon: const Icon(Icons.close),
                      ),
                    ],
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      StreamBuilder<PlayerState>(
                        stream: _controller.player.onPlayerStateChanged,
                        builder: (context, snapshot) {
                          final playerState = snapshot.data;
                          return _playerButton(playerState);
                        },
                      ),
                    ],
                  ),
                  ValueListenableBuilder<ProgressBarState>(
                    valueListenable: _controller.progressNotifier,
                    builder: (_, value, __) {
                      return ProgressBar(
                        progress: value.current!,
                        buffered: value.buffered,
                        barHeight: 2,
                        thumbRadius: 8,
                        timeLabelTextStyle: robotoRegular.copyWith(
                          color: Colors.black,
                          fontSize: 12,
                        ),
                        total: value.total!,
                        onSeek: (value) => _controller.seekTo(value),
                      );
                    },
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _playerButton(PlayerState? state) {
    if (state != PlayerState.completed &&
        state != PlayerState.paused &&
        state != PlayerState.playing &&
        state != PlayerState.stopped) {
      return Container(
        margin: const EdgeInsets.all(8),
        width: 25,
        height: 25,
        child: const LoadingIndicator(height: 25),
      );
    } else if (state == PlayerState.playing) {
      return IconButton(
        onPressed: () => _controller.playOrPause(),
        icon: const Icon(Icons.pause),
      );
    } else {
      return IconButton(
        onPressed: () => _controller.playOrPause(),
        icon: const Icon(Icons.play_arrow),
      );
    }
  }
}
