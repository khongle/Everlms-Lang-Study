import 'dart:io';

import 'package:dio/dio.dart';
import 'package:everlms/core/theme/app_colors.dart';
import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:path_provider/path_provider.dart';

class PdfLessonWidget extends StatefulWidget {
  const PdfLessonWidget({required this.pdfUrl, super.key});

  final String pdfUrl;

  @override
  State<PdfLessonWidget> createState() => _PdfLessonWidgetState();
}

class _PdfLessonWidgetState extends State<PdfLessonWidget> {
  String? _localPath;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _downloadPdf();
  }

  Future<void> _downloadPdf() async {
    try {
      final dir = await getTemporaryDirectory();
      final filename = 'lesson_${widget.pdfUrl.hashCode}.pdf';
      final file = File('${dir.path}/$filename');
      if (!file.existsSync()) {
        await Dio().download(widget.pdfUrl, file.path);
      }
      if (mounted) setState(() => _localPath = file.path);
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.accentPrimary),
      );
    }
    if (_error != null || _localPath == null) {
      return const Center(
        child: Text(
          'Failed to load PDF.',
          style: TextStyle(color: AppColors.textMuted),
        ),
      );
    }
    return PDFView(
      filePath: _localPath,
    );
  }
}
