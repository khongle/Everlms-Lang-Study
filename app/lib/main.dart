import 'dart:convert';
import 'dart:io';
import 'dart:ui';

import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/services/network_service.dart';
import 'package:everlms/core/theme/app_theme.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/feature/settings/presentation/providers/locale_provider.dart';
import 'package:everlms/feature/settings/presentation/providers/theme_provider.dart';
import 'package:everlms/l10n/app_localizations.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_downloader/flutter_downloader.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:shared_preferences/shared_preferences.dart';

HttpServer? server;
final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
    FlutterLocalNotificationsPlugin();
final navigatorKey = GlobalKey<NavigatorState>();
final appKey = GlobalKey<_MyEverLMSAppState>();
final RouteObserver<PageRoute> routeObserver = RouteObserver<PageRoute>();
String basicAuthWeb =
    'Basic ${base64.encode(utf8.encode('${Config.usernameLMS}:${Config.passwordLMS}'))}';

Future<void> main() async {
  await dotenv.load();
  HttpOverrides.global = MyHttpOverrides();
  await FlutterDownloader.initialize(debug: true, ignoreSsl: true);
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  String? bookingID;
  final sharedPreferences = await SharedPreferences.getInstance();
  initRouter(sharedPreferences);
  final url = sharedPreferences.getString(AppConstants.baseUrl);
  if (url != null && url.isNotEmpty) {
    Config.baseUrl = url;
  }
  NetworkService().initialize();

  Stripe.publishableKey = '${dotenv.env['STRIPE_PUBLIC_KEY']}';
  Stripe.merchantIdentifier = 'any string works';
  await Stripe.instance.applySettings();
  runApp(
    ProviderScope(
      overrides: [
        sharedPreferencesProvider.overrideWithValue(sharedPreferences),
      ],
      child: MyEverLMSApp(
        bookingID: bookingID,
        key: appKey,
      ),
    ),
  );
}

class MyEverLMSApp extends ConsumerStatefulWidget {
  const MyEverLMSApp({
    required this.bookingID,
    super.key,
  });
  final String? bookingID;

  static void restartApp(BuildContext context, String newUrl) {
    context.findAncestorStateOfType<_MyEverLMSAppState>()?.restartApp(newUrl);
  }

  @override
  ConsumerState<MyEverLMSApp> createState() => _MyEverLMSAppState();
}

class _MyEverLMSAppState extends ConsumerState<MyEverLMSApp> {
  Key key = UniqueKey();
  void restartApp(String newUrl) {
    setState(() {
      key = UniqueKey();
      Config.baseUrl = newUrl;
    });
  }

  @override
  Widget build(BuildContext context) {
    final themeMode = ref.watch(themeProvider);
    final locale = ref.watch(localeProvider);

    return KeyedSubtree(
      key: key,
      child: MaterialApp.router(
        routerConfig: appRouter,
        title: AppConstants.appName,
        debugShowCheckedModeBanner: false,
        scrollBehavior: const MaterialScrollBehavior().copyWith(
          dragDevices: {
            PointerDeviceKind.mouse,
            PointerDeviceKind.touch,
          },
        ),
        theme: light,
        darkTheme: dark,
        themeMode: themeMode,
        locale: locale,
        localizationsDelegates: const [
          AppLocalizations.delegate,
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
        ],
        supportedLocales: AppLocalizations.supportedLocales,
      ),
    );
  }
}

class MyHttpOverrides extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (cert, host, port) => true;
  }
}
