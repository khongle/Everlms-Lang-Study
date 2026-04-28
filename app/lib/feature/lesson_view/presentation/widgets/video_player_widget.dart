import 'package:better_player_plus/better_player_plus.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';

/// Renders a video lesson — YouTube, Vimeo, or generic file URL.
class VideoPlayerWidget extends StatefulWidget {
  const VideoPlayerWidget({required this.videoUrl, super.key});

  final String videoUrl;

  @override
  State<VideoPlayerWidget> createState() => _VideoPlayerWidgetState();
}

class _VideoPlayerWidgetState extends State<VideoPlayerWidget> {
  YoutubePlayerController? _ytController;
  BetterPlayerController? _betterController;
  bool _isYoutube = false;
  bool _isVimeo = false;

  @override
  void initState() {
    super.initState();
    _initPlayer();
  }

  void _initPlayer() {
    final url = widget.videoUrl;
    final ytId = YoutubePlayer.convertUrlToId(url);
    if (ytId != null) {
      _isYoutube = true;
      _ytController = YoutubePlayerController(
        initialVideoId: ytId,
        flags: const YoutubePlayerFlags(autoPlay: false),
      );
      return;
    }

    if (url.contains('vimeo.com')) {
      _isVimeo = true;
      return;
    }

    // Generic video — use better_player_plus
    final dataSource = BetterPlayerDataSource(
      BetterPlayerDataSourceType.network,
      url,
    );
    _betterController = BetterPlayerController(
      const BetterPlayerConfiguration(aspectRatio: 16 / 9),
      betterPlayerDataSource: dataSource,
    );
  }

  @override
  void dispose() {
    _ytController?.dispose();
    _betterController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.videoUrl.isEmpty) {
      return _placeholder('No video URL');
    }

    if (_isYoutube && _ytController != null) {
      return YoutubePlayer(
        controller: _ytController!,
        showVideoProgressIndicator: true,
        progressIndicatorColor: AppColors.accentPrimary,
      );
    }

    if (_isVimeo) {
      // Vimeo embed via WebView fallback
      return _placeholder('Vimeo player coming soon');
    }

    if (_betterController != null) {
      return AspectRatio(
        aspectRatio: 16 / 9,
        child: BetterPlayer(controller: _betterController!),
      );
    }

    return _placeholder('Unsupported video format');
  }

  Widget _placeholder(String message) {
    return AspectRatio(
      aspectRatio: 16 / 9,
      child: ColoredBox(
        color: Colors.black,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.play_circle_outline,
                  size: 48, color: AppColors.textMuted,),
              const SizedBox(height: 8),
              Text(message,
                  style: const TextStyle(
                      color: AppColors.textMuted, fontSize: 13,),),
            ],
          ),
        ),
      ),
    );
  }
}
