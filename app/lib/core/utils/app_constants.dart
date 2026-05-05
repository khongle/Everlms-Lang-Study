import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/root/data/models/response/language_model.dart';

class AppConstants {
  static const String appName = 'EverLMS';
  static const String baseUrl = 'baseUrl';

  // ---------------------------------------------------------------------------
  // SharedPreferences keys
  // ---------------------------------------------------------------------------

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
  static const String isOnBoardScreen = 'lms_on_board_seen';
  static const String localizationKey = 'X-localization';
  static const String createForumTopicOffline = 'create_forum_topic_offline';
  static const String createCommentOffline = 'create_comment_offline';

  /// Stores the OAuth2 refresh_token value.
  static const String oauthRefreshToken = 'lms_oauth_refresh_token';

  /// Stores the current OTP session token (otp_token) during registration /
  /// forgot-password flows.
  static const String otpSessionToken = 'lms_otp_session_token';

  /// Stores the one-time reset_token during the forgot-password reset flow.
  static const String resetToken = 'lms_reset_token';

  // User profile keys
  static const String userFirstName = 'user_first_name';

  // ---------------------------------------------------------------------------
  // Mobile API — Auth (phase-01 · 01-auth)
  // ---------------------------------------------------------------------------

  /// UID Login: email + password → {uid, access_token, ...}
  static const String mobileLogin = 'api/v1/oauth/token';

  /// OAuth2 password-grant token endpoint (Simple OAuth module).
  static const String oauthToken = 'oauth/token';

  /// OAuth2 token revocation endpoint.
  static const String oauthTokenRevoke = 'oauth/token/revoke';

  /// Mobile registration: creates blocked user + sends OTP email.
  static const String mobileRegister = 'api/v1/user/register';

  /// Verify registration OTP → activates account + returns tokens.
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

  // ---------------------------------------------------------------------------
  // Mobile API — User Profile (phase-01 · 12-user)
  // ---------------------------------------------------------------------------

  /// GET /api/v1/user/me — current user's profile (Bearer required).
  static const String mobileUserMe = 'api/v1/user/me';

  /// PATCH /api/v1/user/profile — update profile fields (Bearer required).
  static const String mobileUserProfile = 'api/v1/user/profile';

  /// POST /api/v1/user/avatar — upload avatar (Bearer required).
  static const String mobileUserAvatar = 'api/v1/user/avatar';

  /// Mobile change password (POST, requires Bearer token).
  static const String mobileChangePassword = 'api/v1/user/change-password';

  // ---------------------------------------------------------------------------
  // Mobile API — Home Dashboard (phase-01 · 02-course)
  // ---------------------------------------------------------------------------

  /// Dashboard summary: in-progress courses, completed count, certificates count.
  static const String mobileDashboard = 'api/v1/dashboard';

  /// Search / list courses with filters + pagination.
  static const String mobileCourseSearch = 'api/v1/courses/search';

  /// List published course categories with course counts.
  static const String mobileCategories = 'api/v1/categories';

  // ---------------------------------------------------------------------------
  // Mobile API — Course Detail (phase-01 · 02-course)
  // ---------------------------------------------------------------------------

  /// Course detail (optionally authenticated for enrollment status).
  static String mobileCourseDetail(String courseId) =>
      'api/v1/courses/$courseId';

  /// Enroll authenticated user in a free course.
  static String mobileEnrollFree(String courseId) =>
      'api/v1/courses/$courseId/enroll-free';

  // ---------------------------------------------------------------------------
  // Mobile API — Lesson (phase-01 · 05-lesson)
  // ---------------------------------------------------------------------------

  /// Lesson detail for a specific course lesson.
  static String mobileLessonDetail(String courseId, String lessonId) =>
      'api/v1/courses/$courseId/lessons/$lessonId';

  // ---------------------------------------------------------------------------
  // Mobile API — Progress (phase-01 · 07-progress)
  // ---------------------------------------------------------------------------

  /// Mark lesson as complete; returns next lesson info.
  static const String mobileMarkComplete = 'api/lms/progress/mark-complete';

  /// Get enrolled courses list.
  static const String mobileEnrolledCourses = 'api/v1/my-courses';

  // ---------------------------------------------------------------------------
  // Mobile API — Certificates (phase-01 · 08-certificate)
  // ---------------------------------------------------------------------------

  /// List current user's certificates.
  static const String mobileCertificates = 'api/v1/certificates';

  /// Download certificate PDF by ID.
  static String mobileCertificateDownload(String id) =>
      'api/v1/certificates/$id/download';

  // ---------------------------------------------------------------------------
  // Mobile API — Wishlist (phase-01 · 09-wishlist)
  // ---------------------------------------------------------------------------

  /// Toggle wishlist status for a course.
  static const String mobileWishlistToggle = 'api/lms/wishlist/toggle';

  /// Check wishlist status for a list of courses.
  static const String mobileWishlistStatus = 'api/lms/wishlist/status';

  /// Get full wishlist.
  static const String mobileWishlist = 'api/v1/wishlist';

  // ---------------------------------------------------------------------------
  // Mobile API — Notifications (phase-01 · 10-notification)
  // ---------------------------------------------------------------------------

  /// Unread notification badge count.
  static const String mobileNotificationUnreadCount =
      'api/lms/notifications/unread-count';

  /// List notifications.
  static const String mobileNotifications = 'api/v1/notifications';

  /// Mark notification(s) as read.
  static const String mobileNotificationsMarkRead =
      'api/v1/notifications/mark-read';

  // ---------------------------------------------------------------------------
  // Mobile API — Lesson Notes (phase-01 · 11-note)
  // ---------------------------------------------------------------------------

  /// Get note for a lesson.
  static const String mobileLessonNoteGet = 'api/lms/note/get';

  /// Save/update note for a lesson.
  static const String mobileLessonNoteSave = 'api/lms/note/save';

  /// Delete note for a lesson.
  static const String mobileLessonNoteDelete = 'api/lms/note/delete';

  // ---------------------------------------------------------------------------
  // Mobile API — Ratings (phase-01 · 13-rating)
  // ---------------------------------------------------------------------------

  /// Get ratings for a course.
  static String mobileCourseRatings(String courseId) =>
      'api/v1/courses/$courseId/ratings';

  /// Submit a new rating.
  static const String mobileRatingsSubmit = 'api/v1/ratings';

  /// Update an existing rating.
  static String mobileRatingUpdate(String id) => 'api/v1/ratings/$id';

  // ---------------------------------------------------------------------------
  // Mobile API — Quiz (phase-01 · 06-quiz)
  // ---------------------------------------------------------------------------

  /// Get quiz by ID.
  static String mobileQuiz(String quizId) => 'api/v1/quiz/$quizId';

  /// Submit quiz answers.
  static String mobileQuizSubmit(String quizId) => 'api/v1/quiz/$quizId/submit';

  /// Get quiz result by result ID.
  static String mobileQuizResult(String resultId) =>
      'api/v1/quiz/results/$resultId';

  /// List current user's quiz results.
  static const String mobileMyQuizResults = 'api/v1/quiz/my-results';

  // ---------------------------------------------------------------------------
  // Mobile API — News (phase-01 · 14-news)
  // ---------------------------------------------------------------------------

  /// List news/blog articles.
  static const String mobileNews = 'api/v1/news';

  /// News article detail.
  static String mobileNewsDetail(String id) => 'api/v1/news/$id';

  // ---------------------------------------------------------------------------
  // Mobile API — Glossary (phase-01 · 15-glossary)
  // ---------------------------------------------------------------------------

  /// List all glossary terms (grouped by letter).
  static const String mobileGlossary = 'api/v1/glossary';

  /// Single glossary term detail.
  static String mobileGlossaryDetail(String id) => 'api/v1/glossary/$id';

  // ---------------------------------------------------------------------------
  // Mobile API — FCM / Device token
  // ---------------------------------------------------------------------------

  static const String tokenUrl = '/api/v1/customer/update/fcm-token';

  // ---------------------------------------------------------------------------
  // App config
  // ---------------------------------------------------------------------------

  static const String topic = 'customer';

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

  // External URLs
  static const String termsUrl = 'https://everlms.com/terms-use';
  static const String privacyPolicyUrl = 'https://everlms.com/privacy-policy';

  // Role constants
  static const String anonymous = 'anonymous';
  static const String authenticated = 'authenticated';
  static const String membership = 'membership';
  static const String student = 'student';
  static const String teacher = 'teacher';
  static const String contentEditor = 'content_editor';
  static const String administrator = 'administrator';
  static const String hostZoomClass = 'host_zoom_class';

  // ---------------------------------------------------------------------------
  // Legacy API endpoints — NOT in phase-01 spec (do not delete)
  // ---------------------------------------------------------------------------

  @Deprecated('Use mobileLogin (api/v1/oauth/token) instead.')
  static const String loginUrl = 'user/login?_format=json';
  @Deprecated('Use mobileRegister (api/v1/user/register) instead.')
  static const String registerUrl = 'user/register?_format=json';
  @Deprecated('Use mobileRegister (api/v1/user/register) instead.')
  static const String register = 'lms-register-user';
  static const String socialAuth = 'lms-social-auth';
  static String logout(String token) => 'user/logout?_format=json&token=$token';
  static const String refreshToken = 'jwt/token';
  static const String sessionToken = 'session/token';
  static String resetPassword(String uid) => 'user/$uid?_format=json';
  static String userDataDrupal(String mail) =>
      'jsonapi/user/user?filter[mail]=$mail';
  @Deprecated('Use mobileUserMe (api/v1/user/me) instead.')
  static String user(String uid) => 'lms-user/$uid?_format=json';
  @Deprecated('Use mobileUserProfile (api/v1/user/profile) instead.')
  static String updateUser(String uid) => 'lms-update-user/$uid?_format=json';
  static String statusUser(String email) => 'lms-status-user/$email';
  static const String sendEmailOTP = 'lms-send-email-otp';
  @Deprecated('Use mobileEnrolledCourses (api/v1/my-courses) instead.')
  static String myCourse(String uid) => 'lms-my-courses/$uid';
  static const String addCourse = 'lms-add-course?_format=json';
  static String teacherCourse(String teacherUid) =>
      'teacher-courses/$teacherUid?_format=json';
  static const String lmsCourse = '/lms-courses';
  @Deprecated('Use mobileCourseSearch (api/courses/search) instead.')
  static String lmsCoursesNotAccess(String userId) =>
      'lms-get-courses-not-access/$userId';
  @Deprecated('Use mobileEnrolledCourses (api/v1/my-courses) instead.')
  static String lmsCoursesAccess(String userId) =>
      'lms-get-courses-access/$userId';
  @Deprecated(
    'Curriculum is now embedded in mobileCourseDetail (api/v1/courses/:id).',
  )
  static String listChapter(String courseId) =>
      'lms-get-chapter-by-course/$courseId';
  // use for download and extract, need to update follow new structure
  static String listLesson(String courseId) =>
      'lms-lesson-by-course/$courseId?_format=json';
  static const String lmsCheckCoursePayment = 'lms-check-course-payment';
  static const String lmsCheckPermission = 'lms-check-permission';
  static const String lmsUserCourseCheck = 'lms-user-course-check';
  static const String lmsUserChapterProgress = 'lms-user-chapter-progress';
  static const String lmsUpdateStatusLesson = 'lms-update-status-lesson';
  static const String lmsCreateComment = 'lms-post-comment';
  static const String lmsGetComments = 'lms-get-comment';
  @Deprecated('Use mobileCourseRatings (api/v1/courses/:id/ratings) instead.')
  static const String reviews = 'lms-get-reviews';
  @Deprecated('Use mobileRatingsSubmit (api/v1/ratings) instead.')
  static const String writeReviews = 'lms-write-review';
  @Deprecated('Use mobileQuiz (api/v1/quiz/:id) instead.')
  static const String getListQuizByCourseId = 'lms-quiz-by-course';
  @Deprecated('Use mobileQuiz (api/v1/quiz/:id) instead.')
  static const String getListQuizByChapterId = 'lms-quiz-by-chapter';
  @Deprecated('Use mobileQuiz (api/v1/quiz/:id) instead.')
  static const String getListQuizByLessonId = 'lms-quiz-by-lesson';
  @Deprecated('Use mobileQuizSubmit (api/v1/quiz/:id/submit) instead.')
  static const String submitQuiz = 'lms-submit-quiz';
  static const String getForum = 'lms-get-forums';
  static const String getListTopic = 'lms-get-topics';
  static String getTopic(String topicId) => 'lms-get-topic/$topicId';
  static const String createForumTopic = 'lms-create-forum-topic';
  static const String createComment = 'lms-post-comment-forum-topic';
  static const String lmsGetForumsOffline = 'lms-get-forums-offline';
  static const String getFaq = 'lms-get-faq';
  @Deprecated('Use mobileNews (api/v1/news) instead.')
  static const String getNews = 'lms-news';
  static const String language = 'lms-languages?_format=json';
  static String getCart(String userId) => 'lms-get-cart/$userId';
  static const String addToCart = 'lms-add-cart';
  static const String emptyCart = 'lms-empty-cart';
  static const String lmsRemoveItemCart = 'lms-remove-item-cart';
  static const String updateCompleteCart = 'lms-order-complete';
  @Deprecated('Use mobileEnrollFree (api/v1/courses/:id/enroll-free) instead.')
  static const String lmsStartCourse = 'lms-start-course';
  static const String getMembership = 'lms-membership-api';
  @Deprecated('Use mobileCertificates (api/v1/certificates) instead.')
  static String userCertificates(String uid) => 'lms-get-user-certificate/$uid';
  static const String getBasicPage = 'lms-get-basic-page';
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
  static const String explore = 'explore';
  @Deprecated('Use mobileDashboard (api/v1/dashboard) instead.')
  static const String homeScreen = 'home-screen';
  @Deprecated('Use mobileCourseSearch (api/courses/search) instead.')
  static const String latestCourse = 'latest-courses';
  static const String instructorProfile = 'instructor/profile';
  static const String instructorCourseList = 'instructor/courses';
  static const String instructorStudentList = 'instructor/students';
  static const String followUnfollow = 'user/follow-unfollow';
  static const String organizationProfile = 'organization/profile';
  static const String organizationCourseList = 'organization/courses';
  static const String organizationInstructorList = 'organization/instructors';
  @Deprecated('Use mobileEnrolledCourses (api/v1/my-courses) instead.')
  static const String myCourseList = 'user/my-courses';
  @Deprecated('Use mobileWishlist (api/v1/wishlist) instead.')
  static const String myWishList = 'user/my-wishlists';
  @Deprecated('Use mobileWishlistToggle (api/lms/wishlist/toggle) instead.')
  static const String addOrRemoveWishList = 'user/add-remove-wishlist';
  static const String saveProgress = 'user/save-progress';
  static const String removeFromCart = 'user/carts';
  static const String cartList = 'user/carts';
  static const String applyCoupon = 'user/apply-coupon';
  static const String couponList = 'user/coupons';
  static const String meetingList = 'user/student/meeting';
  static const String myResources = 'user/my-resources';
  static String deleteAccount(String uid) => 'lms-delete-user/$uid';
  static const String orderHistory = 'user/order-histories';
  @Deprecated('Use mobileCourseSearch (api/courses/search) instead.')
  static const String searchCourses = 'api/courses/search';
  static const String courseCategories = 'categories';
  static const String categoryCourses = 'category-courses'; // 404
  @Deprecated('Use mobileCertificates (api/v1/certificates) instead.')
  static const String certificates = 'user/certificates';
  @Deprecated(
    'Use mobileCertificateDownload(id) (api/v1/certificates/:id/download) instead.',
  )
  static const String certificateDownload = 'user/certificate-download';
  static const String courseSection = 'user/course-sections';
  static const String quizData = 'user/questions';
  @Deprecated('Use mobileNotifications (api/v1/notifications) instead.')
  static const String notificationApi = 'user/notifications';
  @Deprecated('Use mobileNotifications (api/v1/notifications) instead.')
  static String notification(String uid) => 'lms-get-notification-by-user/$uid';
  static const String configUri = '/api/v1/customer/config';
  static const String socialRegisterUrl = '/api/v1/auth/social-register';
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
}
