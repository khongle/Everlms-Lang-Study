import 'dart:async';
import 'dart:core';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/auth/presentation/providers/auth_provider.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';
import 'package:go_router/go_router.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => SplashScreenState();
}

class SplashScreenState extends ConsumerState<SplashScreen> {
  final GlobalKey<ScaffoldState> _globalKey = GlobalKey();
  bool isNotConnected = false;
  Future<void> _initializeConnectivity() async {
    final result = await Connectivity().checkConnectivity();
    setState(() {
      isNotConnected = !result.any((r) => r != ConnectivityResult.none);
    });

    //if (isNotConnected) {
    _route(isNotConnected);
    //}}
  }

  @override
  void initState() {
    super.initState();
    _initializeConnectivity();
    // initSharedData is handled by splashProvider build()

    // bool firstTime = true;
    // _onConnectivityChanged = Connectivity()
    //     .onConnectivityChanged
    //     .listen((ConnectivityResult result) {
    //   if (!firstTime) {
    //     setState(() {
    //       isNotConnected = result != ConnectivityResult.wifi &&
    //           result != ConnectivityResult.mobile;
    //     });
    //     isNotConnected
    //         ? const SizedBox()
    //         : ScaffoldMessenger.of(context).hideCurrentSnackBar();
    //     ScaffoldMessenger.of(context).showSnackBar(SnackBar(
    //       backgroundColor: isNotConnected ? Colors.red : Colors.green,
    //       duration: Duration(seconds: isNotConnected ? 6000 : 3),
    //       content: Text(
    //         isNotConnected ? 'no_connection' : 'connected',
    //         textAlign: TextAlign.center,
    //       ),
    //     ));
    //     if (!isNotConnected) {
    //       _route(isNotConnected);
    //     }
    //   }
    //   firstTime = false;
    // });

    // _route(isNotConnected);
  }

  @override
  void dispose() {
    super.dispose();
  }

  Future<void> _route(bool isNotConnected) async {
    if (kDebugMode) {
      debugPrint('>>>:$isNotConnected');
      debugPrint('Domain ${Config.baseUrl}');
    }
    if (!isNotConnected) {
      final authNotifier = ref.read(authProvider.notifier);
      if (authNotifier.isLoggedIn()) {
        authNotifier.loadAppData();
        Timer(const Duration(seconds: 4), () async {
          context.go(AppRoutes.getMainRoute(AppRoutes.splash));
        });
      } else {
        if (Config.baseUrl == Config.defaultDomain) {
          await authNotifier.autoLoginDefault();
        } else {
          await authNotifier.allowSocialAuth();
          if (!mounted) return;
          context.go(AppRoutes.getSignInRoute('fromSplash'));
        }
      }
    } else {
      context.push(AppRoutes.mainOfflineScreen);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _globalKey,
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Column(
        children: [
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.3,
          ),
          Center(
            child: SvgPicture.asset(
              Images.splash,
              width: 250,
              height: 250,
            ),
          ),
        ],
      ),
    );
  }
}

class SplashCustomPainter extends CustomPainter {
  SplashCustomPainter(this.context);
  final BuildContext? context;

  @override
  void paint(Canvas canvas, Size size) {
    final leftCorner = Paint();
    leftCorner.color = Theme.of(context!).primaryColor.withValues(alpha: .3);
    var path = Path();
    path.lineTo(0, 170);
    //Added this line
    path.relativeQuadraticBezierTo(100, -20, 110, -170);
    canvas.drawPath(path, leftCorner);
    final paint = Paint();

    // Path number 3
    paint.color = Theme.of(context!).primaryColor.withValues(alpha: .3);
    path = Path();
    path.lineTo(size.width, size.height / 3);
    path.cubicTo(
      size.width * 1.8,
      size.height * 0.5,
      size.width / 2,
      size.height,
      size.width / .99,
      size.height,
    );
    path.cubicTo(
      size.width,
      10,
      size.width,
      size.height / 10,
      size.width,
      size.height / 3,
    );

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(SplashCustomPainter oldDelegate) => false;

  @override
  bool shouldRebuildSemantics(SplashCustomPainter oldDelegate) => false;
}
