import 'dart:io';

import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:go_router/go_router.dart';

class QuizCertificateSection extends StatelessWidget {
  const QuizCertificateSection({
    required this.isLoadingFile,
    required this.urlDownload,
    required this.fileCertificate,
    super.key,
  });

  final bool isLoadingFile;
  final String? urlDownload;
  final File? fileCertificate;

  @override
  Widget build(BuildContext context) {
    if (isLoadingFile) {
      return const LoadingIndicator();
    }
    if (urlDownload == null) {
      return Center(
        child: Text(
          textAlign: TextAlign.center,
          'Pass this quiz to earn your certificate.',
          style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeExtraLarge),
        ),
      );
    }
    if (fileCertificate == null || !fileCertificate!.existsSync()) {
      return Center(
        child: Text(
          textAlign: TextAlign.center,
          'Oops! Something went wrong while loading your certificate.',
          style: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeExtraLarge,
            color: Theme.of(context).colorScheme.error,
          ),
        ),
      );
    }
    return InkWell(
      onTap: () async {
        await context.push(
          AppRoutes.viewUserCertificateScreen,
          extra: fileCertificate?.path,
        );
      },
      child: Hero(
        tag: 'pass_quiz_certification',
        child: IgnorePointer(
          child: PDFView(
            filePath: fileCertificate!.path,
            enableSwipe: false,
          ),
        ),
      ),
    );
  }
}
