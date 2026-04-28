import 'package:everlms/core/config/app_config.dart';
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';

class WebViewWidget extends StatefulWidget {
  const WebViewWidget({required this.url, super.key});
  final String? url;

  @override
  State<WebViewWidget> createState() => _WebViewWidgetState();
}

class _WebViewWidgetState extends State<WebViewWidget> {
  late InAppWebViewController webView;
  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: MediaQuery.of(context).size.width * 9 / 16 + 35,
      child: InAppWebView(
        initialUrlRequest: URLRequest(url: WebUri(widget.url ?? '')),
        initialSettings: InAppWebViewSettings(
          useOnDownloadStart: true,
          mediaPlaybackRequiresUserGesture: false,
          allowsInlineMediaPlayback: true,
          transparentBackground: true,
        ),
        onReceivedHttpAuthRequest: (controller, challenge) async {
          return HttpAuthResponse(
            action: HttpAuthResponseAction.PROCEED,
            username: Config.usernameLMS,
            password: Config.passwordLMS,
            permanentPersistence: true,
          );
        },
        onWebViewCreated: (controller) async {
          webView = controller;
        },
      ),
    );
  }
}
