import 'package:everlms/core/utils/dimensions.dart';
import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:go_router/go_router.dart';

class ViewUserCertificate extends StatefulWidget {
  const ViewUserCertificate({this.filePath, super.key});
  final String? filePath;

  @override
  State<ViewUserCertificate> createState() => _ViewUserCertificateState();
}

class _ViewUserCertificateState extends State<ViewUserCertificate> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Hero(
        tag: 'pass_quiz_certification',
        child: Padding(
          padding: const EdgeInsets.only(top: Dimensions.paddingSizeLarge),
          child: PDFView(
            filePath: widget.filePath,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.pop(),
        child: const Icon(
          Icons.fullscreen_exit,
        ),
      ),
    );
  }
}
