import 'package:everlms/core/theme/app_colors.dart';
import 'package:everlms/core/widgets/expandable_html.dart';
import 'package:flutter/material.dart';

class TextLessonWidget extends StatelessWidget {
  const TextLessonWidget({required this.htmlContent, super.key});

  final String htmlContent;

  @override
  Widget build(BuildContext context) {
    if (htmlContent.isEmpty) {
      return const Padding(
        padding: EdgeInsets.all(24),
        child: Text(
          'No content available.',
          style: TextStyle(color: AppColors.textMuted),
        ),
      );
    }
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: ExpandableHtml(
        data: htmlContent,
        maxLines: 9999,
      ),
    );
  }
}
