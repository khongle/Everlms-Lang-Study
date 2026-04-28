import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/main.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';

class ScormViewer extends StatefulWidget {
  const ScormViewer({
    required this.scormContentPath,
    required this.lessonId,
    required this.title,
    required this.isOnline,
    super.key,
    this.uid,
  });
  final String? uid;
  final String title;
  final String lessonId;
  final String scormContentPath;
  final bool isOnline;

  @override
  State<ScormViewer> createState() => _ScormViewerState();
}

class _ScormViewerState extends State<ScormViewer> {
  late InAppWebViewController webView;
  String url = '';
  bool isLoading = true;
  bool isFullScreen = false;
  @override
  void initState() {
    if (widget.uid != null) {
      url = '${widget.scormContentPath}?userid=${widget.uid}';
    } else {
      url = widget.scormContentPath;
    }

    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: isFullScreen
          ? MediaQuery.of(context).size.height
          : MediaQuery.of(context).size.height * 0.6,
      child: Stack(
        children: [
          renderContentWeb(context),
          if (isLoading)
            Container(
              alignment: Alignment.center,
              color: Theme.of(context).scaffoldBackgroundColor,
              height: MediaQuery.of(context).size.height,
              child: const LoadingIndicator(),
            )
          else
            const SizedBox.shrink(),
        ],
      ),
    );
  }

  Widget renderContentWeb(BuildContext context) {
    final header = {
      'authorization': basicAuthWeb,
    };

    return InAppWebView(
      // Loads your SCORM launch HTML from local server
      gestureRecognizers: {
        const Factory<OneSequenceGestureRecognizer>(
          EagerGestureRecognizer.new, // Claims all gestures immediately
        ),
        const Factory<VerticalDragGestureRecognizer>(
          VerticalDragGestureRecognizer.new,
        ),
      }.toSet(),
      initialSettings: InAppWebViewSettings(
        useOnDownloadStart: true,
        mediaPlaybackRequiresUserGesture: false,
        disableHorizontalScroll: true,
        allowsInlineMediaPlayback: true,
        transparentBackground: true,
      ),
      onWebViewCreated: (controller) async {
        webView = controller;
        await InAppWebViewController.clearAllCache();
        webView.loadUrl(
          urlRequest: URLRequest(url: WebUri(url), headers: header),
        );
      },
      onReceivedHttpAuthRequest: (controller, challenge) async {
        return HttpAuthResponse(
          username: Config.usernameLMS,
          password: Config.passwordLMS,
          action: HttpAuthResponseAction.PROCEED,
        );
      },
      onLoadStop: (controller, url) async {
        if (widget.isOnline) {
          await controller.evaluateJavascript(
            source: """
          (function() {
            var iframe = document.querySelector('iframe');
            if (iframe) {
              // Clear body
              document.body.innerHTML = "";
              document.body.style.margin = "0";
              document.body.style.padding = "0";
              document.body.style.overflow = "hidden";
            
              // Force iframe full width
              iframe.removeAttribute("width");
              iframe.removeAttribute("height");
              iframe.style.width = "100%";
              iframe.style.height = "100vh"; // fill viewport height
              iframe.style.border = "none";
              iframe.style.display = "block";
            
              document.body.appendChild(iframe);
            } else {
              document.body.innerHTML = "<p>No iframe found</p>";
            }
          })();
        """,
          );
        }
        await Future.delayed(const Duration(seconds: 2), () {
          if (mounted) {
            setState(() {
              isLoading = false;
            });
          }
        });
      },
      onReceivedError: (controller, request, error) {
        // Check error contain ERR_INTERNET_DISCONNECTED
        if (error.description.contains('ERR_INTERNET_DISCONNECTED')) {
          // Show error message
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(tl(context).no_internet_connection),
            ),
          );
        }
      },
    );
  }
}
