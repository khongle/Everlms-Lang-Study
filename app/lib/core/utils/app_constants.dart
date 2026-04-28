import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/root/data/models/response/language_model.dart';

class AppConstants {
  static const String appName = 'EverLMS';
  static const String baseUrl = 'baseUrl';

  static const String loginUrl = 'user/login?_format=json';
  static const String registerUrl = 'user/register?_format=json';
  static const String register = 'lms-register-user';

  static const String socialAuth = 'lms-social-auth';

  static String logout(String token) => 'user/logout?_format=json&token=$token';

  static const String refreshToken = 'jwt/token';
  static const String sessionToken = 'session/token';
  static String resetPassword(String uid) => 'user/$uid?_format=json';
  static String userDataDrupal(String mail) =>
      'jsonapi/user/user?filter[mail]=$mail';
  // Profile
  static String user(String uid) => 'lms-user/$uid?_format=json';
  static String updateUser(String uid) => 'lms-update-user/$uid?_format=json';
  static String statusUser(String email) => 'lms-status-user/$email';
  // email otp
  static const String sendEmailOTP = 'lms-send-email-otp';

  // Course
  static String myCourse(String uid) => 'lms-my-courses/$uid';
  static const String addCourse = 'lms-add-course?_format=json';
  static String teacherCourse(String teacherUid) =>
      'teacher-courses/$teacherUid?_format=json';
  static const String lmsCourse = '/lms-courses';
  static String lmsCoursesNotAccess(String userId) =>
      'lms-get-courses-not-access/$userId';
  static String lmsCoursesAccess(String userId) =>
      'lms-get-courses-access/$userId';
  static String listChapter(String courseId) =>
      'lms-get-chapter-by-course/$courseId';
  // use for download and extract, need to update follow new structure
  static String listLesson(String courseId) =>
      'lms-lesson-by-course/$courseId?_format=json';
  //
  static const String lmsCheckCoursePayment = 'lms-check-course-payment';
  static const String lmsCheckPermission = 'lms-check-permission';
  static const String lmsUserCourseCheck = 'lms-user-course-check';
  static const String lmsUserChapterProgress = 'lms-user-chapter-progress';
  static const String lmsUpdateStatusLesson = 'lms-update-status-lesson';

  static const String lmsCreateComment = 'lms-post-comment';
  static const String lmsGetComments = 'lms-get-comment';
  static const String reviews = 'lms-get-reviews';
  static const String writeReviews = 'lms-write-review';

  // Quiz
  static const String getListQuizByCourseId = 'lms-quiz-by-course';
  static const String getListQuizByChapterId = 'lms-quiz-by-chapter';
  static const String getListQuizByLessonId = 'lms-quiz-by-lesson';
  static const String submitQuiz = 'lms-submit-quiz';
  // static const String getQuizResult = 'lms-get-quiz-result'; // submit will return result

  // Forum
  static const String getForum = 'lms-get-forums';
  static const String getListTopic = 'lms-get-topics';
  // static const String getReplyList = 'lms-get-forums-current-user';
  static String getTopic(String topicId) => 'lms-get-topic/$topicId';
  static const String createForumTopic = 'lms-create-forum-topic';
  static const String createComment = 'lms-post-comment-forum-topic';
  static const String lmsGetForumsOffline = 'lms-get-forums-offline';

  // Faq
  static const String getFaq = 'lms-get-faq';

  // News
  static const String getNews = 'lms-news';

  // Language : useless
  static const String language = 'lms-languages?_format=json';

  // Cart
  static String getCart(String userId) => 'lms-get-cart/$userId';
  static const String addToCart = 'lms-add-cart';
  static const String emptyCart = 'lms-empty-cart';
  static const String lmsRemoveItemCart = 'lms-remove-item-cart';
  static const String updateCompleteCart = 'lms-order-complete';
  static const String lmsStartCourse = 'lms-start-course';

  /// Membership
  static const String getMembership = 'lms-membership-api';

  static String userCertificates(String uid) => 'lms-get-user-certificate/$uid';

  /// Basic page
  static const String getBasicPage = 'lms-get-basic-page';

  ///---------------------------------------------------------------------------

  // Domain example of template
  static const String configUrl = 'configs';
  static const String onBoards = 'on-boards';

  static const String socialLoginUrl = 'social-login';

  static const String registerWithPhone = 'register-by-phone';
  static const String verifyPhoneOTP = 'verify-phone-otp';
  static const String resendPhoneOTP = 'resend-phone-otp';
  static const String getPhoneLoginOTP = 'get-login-otp';
  static const String verifyLoginPhoneOTP = 'verify-login-otp';

  static const String resendEmailOTP = 'resend-email-otp';

  static const String verifyOTPForForgotPassword = 'verify-otp';

  // ---------------------------------------------------------------------------
  // Mobile API — OAuth2 + new auth endpoints (lms_mobile_api module)
  // ---------------------------------------------------------------------------

  /// OAuth2 password-grant token endpoint (provided by Simple OAuth module).
  static const String oauthToken = 'oauth/token';

  /// OAuth2 token revocation endpoint.
  static const String oauthTokenRevoke = 'oauth/token/revoke';

  /// Mobile registration: creates blocked user + sends OTP email.
  static const String mobileRegister = 'api/v1/user/register';

  /// Verify registration OTP → activates account + returns OAuth2 tokens.
  static const String mobileRegisterVerifyOtp =
      'api/v1/user/register/verify-otp';

  /// Resend registration OTP; returns a new otp_token.
  static const String mobileRegisterResendOtp =
      'api/v1/user/register/resend-otp';

  /// Request forgot-password OTP email.
  static const String mobileForgotPassword = 'api/v1/user/forgot-password';

  /// Verify forgot-password OTP → returns reset_token.
  static const String mobileForgotPasswordVerifyOtp =
      'api/v1/user/forgot-password/verify-otp';

  /// Reset password using the one-time reset_token.
  static const String mobileResetPassword = 'api/v1/user/reset-password';

  /// UID Login: email + password → {uid, access_token, ...}
  static const String mobileLogin = 'api/v1/oauth/token';

  /// Mobile user profile (GET, requires Bearer token).
  static const String mobileUserProfile = 'api/v1/user/profile';

  /// Mobile change password (POST, requires Bearer token).
  static const String mobileChangePassword = 'api/v1/user/change-password';

  // ---------------------------------------------------------------------------
  // Mobile API — Course & Dashboard endpoints (phase-01)
  // ---------------------------------------------------------------------------

  /// Dashboard summary: enrolled courses, progress, certificates count.
  static const String mobileDashboard = 'api/v1/dashboard';

  /// List published course categories with course counts.
  static const String mobileCategories = 'api/v1/categories';

  /// Course detail (optionally authenticated for enrollment status).
  static String mobileCourseDetail(String courseId) =>
      'api/v1/courses/$courseId';

  /// Enroll authenticated user in a free course.
  static String mobileEnrollFree(String courseId) =>
      'api/v1/courses/$courseId/enroll-free';

  /// Lesson detail for a specific course lesson.
  static String mobileLessonDetail(String courseId, String lessonId) =>
      'api/v1/courses/$courseId/lessons/$lessonId';

  /// Mark lesson as complete; returns next lesson info.
  static const String mobileMarkComplete = 'api/lms/progress/mark-complete';

  /// Unread notification badge count.
  static const String mobileNotificationUnreadCount =
      'api/lms/notifications/unread-count';

  /// Toggle wishlist status for a course.
  static const String mobileWishlistToggle = 'api/lms/wishlist/toggle';

  /// Check wishlist status for a list of courses.
  static const String mobileWishlistStatus = 'api/lms/wishlist/status';

  /// Get note for a lesson.
  static const String mobileLessonNoteGet = 'api/lms/note/get';

  /// Save/update note for a lesson.
  static const String mobileLessonNoteSave = 'api/lms/note/save';

  /// Search courses: keyword, category, level, price filters + pagination.
  static const String mobileCourseSearch = 'api/courses/search';

  // SharedPreferences keys for OAuth2 tokens.

  /// Stores the OAuth2 refresh_token value.
  static const String oauthRefreshToken = 'lms_oauth_refresh_token';

  /// Stores the current OTP session token (otp_token) during registration /
  /// forgot-password flows.
  static const String otpSessionToken = 'lms_otp_session_token';

  /// Stores the one-time reset_token during the forgot-password reset flow.
  static const String resetToken = 'lms_reset_token';

  static const String explore = 'explore';
  static const String homeScreen = 'home-screen';
  static const String latestCourse = 'latest-courses';
  static const String instructorProfile = 'instructor/profile';
  static const String instructorCourseList = 'instructor/courses';
  static const String instructorStudentList = 'instructor/students';
  static const String followUnfollow = 'user/follow-unfollow';
  //organization
  static const String organizationProfile = 'organization/profile';
  static const String organizationCourseList = 'organization/courses';
  static const String organizationInstructorList = 'organization/instructors';
  //my course screen
  static const String myCourseList = 'user/my-courses';
  static const String myWishList = 'user/my-wishlists';
  static const String addOrRemoveWishList = 'user/add-remove-wishlist';
  //cart
  // static const String addToCart = 'user/add-to-cart';
  static const String saveProgress = 'user/save-progress';
  static const String removeFromCart = 'user/carts';
  static const String cartList = 'user/carts';
  static const String applyCoupon = 'user/apply-coupon';
  static const String couponList = 'user/coupons';
  static const String meetingList = 'user/student/meeting';
  static const String myResources = 'user/my-resources';

  static String deleteAccount(String uid) => 'lms-delete-user/$uid';
  //order history
  static const String orderHistory = 'user/order-histories';

  //search
  static const String searchCourses = 'api/courses/search';
  //category
  static const String courseCategories = 'categories';
  static const String categoryCourses = 'category-courses'; // 404
  static const String certificates = 'user/certificates';
  static const String certificateDownload = 'user/certificate-download';
  static const String courseSection = 'user/course-sections';
  //quiz
  static const String quizData = 'user/questions';
  //notification
  static const String notificationApi = 'user/notifications';

  static const String basicAuth = 'lms_auth';

  static const String theme = 'lms_theme';
  static const String token = 'lms_token';
  static const String provider = 'provider';
  static const String logoutToken = 'logoutToken';
  static const String cookie = 'cookie';
  static const String uid = 'lms_uid';
  static const String csrfToken = 'lms_csrf_token';
  static const String countryCode = 'lms_country_code';
  static const String languageCode = 'lms_language_code';
  static const String userPassword = 'lms_user_password';
  static const String userNumber = 'lms_user_number';
  static const String userCountryCode = 'lms_user_country_code';
  static String notification(String uid) => 'lms-get-notification-by-user/$uid';
  static const String isOnBoardScreen = 'lms_on_board_seen';
  static const String topic = 'customer';
  static const String createForumTopicOffline = 'create_forum_topic_offline';
  static const String createCommentOffline = 'create_comment_offline';
  static const String localizationKey = 'X-localization';
  static const String configUri = '/api/v1/customer/config';
  static const String socialRegisterUrl = '/api/v1/auth/social-register';
  static const String tokenUrl = '/api/v1/customer/update/fcm-token';
  static const String notificationCount = '/api/v1/customer/notification';
  static const String resetPasswordUrl = '/api/v1/customer/notification';
  static const String customerInfoUrl = '/api/v1/customer/notification';
  static const String updateProfileUrl = '/api/v1/customer/notification';
  static const String verifyTokenUrl = '/api/v1/customer/notification';

  static const String pages = '/api/v1/customer/config/pages';

  static const String verifyPhoneUrl = '/api/v1/customer/config/pages';
  static const String subscriptionUrl = '/api/v1/customer/config/pages';
  static const String createChannel = 'createChannel';
  static const String getChannelList = 'get-channel-list';
  static const String getConversation = 'get-conversation';
  static const String searchHistory = 'search-history';

  static List<LanguageModel> languages = [
    LanguageModel(
      imageUrl: Images.us,
      languageName: 'English',
      countryCode: 'US',
      languageCode: 'en',
    ),
    LanguageModel(
      imageUrl: Images.arabicTwo,
      languageName: 'Việt Nam',
      countryCode: 'VN',
      languageCode: 'vi',
    ),
  ];

  // User profile keys
  static const String userFirstName = 'user_first_name';

  // External URLs
  static const String termsUrl = 'https://everlms.com/terms-use';
  static const String privacyPolicyUrl = 'https://everlms.com/privacy-policy';

  static const String anonymous = 'anonymous';
  static const String authenticated = 'authenticated';
  static const String membership = 'membership';
  static const String student = 'student';
  static const String teacher = 'teacher';
  static const String contentEditor = 'content_editor';
  static const String administrator = 'administrator';
  static const String hostZoomClass = 'host_zoom_class';
}
