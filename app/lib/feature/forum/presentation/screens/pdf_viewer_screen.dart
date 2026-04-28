import 'dart:io';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:permission_handler/permission_handler.dart';

class PDFViewerScreen extends StatefulWidget {
  const PDFViewerScreen({
    required this.pdfUrl,
    required this.fileName,
    super.key,
  });
  final String pdfUrl;
  final String fileName;

  @override
  State<PDFViewerScreen> createState() => _PDFViewerScreenState();
}

class _PDFViewerScreenState extends State<PDFViewerScreen> {
  @override
  void initState() {
    super.initState();
    requestStoragePermission();
  }

  Future<void> requestStoragePermission() async {
    await Permission.photos.request();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(),
      body: FutureBuilder<File>(
        future: downloadPDF(widget.pdfUrl, widget.fileName),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const LoadingIndicator();
          }
          if (snapshot.connectionState == ConnectionState.done) {
            if (snapshot.hasError) {
              return Center(
                child: Text(tl(context).something_wrong),
              );
            }
            return Column(
              children: [
                Expanded(
                  child: Padding(
                    padding:
                        const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    child: PDFView(
                      filePath: snapshot.data!.path,
                    ),
                  ),
                ),
                CustomButton(
                  buttonText: tl(context).download,
                  onPressed: () async {
                    LoadingDialog.show(context);
                    await downloadAndSaveFile(widget.pdfUrl, widget.fileName);
                    if (context.mounted) {
                      LoadingDialog.hide(context);
                    }
                  },
                ),
              ],
            );
          }
          return Container();
        },
      ),
    );
  }

  // Load the PDF file from the download external storage
  Future<File> getPDF() async {
    final dir = Directory('/storage/emulated/0/Download');
    final file = File('${dir.path}/certi.pdf');

    return file;
  }
}
