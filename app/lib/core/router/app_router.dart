import 'package:everlms/core/router/route_args.dart';
import 'package:everlms/core/utils/app_constants.dart';
import 'package:everlms/core/utils/enum.dart';
import 'package:everlms/core/widgets/view_all_courses.dart';
import 'package:everlms/feature/auth/presentation/screens/change_password/change_password_screen.dart';
import 'package:everlms/feature/auth/presentation/screens/email_verification_screen.dart';
import 'package:everlms/feature/auth/presentation/screens/forgot_password/forgot_password_screen.dart';
import 'package:everlms/feature/auth/presentation/screens/forgot_password/new_pass_screen.dart';
import 'package:everlms/feature/auth/presentation/screens/sign_in_screen.dart';
import 'package:everlms/feature/auth/presentation/screens/sign_up_screen.dart';
import 'package:everlms/feature/cart/presentation/screens/cart_screen.dart';
import 'package:everlms/feature/class_room/presentation/screens/landing_learning_screen.dart';
import 'package:everlms/feature/class_room/presentation/screens/learning_screen.dart';
import 'package:everlms/feature/coupon/presentation/screens/coupon_screen.dart';
import 'package:everlms/feature/course_catalog/presentation/screens/course_catalog_screen.dart';
import 'package:everlms/feature/course_details/presentation/screens/course_details_offline_screen.dart';
import 'package:everlms/feature/course_details/presentation/screens/course_details_screen.dart';
import 'package:everlms/feature/course_details/presentation/screens/course_details_screen_not_purchase_screen.dart';
import 'package:everlms/feature/dashboard/presentation/screens/home_dashboard_screen.dart';
import 'package:everlms/feature/forum/presentation/screens/forum_detail_screen.dart';
import 'package:everlms/feature/forum/presentation/screens/forum_screen.dart';
import 'package:everlms/feature/forum/presentation/screens/pdf_viewer_screen.dart';
import 'package:everlms/feature/forum/presentation/screens/topic_screen.dart';
import 'package:everlms/feature/html/presentation/screens/html_viewer_screen.dart';
import 'package:everlms/feature/instructor_details/presentation/screens/instructor_detail_screen.dart';
import 'package:everlms/feature/landing/presentation/screens/landing_screen.dart';
import 'package:everlms/feature/landing/presentation/screens/offline_landing_screen.dart';
import 'package:everlms/feature/lesson_view/presentation/screens/lesson_view_screen.dart';
import 'package:everlms/feature/lms_course_detail/presentation/screens/course_detail_screen.dart';
import 'package:everlms/feature/meeting/presentation/screens/meeting_screen.dart';
import 'package:everlms/feature/membership/presentation/screens/membership_screen.dart';
import 'package:everlms/feature/my_courses/presentation/screens/my_courses_screen.dart';
import 'package:everlms/feature/news/presentation/screens/news_screen.dart';
import 'package:everlms/feature/news/presentation/widgets/news_by_tags.dart';
import 'package:everlms/feature/news/presentation/widgets/news_detail.dart';
import 'package:everlms/feature/notification/presentation/screens/notification_screen.dart';
import 'package:everlms/feature/order/presentation/screens/order_history_screen.dart';
import 'package:everlms/feature/organization/presentation/screens/organization_screen.dart';
import 'package:everlms/feature/profile/presentation/screens/edit_profile_screen.dart';
import 'package:everlms/feature/profile/presentation/screens/profile_screen.dart';
import 'package:everlms/feature/profile/presentation/widgets/user_certificates_screen.dart';
import 'package:everlms/feature/quiz/data/models/data.dart';
import 'package:everlms/feature/quiz/data/models/given_answer.dart';
import 'package:everlms/feature/quiz/presentation/screens/quiz_detail_page.dart';
import 'package:everlms/feature/quiz/presentation/screens/quiz_result_screen.dart';
import 'package:everlms/feature/quiz/presentation/screens/quiz_start_page.dart';
import 'package:everlms/feature/quiz/presentation/widgets/view_user_certificate.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/presentation/screens/not_found_screen.dart';
import 'package:everlms/feature/settings/presentation/screens/settings_screen.dart';
import 'package:everlms/feature/settings/presentation/widgets/switch_domain_screen.dart';
import 'package:everlms/feature/splash/presentation/screens/splash_screen.dart';
import 'package:everlms/main.dart' show navigatorKey;
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// All route name constants – kept identical to [RouteHelper] for
/// backward-compatibility with existing `Get.toNamed(AppRoutes.xxx)` calls
/// while we migrate navigation incrementally.
class AppRoutes {
  AppRoutes._();

  static const String splash = '/splash';
  static const String switchDomainScreen = '/switch-domain-screen';
  static const String news = '/news';
  static const String newsDetail = '/news-detail';
  static const String forum = '/forum';
  static const String forumDetail = '/forum-detail';
  static const String topic = '/topic';
  static const String signIn = '/sign-in';
  static const String signUp = '/sign-up';
  static const String main = '/main';
  static const String profile = '/profile';
  static const String html = '/html';
  static const String cart = '/cart';
  static const String settingScreen = '/settingScreen';
  static const String courseDetailsScreen = '/courseDetailsScreen';
  static const String courseDetailsScreenNotPurchase =
      '/courseDetailsScreenNotPurchase';
  static const String courseDetailsOfflineScreen =
      '/courseDetailsOfflineScreen';
  static const String instructorDetails = '/instructorDetails';
  static const String couponScreen = '/couponScreen';
  static const String organizationScreen = '/organizationScreen';
  static const String orderHistoryScreen = '/orderHistoryScreen';
  static const String emailVerificationScreen = '/emailVerificationScreen';
  static const String forgotPasswordScreen = '/forgot_password_screen';
  static const String newPasswordScreen = '/new_password_screen';
  static const String meetingScreen = '/meetingScreen';
  static const String userCertificateScreen = '/userCertificateScreen';
  static const String editProfileScreen = '/editProfileScreen';
  static const String quizStartPage = '/quizStartPage';
  static const String learningScreen = '/learningScreen';
  static const String landingLearningScreen = '/landingLearningScreen';
  static const String quizDetailsScreen = '/QuizDetailScreen';
  static const String quizResultScreen = '/QuizResultScreen';
  static const String notificationScreen = '/notificationScreen';
  static const String changePassword = '/changePasswordScreen';
  static const String myCourseScreen = '/myCourseScreen';
  static const String membershipScreen = '/membershipScreen';
  static const String pdfViewerScreen = '/pdfViewerScreen';
  static const String viewAllCoursesScreen = '/viewAllCoursesScreen';
  static const String mainOfflineScreen = '/mainOfflineScreen';
  static const String newsTagScreen = '/newsTagScreen';
  static const String viewUserCertificateScreen = '/viewUserCertificateScreen';
  static const String homeDashboard = '/home';
  static const String courseCatalog = '/courses';

  // ---------------------------------------------------------------------------
  // Helper builders (mirrors RouteHelper static methods)
  // ---------------------------------------------------------------------------
  static String getSignInRoute(String page) => '$signIn?page=$page';
  static String getMainRoute(String page) => '$main?page=$page';
  static String getHtmlRoute(String page) => '$html?page=$page';
  static String getLearningScreen(String fromPage, String isOnline) =>
      '$learningScreen?isOnline=$isOnline&fromPage=$fromPage';
  static String getEmailVerificationScreen(String page, String email) =>
      '$emailVerificationScreen?page=$page&email=$email';

  // Simple route getters (no parameters)
  static String getProfileRoute() => profile;
  static String getCourseDetailsScreenRoute() => courseDetailsScreen;
  static String getCourseDetailsNotPurchaseScreenRoute() =>
      courseDetailsScreenNotPurchase;
  static String getCourseDetailsOfflineRoute() => courseDetailsOfflineScreen;
  static String getInstructorDetails() => instructorDetails;
  static String getCartScreen() => cart;
  static String getOrganizationScreen() => organizationScreen;
  static String getLandingLearning(
    String fromPage,
    String isOnline, {
    List? listChapter,
    int? indexLesson,
    int? indexChapter,
  }) =>
      '$landingLearningScreen?isOnline=$isOnline&indexLesson=$indexLesson&indexChapter=$indexChapter&listChapter=$listChapter&fromPage=$fromPage';
}

// ---------------------------------------------------------------------------
// Auth redirect guard (Plan Item 11)
// ---------------------------------------------------------------------------

// Public-only routes (auth required for everything else except splash/offline).
const Set<String> _publicRoutes = {
  AppRoutes.splash,
  AppRoutes.signIn,
  AppRoutes.signUp,
  AppRoutes.emailVerificationScreen,
  AppRoutes.forgotPasswordScreen,
  AppRoutes.newPasswordScreen,
  AppRoutes.switchDomainScreen,
  AppRoutes.mainOfflineScreen,
};

/// Initialised from main() once SharedPreferences is ready, so the redirect
/// guard can perform a synchronous token check without async I/O.
SharedPreferences? _routerPrefs;

/// Call this from main() after [SharedPreferences.getInstance()] resolves.
void initRouter(SharedPreferences prefs) {
  _routerPrefs = prefs;
}

String? _authRedirect(BuildContext context, GoRouterState state) {
  final prefs = _routerPrefs;
  if (prefs == null) return null; // Prefs not ready yet — let splash decide.

  final token = prefs.getString(AppConstants.token) ?? '';
  final isLoggedIn = token.isNotEmpty;
  final location = state.matchedLocation;

  // Unauthenticated user trying to access a protected route → login.
  if (!isLoggedIn && !_publicRoutes.contains(location)) {
    return AppRoutes.getSignInRoute('fromSplash');
  }

  return null; // No redirect.
}

// ---------------------------------------------------------------------------
// Router instance
// ---------------------------------------------------------------------------
final GoRouter appRouter = GoRouter(
  navigatorKey: navigatorKey,
  initialLocation: AppRoutes.splash,
  redirect: _authRedirect,
  errorBuilder: (context, state) => const NotFoundScreen(),
  routes: [
    GoRoute(path: AppRoutes.splash, builder: (_, __) => const SplashScreen()),
    GoRoute(
      path: AppRoutes.switchDomainScreen,
      builder: (_, state) => SwitchDomainScreen(urlArg: state.extra as String?),
    ),
    GoRoute(
      path: AppRoutes.signIn,
      builder: (_, state) =>
          SignInScreen(fromPage: state.uri.queryParameters['page'] ?? ''),
    ),
    GoRoute(path: AppRoutes.signUp, builder: (_, __) => const SignUpScreen()),
    GoRoute(
      path: AppRoutes.main,
      builder: (_, state) => const MainScreen(pageIndex: 0),
    ),
    GoRoute(
      path: AppRoutes.emailVerificationScreen,
      builder: (_, state) => EmailVerificationScreen(
        fromPage: state.uri.queryParameters['page'] ?? '',
        email: state.uri.queryParameters['email'] ?? '',
      ),
    ),
    GoRoute(
      path: AppRoutes.forgotPasswordScreen,
      builder: (_, __) => const ForgotPasswordScreen(),
    ),
    GoRoute(
      path: AppRoutes.newPasswordScreen,
      builder: (_, __) => const NewPassScreen(),
    ),
    GoRoute(
      path: AppRoutes.changePassword,
      builder: (_, __) => const ChangePasswordScreen(),
    ),
    GoRoute(
      path: AppRoutes.html,
      builder: (_, state) {
        final page = state.uri.queryParameters['page'];
        final htmlType = page == 'terms_of_use'
            ? HtmlType.termsOfUse
            : page == 'privacy_policy'
                ? HtmlType.privacyPolicy
                : page == 'help_and_support'
                    ? HtmlType.helpAndSupport
                    : HtmlType.aboutUs;
        return HtmlViewerScreen(htmlType: htmlType);
      },
    ),
    GoRoute(
      path: AppRoutes.cart,
      builder: (_, state) => CartScreen(
        courseArg: state.extra as Course?,
      ),
    ),
    GoRoute(
      path: AppRoutes.settingScreen,
      builder: (_, __) => const SettingScreen(),
    ),
    GoRoute(path: AppRoutes.news, builder: (_, __) => const NewsScreen()),
    GoRoute(
      path: AppRoutes.newsDetail,
      builder: (_, state) => NewsDetail(newsArg: state.extra),
    ),
    GoRoute(path: AppRoutes.forum, builder: (_, __) => const ForumScreen()),
    GoRoute(
      path: AppRoutes.forumDetail,
      builder: (_, state) {
        final args = state.extra as Map<String, dynamic>?;
        return ForumDetailScreen(
          forumIdArg: args?['forum_id']?.toString(),
          forumNameArg: args?['forum_name']?.toString(),
        );
      },
    ),
    GoRoute(
      path: AppRoutes.topic,
      builder: (_, state) {
        final args = state.extra as Map<String, dynamic>?;
        return TopicScreen(
          topicIdArg: args?['topic_id']?.toString(),
          topicNameArg: args?['topic_name']?.toString(),
          forumIdArg: args?['forum_id']?.toString(),
          forumNameArg: args?['forum_name']?.toString(),
        );
      },
    ),
    GoRoute(
      path: AppRoutes.courseDetailsScreen,
      builder: (_, state) {
        final extra = state.extra;
        if (extra is Course) {
          return CourseDetailsScreen(courseArg: extra);
        } else if (extra is Map) {
          return CourseDetailsScreen(courseArg: extra['course'] as Course?);
        } else if (extra is int) {
          return CourseDetailsScreen(courseArg: Course(id: extra));
        }
        return const CourseDetailsScreen();
      },
    ),
    GoRoute(
      path: AppRoutes.courseDetailsScreenNotPurchase,
      builder: (_, state) =>
          CourseDetailsNotPurchaseScreen(courseArg: state.extra),
    ),
    GoRoute(
      path: AppRoutes.courseDetailsOfflineScreen,
      builder: (_, state) =>
          CourseDetailsOfflineScreen(courseArg: state.extra as Course?),
    ),
    GoRoute(path: AppRoutes.profile, builder: (_, __) => const ProfileScreen()),
    GoRoute(
      path: AppRoutes.instructorDetails,
      builder: (_, state) => InstructorDetailScreen(instructorArg: state.extra),
    ),
    GoRoute(
      path: AppRoutes.couponScreen,
      builder: (_, __) => const CouponScreen(),
    ),
    GoRoute(
      path: AppRoutes.organizationScreen,
      builder: (_, state) => OrganizationScreen(orgId: state.extra! as int),
    ),
    GoRoute(
      path: AppRoutes.orderHistoryScreen,
      builder: (_, __) => const OrderHistoryScreen(),
    ),
    GoRoute(
      path: AppRoutes.meetingScreen,
      builder: (_, __) => const MeetingScreen(),
    ),
    GoRoute(
      path: AppRoutes.userCertificateScreen,
      builder: (_, __) => const UserCertificatesScreen(),
    ),
    GoRoute(
      path: AppRoutes.editProfileScreen,
      builder: (_, __) => const EditProfileScreen(),
    ),
    GoRoute(
      path: AppRoutes.quizStartPage,
      builder: (_, state) => const QuizStartPage(),
    ),
    GoRoute(
      path: AppRoutes.learningScreen,
      builder: (_, state) {
        final args = state.extra as LearningRouteArgs?;
        return LearningScreen(
          fromPage: args?.fromPage ?? state.uri.queryParameters['fromPage'],
          lesson: args?.lesson,
          isOnline:
              args?.isOnline ?? state.uri.queryParameters['isOnline'] == 'true',
        );
      },
    ),
    GoRoute(
      path: AppRoutes.landingLearningScreen,
      builder: (_, state) {
        final args = state.extra! as LandingLearningRouteArgs;
        return LandingLearningScreen(
          fromPage: args.fromPage,
          indexLesson: args.indexLesson,
          indexChapter: args.indexChapter,
          listChapter: args.listChapter,
          isOnline: args.isOnline,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.quizDetailsScreen,
      builder: (_, state) => QuizDetailScreen(quiz: state.extra as DataQuiz?),
    ),
    GoRoute(
      path: AppRoutes.quizResultScreen,
      builder: (_, state) => QuizResultScreen(
        givenAnswerListArg: state.extra as List<GivenAnswer>?,
      ),
    ),
    GoRoute(
      path: AppRoutes.notificationScreen,
      builder: (_, __) => const NotificationScreen(),
    ),
    GoRoute(
      path: AppRoutes.myCourseScreen,
      builder: (_, __) => const MyCoursesScreen(),
    ),
    GoRoute(
      path: AppRoutes.membershipScreen,
      builder: (_, __) => const MembershipScreen(),
    ),
    GoRoute(
      path: AppRoutes.pdfViewerScreen,
      builder: (_, state) {
        final args = state.extra! as Map<String, dynamic>;
        return PDFViewerScreen(
          pdfUrl: args['pdfUrl'] as String,
          fileName: args['fileName'] as String,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.viewAllCoursesScreen,
      builder: (_, state) {
        final args = state.extra! as Map<String, dynamic>;
        return ViewAllCourses(
          title: args['title'] as String,
          courseList: args['courseList'] as List<Course>,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.mainOfflineScreen,
      builder: (_, __) => const MainOfflineScreen(pageIndex: 0),
    ),
    GoRoute(
      path: AppRoutes.newsTagScreen,
      builder: (_, state) => NewsTag(tag: state.extra as String? ?? ''),
    ),
    GoRoute(
      path: AppRoutes.viewUserCertificateScreen,
      builder: (_, state) =>
          ViewUserCertificate(filePath: state.extra as String?),
    ),
    // ---------------------------------------------------------------------------
    // Phase-4 feature routes
    // ---------------------------------------------------------------------------
    GoRoute(
      path: AppRoutes.homeDashboard,
      builder: (_, __) => const HomeDashboardScreen(),
    ),
    GoRoute(
      path: AppRoutes.courseCatalog,
      builder: (_, __) => const CourseCatalogScreen(),
    ),
    GoRoute(
      path: '/courses/:courseId',
      builder: (_, state) {
        final id = int.tryParse(state.pathParameters['courseId'] ?? '') ?? 0;
        return CourseDetailScreen(courseId: id);
      },
    ),
    GoRoute(
      path: '/courses/:courseId/lessons/:lessonId',
      builder: (_, state) {
        final cid = int.tryParse(state.pathParameters['courseId'] ?? '') ?? 0;
        final lid = int.tryParse(state.pathParameters['lessonId'] ?? '') ?? 0;
        return LessonViewScreen(courseId: cid, lessonId: lid);
      },
    ),
  ],
);
