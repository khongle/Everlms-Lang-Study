import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/feature/lesson_view/data/models/lesson_detail_dto.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class ZoomLessonWidget extends StatelessWidget {
  const ZoomLessonWidget({required this.lesson, super.key});

  final LessonDetailDto lesson;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: AppColors.bgCard,
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: const Color(0xFF2D8CFF).withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.video_camera_front_rounded,
                      color: AppColors.accentZoom,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Zoom Meeting',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              if (lesson.zoomMeetingId != null) ...[
                _InfoRow(label: 'Meeting ID', value: lesson.zoomMeetingId!),
                const SizedBox(height: 8),
              ],
              if (lesson.zoomPassword != null) ...[
                _InfoRow(label: 'Password', value: lesson.zoomPassword!),
                const SizedBox(height: 8),
              ],
              if (lesson.zoomStartTime != null) ...[
                _InfoRow(
                  label: 'Start time',
                  value: _formatTime(lesson.zoomStartTime!),
                ),
                const SizedBox(height: 8),
              ],
              if (lesson.zoomJoinUrl != null) ...[
                const SizedBox(height: 16),
                GestureDetector(
                  onTap: () async {
                    final uri = Uri.parse(lesson.zoomJoinUrl!);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(uri,
                          mode: LaunchMode.externalApplication,);
                    }
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    decoration: BoxDecoration(
                      color: AppColors.accentZoom,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.open_in_new, size: 18, color: Colors.white),
                        SizedBox(width: 8),
                        Text(
                          'Join Meeting',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  String _formatTime(DateTime dt) {
    return '${dt.day}/${dt.month}/${dt.year}  ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 100,
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textMuted,
            ),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: AppColors.textPrimary,
            ),
          ),
        ),
      ],
    );
  }
}
