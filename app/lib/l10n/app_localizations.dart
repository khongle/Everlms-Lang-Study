import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_en.dart';
import 'app_localizations_vi.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
      : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
    delegate,
    GlobalMaterialLocalizations.delegate,
    GlobalCupertinoLocalizations.delegate,
    GlobalWidgetsLocalizations.delegate,
  ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('en'),
    Locale('vi')
  ];

  /// No description provided for @app_name.
  ///
  /// In en, this message translates to:
  /// **'Faculty'**
  String get app_name;

  /// No description provided for @intro_screen_text_1.
  ///
  /// In en, this message translates to:
  /// **'Unlock your career with Faculty app'**
  String get intro_screen_text_1;

  /// No description provided for @intro_screen_text_2.
  ///
  /// In en, this message translates to:
  /// **'Hassle free admission preparation'**
  String get intro_screen_text_2;

  /// No description provided for @intro_screen_text_3.
  ///
  /// In en, this message translates to:
  /// **'Participate in daily life classes'**
  String get intro_screen_text_3;

  /// No description provided for @intro_screen_subtitle_1.
  ///
  /// In en, this message translates to:
  /// **'Powerup your skills'**
  String get intro_screen_subtitle_1;

  /// No description provided for @intro_screen_subtitle_2.
  ///
  /// In en, this message translates to:
  /// **'at the palm of your hand'**
  String get intro_screen_subtitle_2;

  /// No description provided for @intro_screen_subtitle_3.
  ///
  /// In en, this message translates to:
  /// **'and keep yourself engaged'**
  String get intro_screen_subtitle_3;

  /// No description provided for @home.
  ///
  /// In en, this message translates to:
  /// **'Home'**
  String get home;

  /// No description provided for @bestseller.
  ///
  /// In en, this message translates to:
  /// **'Bestseller'**
  String get bestseller;

  /// No description provided for @login.
  ///
  /// In en, this message translates to:
  /// **'Log in'**
  String get login;

  /// No description provided for @forgot_password.
  ///
  /// In en, this message translates to:
  /// **'Forgot password?'**
  String get forgot_password;

  /// No description provided for @email_address.
  ///
  /// In en, this message translates to:
  /// **'Email Address'**
  String get email_address;

  /// No description provided for @password.
  ///
  /// In en, this message translates to:
  /// **'Password'**
  String get password;

  /// No description provided for @confirm_password.
  ///
  /// In en, this message translates to:
  /// **'Confirm Password'**
  String get confirm_password;

  /// No description provided for @sign_in_with_phone.
  ///
  /// In en, this message translates to:
  /// **'Sign in with phone'**
  String get sign_in_with_phone;

  /// No description provided for @sign_up_with_phone.
  ///
  /// In en, this message translates to:
  /// **'Sign Up with phone'**
  String get sign_up_with_phone;

  /// No description provided for @new_user.
  ///
  /// In en, this message translates to:
  /// **'New to EverLMS?'**
  String get new_user;

  /// No description provided for @account_exists.
  ///
  /// In en, this message translates to:
  /// **'Account Already Exists'**
  String get account_exists;

  /// No description provided for @account_exists_message.
  ///
  /// In en, this message translates to:
  /// **'An account with this email already exists. Please use a different email address or sign in'**
  String get account_exists_message;

  /// No description provided for @account_not_found.
  ///
  /// In en, this message translates to:
  /// **'Account Not Found'**
  String get account_not_found;

  /// No description provided for @account_not_found_message.
  ///
  /// In en, this message translates to:
  /// **'We couldn\'t find an account with this email address. Please make sure you\'ve entered the correct email.'**
  String get account_not_found_message;

  /// No description provided for @account_block.
  ///
  /// In en, this message translates to:
  /// **'Account Blocked'**
  String get account_block;

  /// No description provided for @account_block_message.
  ///
  /// In en, this message translates to:
  /// **'Your account has been blocked. Please contact the administrator for further assistance.'**
  String get account_block_message;

  /// No description provided for @too_many_fail_attempts.
  ///
  /// In en, this message translates to:
  /// **'Too many fail attempts. Your account has been blocked, please contact the administrator'**
  String get too_many_fail_attempts;

  /// No description provided for @create_an_account.
  ///
  /// In en, this message translates to:
  /// **'Create an Account'**
  String get create_an_account;

  /// No description provided for @or_continue_with.
  ///
  /// In en, this message translates to:
  /// **'Or continue with'**
  String get or_continue_with;

  /// No description provided for @continueAction.
  ///
  /// In en, this message translates to:
  /// **'Continue'**
  String get continueAction;

  /// No description provided for @sign_up.
  ///
  /// In en, this message translates to:
  /// **'Sign up'**
  String get sign_up;

  /// No description provided for @full_name.
  ///
  /// In en, this message translates to:
  /// **'Full Name'**
  String get full_name;

  /// No description provided for @first_name.
  ///
  /// In en, this message translates to:
  /// **'First Name'**
  String get first_name;

  /// No description provided for @last_name.
  ///
  /// In en, this message translates to:
  /// **'Last Name'**
  String get last_name;

  /// No description provided for @sign_in_here.
  ///
  /// In en, this message translates to:
  /// **'Sign in here'**
  String get sign_in_here;

  /// No description provided for @already_have_an_account.
  ///
  /// In en, this message translates to:
  /// **'Already have an account?'**
  String get already_have_an_account;

  /// No description provided for @phone_number.
  ///
  /// In en, this message translates to:
  /// **'Phone number'**
  String get phone_number;

  /// No description provided for @submit.
  ///
  /// In en, this message translates to:
  /// **'Submit'**
  String get submit;

  /// No description provided for @continue_as_a_guest.
  ///
  /// In en, this message translates to:
  /// **'Continue as a Guest'**
  String get continue_as_a_guest;

  /// No description provided for @next.
  ///
  /// In en, this message translates to:
  /// **'Next'**
  String get next;

  /// No description provided for @get_started.
  ///
  /// In en, this message translates to:
  /// **'Get Started'**
  String get get_started;

  /// No description provided for @back_to_login.
  ///
  /// In en, this message translates to:
  /// **'Back to log in'**
  String get back_to_login;

  /// No description provided for @incorrect_otp.
  ///
  /// In en, this message translates to:
  /// **'Incorrect OTP.'**
  String get incorrect_otp;

  /// No description provided for @alert_block_user.
  ///
  /// In en, this message translates to:
  /// **'Incorrect OTP. {time} attempts remain before account blocked.'**
  String alert_block_user(String time);

  /// No description provided for @invalid_email.
  ///
  /// In en, this message translates to:
  /// **'Invalid email'**
  String get invalid_email;

  /// No description provided for @invalid_email_message.
  ///
  /// In en, this message translates to:
  /// **'Please enter an valid email to continue'**
  String get invalid_email_message;

  /// No description provided for @my_courses.
  ///
  /// In en, this message translates to:
  /// **'My Courses'**
  String get my_courses;

  /// No description provided for @learn.
  ///
  /// In en, this message translates to:
  /// **'Learn'**
  String get learn;

  /// No description provided for @my_learning_courses.
  ///
  /// In en, this message translates to:
  /// **'My Learning Courses'**
  String get my_learning_courses;

  /// No description provided for @wait_a_moment.
  ///
  /// In en, this message translates to:
  /// **'Please wait a moment...'**
  String get wait_a_moment;

  /// No description provided for @download_course.
  ///
  /// In en, this message translates to:
  /// **'Download Course'**
  String get download_course;

  /// No description provided for @remove_offline_course.
  ///
  /// In en, this message translates to:
  /// **'Remove Offline Course'**
  String get remove_offline_course;

  /// No description provided for @remove.
  ///
  /// In en, this message translates to:
  /// **'Remove'**
  String get remove;

  /// No description provided for @available_offline.
  ///
  /// In en, this message translates to:
  /// **'Available offline'**
  String get available_offline;

  /// No description provided for @process_take_few_minutes.
  ///
  /// In en, this message translates to:
  /// **'This process may take a few minutes.'**
  String get process_take_few_minutes;

  /// No description provided for @back_press_again_to_exit.
  ///
  /// In en, this message translates to:
  /// **'Back press again to exit'**
  String get back_press_again_to_exit;

  /// No description provided for @categories.
  ///
  /// In en, this message translates to:
  /// **'Categories'**
  String get categories;

  /// No description provided for @top_courses.
  ///
  /// In en, this message translates to:
  /// **'Top Courses'**
  String get top_courses;

  /// No description provided for @latest_courses.
  ///
  /// In en, this message translates to:
  /// **'Latest Courses'**
  String get latest_courses;

  /// No description provided for @no_course_found.
  ///
  /// In en, this message translates to:
  /// **'No course found'**
  String get no_course_found;

  /// No description provided for @not_yet_course.
  ///
  /// In en, this message translates to:
  /// **'You haven\'t enrolled in any courses'**
  String get not_yet_course;

  /// No description provided for @course_curriculum.
  ///
  /// In en, this message translates to:
  /// **'Course Curriculum'**
  String get course_curriculum;

  /// No description provided for @curriculum.
  ///
  /// In en, this message translates to:
  /// **'Curriculum'**
  String get curriculum;

  /// No description provided for @basic_knowledge.
  ///
  /// In en, this message translates to:
  /// **'Basic knowledge'**
  String get basic_knowledge;

  /// No description provided for @course_features.
  ///
  /// In en, this message translates to:
  /// **'Course Features'**
  String get course_features;

  /// No description provided for @course_instructor.
  ///
  /// In en, this message translates to:
  /// **'Course Instructor'**
  String get course_instructor;

  /// No description provided for @course_resource.
  ///
  /// In en, this message translates to:
  /// **'Course Resource'**
  String get course_resource;

  /// No description provided for @course_details.
  ///
  /// In en, this message translates to:
  /// **'Course Details'**
  String get course_details;

  /// No description provided for @course_review.
  ///
  /// In en, this message translates to:
  /// **'Course Review'**
  String get course_review;

  /// No description provided for @write_review.
  ///
  /// In en, this message translates to:
  /// **'Write Review'**
  String get write_review;

  /// No description provided for @related_course.
  ///
  /// In en, this message translates to:
  /// **'Related Course'**
  String get related_course;

  /// No description provided for @see_more.
  ///
  /// In en, this message translates to:
  /// **'See more'**
  String get see_more;

  /// No description provided for @read_less.
  ///
  /// In en, this message translates to:
  /// **'Read less'**
  String get read_less;

  /// No description provided for @organization.
  ///
  /// In en, this message translates to:
  /// **'Organization'**
  String get organization;

  /// No description provided for @faq.
  ///
  /// In en, this message translates to:
  /// **'FAQ'**
  String get faq;

  /// No description provided for @what_you_will_learn.
  ///
  /// In en, this message translates to:
  /// **'What you learn'**
  String get what_you_will_learn;

  /// No description provided for @what_do_you_want_to_learn.
  ///
  /// In en, this message translates to:
  /// **'What do you want to learn?'**
  String get what_do_you_want_to_learn;

  /// No description provided for @requirements.
  ///
  /// In en, this message translates to:
  /// **'Requirements'**
  String get requirements;

  /// No description provided for @description.
  ///
  /// In en, this message translates to:
  /// **'Description'**
  String get description;

  /// No description provided for @additional_information.
  ///
  /// In en, this message translates to:
  /// **'Additional information'**
  String get additional_information;

  /// No description provided for @frequently_ask_question.
  ///
  /// In en, this message translates to:
  /// **'Frequently Ask Question (FAQ)'**
  String get frequently_ask_question;

  /// No description provided for @featured_courses.
  ///
  /// In en, this message translates to:
  /// **'Featured courses'**
  String get featured_courses;

  /// No description provided for @information.
  ///
  /// In en, this message translates to:
  /// **'Information'**
  String get information;

  /// No description provided for @instructors.
  ///
  /// In en, this message translates to:
  /// **'Instructors'**
  String get instructors;

  /// No description provided for @offer_courses.
  ///
  /// In en, this message translates to:
  /// **'Offered courses'**
  String get offer_courses;

  /// No description provided for @suggested_courses.
  ///
  /// In en, this message translates to:
  /// **'Suggested Courses'**
  String get suggested_courses;

  /// No description provided for @explore_by_category.
  ///
  /// In en, this message translates to:
  /// **'Explore by category'**
  String get explore_by_category;

  /// No description provided for @explore_by_course_collection.
  ///
  /// In en, this message translates to:
  /// **'Explore by course collection'**
  String get explore_by_course_collection;

  /// No description provided for @free_course.
  ///
  /// In en, this message translates to:
  /// **'Free course'**
  String get free_course;

  /// No description provided for @free.
  ///
  /// In en, this message translates to:
  /// **'Free'**
  String get free;

  /// No description provided for @content.
  ///
  /// In en, this message translates to:
  /// **'Content'**
  String get content;

  /// No description provided for @comments.
  ///
  /// In en, this message translates to:
  /// **'Comments'**
  String get comments;

  /// No description provided for @progress.
  ///
  /// In en, this message translates to:
  /// **'Progress'**
  String get progress;

  /// No description provided for @progress_report.
  ///
  /// In en, this message translates to:
  /// **'Progress report'**
  String get progress_report;

  /// No description provided for @overview.
  ///
  /// In en, this message translates to:
  /// **'Overview'**
  String get overview;

  /// No description provided for @complete.
  ///
  /// In en, this message translates to:
  /// **'Complete'**
  String get complete;

  /// No description provided for @learners.
  ///
  /// In en, this message translates to:
  /// **'Learners'**
  String get learners;

  /// No description provided for @updated.
  ///
  /// In en, this message translates to:
  /// **'Updated'**
  String get updated;

  /// No description provided for @start_learning_with_free_courses.
  ///
  /// In en, this message translates to:
  /// **'Start learning with Free Courses'**
  String get start_learning_with_free_courses;

  /// No description provided for @explore_by_instructor.
  ///
  /// In en, this message translates to:
  /// **'Explore by instructor'**
  String get explore_by_instructor;

  /// No description provided for @explore_course.
  ///
  /// In en, this message translates to:
  /// **'Explore course'**
  String get explore_course;

  /// No description provided for @book_by_category.
  ///
  /// In en, this message translates to:
  /// **'Book by category'**
  String get book_by_category;

  /// No description provided for @my_course.
  ///
  /// In en, this message translates to:
  /// **'My course'**
  String get my_course;

  /// No description provided for @downloaded.
  ///
  /// In en, this message translates to:
  /// **'Downloaded'**
  String get downloaded;

  /// No description provided for @purchase_course.
  ///
  /// In en, this message translates to:
  /// **'Purchase course'**
  String get purchase_course;

  /// No description provided for @wishlist.
  ///
  /// In en, this message translates to:
  /// **'Wishlist'**
  String get wishlist;

  /// No description provided for @add_to_cart.
  ///
  /// In en, this message translates to:
  /// **'Add to cart'**
  String get add_to_cart;

  /// No description provided for @close.
  ///
  /// In en, this message translates to:
  /// **'Close'**
  String get close;

  /// No description provided for @explore.
  ///
  /// In en, this message translates to:
  /// **'Explore'**
  String get explore;

  /// No description provided for @more.
  ///
  /// In en, this message translates to:
  /// **'More'**
  String get more;

  /// No description provided for @account.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get account;

  /// No description provided for @your_account.
  ///
  /// In en, this message translates to:
  /// **'Your account'**
  String get your_account;

  /// No description provided for @download_certificates.
  ///
  /// In en, this message translates to:
  /// **'Download certificates'**
  String get download_certificates;

  /// No description provided for @your_certificates.
  ///
  /// In en, this message translates to:
  /// **'Your certificates'**
  String get your_certificates;

  /// No description provided for @are_you_sure_to_logout.
  ///
  /// In en, this message translates to:
  /// **'Are you sure want to logout'**
  String get are_you_sure_to_logout;

  /// No description provided for @your_account_remove_successfully.
  ///
  /// In en, this message translates to:
  /// **'Your account remove successfully'**
  String get your_account_remove_successfully;

  /// No description provided for @about_us.
  ///
  /// In en, this message translates to:
  /// **'About us'**
  String get about_us;

  /// No description provided for @about.
  ///
  /// In en, this message translates to:
  /// **'About'**
  String get about;

  /// No description provided for @terms_of_use.
  ///
  /// In en, this message translates to:
  /// **'Terms of use'**
  String get terms_of_use;

  /// No description provided for @privacy_policy.
  ///
  /// In en, this message translates to:
  /// **'Privacy policy'**
  String get privacy_policy;

  /// No description provided for @private_messages.
  ///
  /// In en, this message translates to:
  /// **'Private Messages'**
  String get private_messages;

  /// No description provided for @help_and_support.
  ///
  /// In en, this message translates to:
  /// **'Help and support'**
  String get help_and_support;

  /// No description provided for @logout.
  ///
  /// In en, this message translates to:
  /// **'Logout'**
  String get logout;

  /// No description provided for @log_out.
  ///
  /// In en, this message translates to:
  /// **'Log out'**
  String get log_out;

  /// No description provided for @sign_in.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get sign_in;

  /// No description provided for @help.
  ///
  /// In en, this message translates to:
  /// **'Help'**
  String get help;

  /// No description provided for @profile.
  ///
  /// In en, this message translates to:
  /// **'Profile'**
  String get profile;

  /// No description provided for @profile_and_security.
  ///
  /// In en, this message translates to:
  /// **'Profile & security'**
  String get profile_and_security;

  /// No description provided for @book_store.
  ///
  /// In en, this message translates to:
  /// **'Book store'**
  String get book_store;

  /// No description provided for @policy_policy.
  ///
  /// In en, this message translates to:
  /// **'Privacy policy'**
  String get policy_policy;

  /// No description provided for @language.
  ///
  /// In en, this message translates to:
  /// **'Language'**
  String get language;

  /// No description provided for @my_certificates.
  ///
  /// In en, this message translates to:
  /// **'My certificates'**
  String get my_certificates;

  /// No description provided for @follow.
  ///
  /// In en, this message translates to:
  /// **'Follow'**
  String get follow;

  /// No description provided for @unfollow.
  ///
  /// In en, this message translates to:
  /// **'Unfollow'**
  String get unfollow;

  /// No description provided for @message.
  ///
  /// In en, this message translates to:
  /// **'Message'**
  String get message;

  /// No description provided for @course.
  ///
  /// In en, this message translates to:
  /// **'Courses'**
  String get course;

  /// No description provided for @student.
  ///
  /// In en, this message translates to:
  /// **'Students'**
  String get student;

  /// No description provided for @follower.
  ///
  /// In en, this message translates to:
  /// **'Follower'**
  String get follower;

  /// No description provided for @appearance.
  ///
  /// In en, this message translates to:
  /// **'Appearance'**
  String get appearance;

  /// No description provided for @download.
  ///
  /// In en, this message translates to:
  /// **'Download'**
  String get download;

  /// No description provided for @books.
  ///
  /// In en, this message translates to:
  /// **'Books'**
  String get books;

  /// No description provided for @favorite.
  ///
  /// In en, this message translates to:
  /// **'Favorite'**
  String get favorite;

  /// No description provided for @notification.
  ///
  /// In en, this message translates to:
  /// **'Notification'**
  String get notification;

  /// No description provided for @orderHistory.
  ///
  /// In en, this message translates to:
  /// **'Order History'**
  String get orderHistory;

  /// No description provided for @voucher.
  ///
  /// In en, this message translates to:
  /// **'Voucher'**
  String get voucher;

  /// No description provided for @meeting.
  ///
  /// In en, this message translates to:
  /// **'Meeting'**
  String get meeting;

  /// No description provided for @changePassword.
  ///
  /// In en, this message translates to:
  /// **'Change Password'**
  String get changePassword;

  /// No description provided for @delete.
  ///
  /// In en, this message translates to:
  /// **'Delete'**
  String get delete;

  /// No description provided for @delete_account.
  ///
  /// In en, this message translates to:
  /// **'Delete account'**
  String get delete_account;

  /// No description provided for @otherBenefit.
  ///
  /// In en, this message translates to:
  /// **'Other Benefit'**
  String get otherBenefit;

  /// No description provided for @other_courses.
  ///
  /// In en, this message translates to:
  /// **'Other courses'**
  String get other_courses;

  /// No description provided for @instructorDetails.
  ///
  /// In en, this message translates to:
  /// **'Instructor Details'**
  String get instructorDetails;

  /// No description provided for @apply.
  ///
  /// In en, this message translates to:
  /// **'Apply'**
  String get apply;

  /// No description provided for @go_to_payment.
  ///
  /// In en, this message translates to:
  /// **'Go to Payment'**
  String get go_to_payment;

  /// No description provided for @total.
  ///
  /// In en, this message translates to:
  /// **'Total'**
  String get total;

  /// No description provided for @coupon.
  ///
  /// In en, this message translates to:
  /// **'Coupon'**
  String get coupon;

  /// No description provided for @discount.
  ///
  /// In en, this message translates to:
  /// **'Discount'**
  String get discount;

  /// No description provided for @sub_total.
  ///
  /// In en, this message translates to:
  /// **'Sub Total'**
  String get sub_total;

  /// No description provided for @apply_coupon.
  ///
  /// In en, this message translates to:
  /// **'Apply Coupon'**
  String get apply_coupon;

  /// No description provided for @cart_item.
  ///
  /// In en, this message translates to:
  /// **'Cart Item'**
  String get cart_item;

  /// No description provided for @complete_payment.
  ///
  /// In en, this message translates to:
  /// **'Complete Payment'**
  String get complete_payment;

  /// No description provided for @payment_get_way.
  ///
  /// In en, this message translates to:
  /// **'Payment GetWay'**
  String get payment_get_way;

  /// No description provided for @contact.
  ///
  /// In en, this message translates to:
  /// **'Contact'**
  String get contact;

  /// No description provided for @go_to_playlist.
  ///
  /// In en, this message translates to:
  /// **'Go to Playlist'**
  String get go_to_playlist;

  /// No description provided for @got_it.
  ///
  /// In en, this message translates to:
  /// **'Got it'**
  String get got_it;

  /// No description provided for @password_mismatch.
  ///
  /// In en, this message translates to:
  /// **'Passwords do not match'**
  String get password_mismatch;

  /// No description provided for @password_mismatch_message.
  ///
  /// In en, this message translates to:
  /// **'Your passwords do not match. Please try again.'**
  String get password_mismatch_message;

  /// No description provided for @all_books.
  ///
  /// In en, this message translates to:
  /// **'All Books'**
  String get all_books;

  /// No description provided for @reset_password.
  ///
  /// In en, this message translates to:
  /// **'Reset Password'**
  String get reset_password;

  /// No description provided for @new_password.
  ///
  /// In en, this message translates to:
  /// **'New Password'**
  String get new_password;

  /// No description provided for @create_a_new_password.
  ///
  /// In en, this message translates to:
  /// **'Create a new password'**
  String get create_a_new_password;

  /// No description provided for @set_new_password.
  ///
  /// In en, this message translates to:
  /// **'Set new password'**
  String get set_new_password;

  /// No description provided for @save.
  ///
  /// In en, this message translates to:
  /// **'Save'**
  String get save;

  /// No description provided for @order_history.
  ///
  /// In en, this message translates to:
  /// **'Order History'**
  String get order_history;

  /// No description provided for @conversation.
  ///
  /// In en, this message translates to:
  /// **'Conversation'**
  String get conversation;

  /// No description provided for @no_conversation_found.
  ///
  /// In en, this message translates to:
  /// **'No Conversation found'**
  String get no_conversation_found;

  /// No description provided for @no_news_found.
  ///
  /// In en, this message translates to:
  /// **'No news found'**
  String get no_news_found;

  /// No description provided for @copy.
  ///
  /// In en, this message translates to:
  /// **'Copy'**
  String get copy;

  /// No description provided for @did_not_receive_the_code.
  ///
  /// In en, this message translates to:
  /// **'Did not receive the code?'**
  String get did_not_receive_the_code;

  /// No description provided for @resend_code.
  ///
  /// In en, this message translates to:
  /// **'Resend code'**
  String get resend_code;

  /// No description provided for @enter_the_verification_code.
  ///
  /// In en, this message translates to:
  /// **'We have sent a 6 digital verification code on'**
  String get enter_the_verification_code;

  /// No description provided for @otp_verification.
  ///
  /// In en, this message translates to:
  /// **'Confirm your phone number'**
  String get otp_verification;

  /// No description provided for @email_otp_verification.
  ///
  /// In en, this message translates to:
  /// **'Confirm your email address'**
  String get email_otp_verification;

  /// No description provided for @confirm_email.
  ///
  /// In en, this message translates to:
  /// **'Confirm email'**
  String get confirm_email;

  /// No description provided for @send_code_too_much.
  ///
  /// In en, this message translates to:
  /// **'Too many code requests. Try again later or use another email.'**
  String get send_code_too_much;

  /// No description provided for @try_another_email.
  ///
  /// In en, this message translates to:
  /// **'Try again later or use another email.'**
  String get try_another_email;

  /// No description provided for @trending_of_this_month.
  ///
  /// In en, this message translates to:
  /// **'Trending Of This Month'**
  String get trending_of_this_month;

  /// No description provided for @view_all.
  ///
  /// In en, this message translates to:
  /// **'View All'**
  String get view_all;

  /// No description provided for @recent_published.
  ///
  /// In en, this message translates to:
  /// **'Recent Published'**
  String get recent_published;

  /// No description provided for @top_sales_books.
  ///
  /// In en, this message translates to:
  /// **'Top Sales Books'**
  String get top_sales_books;

  /// No description provided for @book_details.
  ///
  /// In en, this message translates to:
  /// **'Book Details'**
  String get book_details;

  /// No description provided for @related_book.
  ///
  /// In en, this message translates to:
  /// **'Related Book'**
  String get related_book;

  /// No description provided for @author.
  ///
  /// In en, this message translates to:
  /// **'Author'**
  String get author;

  /// No description provided for @yes.
  ///
  /// In en, this message translates to:
  /// **'Yes'**
  String get yes;

  /// No description provided for @no.
  ///
  /// In en, this message translates to:
  /// **'No'**
  String get no;

  /// No description provided for @summary.
  ///
  /// In en, this message translates to:
  /// **'Summary'**
  String get summary;

  /// No description provided for @invoice.
  ///
  /// In en, this message translates to:
  /// **'Invoice'**
  String get invoice;

  /// No description provided for @name.
  ///
  /// In en, this message translates to:
  /// **'Name'**
  String get name;

  /// No description provided for @email.
  ///
  /// In en, this message translates to:
  /// **'Email'**
  String get email;

  /// No description provided for @phone.
  ///
  /// In en, this message translates to:
  /// **'Phone'**
  String get phone;

  /// No description provided for @verify.
  ///
  /// In en, this message translates to:
  /// **'Verify'**
  String get verify;

  /// No description provided for @verify_your_email.
  ///
  /// In en, this message translates to:
  /// **'Verify your email'**
  String get verify_your_email;

  /// No description provided for @payment_type.
  ///
  /// In en, this message translates to:
  /// **'Payment Type'**
  String get payment_type;

  /// No description provided for @meeting_list.
  ///
  /// In en, this message translates to:
  /// **'Meeting List'**
  String get meeting_list;

  /// No description provided for @certificates.
  ///
  /// In en, this message translates to:
  /// **'Certificates'**
  String get certificates;

  /// No description provided for @gender.
  ///
  /// In en, this message translates to:
  /// **'Gender'**
  String get gender;

  /// No description provided for @select_gender.
  ///
  /// In en, this message translates to:
  /// **'Select Gender'**
  String get select_gender;

  /// No description provided for @update_profile.
  ///
  /// In en, this message translates to:
  /// **'Update Profile'**
  String get update_profile;

  /// No description provided for @date_of_birth.
  ///
  /// In en, this message translates to:
  /// **'Date of birth'**
  String get date_of_birth;

  /// No description provided for @address.
  ///
  /// In en, this message translates to:
  /// **'Address'**
  String get address;

  /// No description provided for @post_review.
  ///
  /// In en, this message translates to:
  /// **'Post Review'**
  String get post_review;

  /// No description provided for @write_something.
  ///
  /// In en, this message translates to:
  /// **'Write Something'**
  String get write_something;

  /// No description provided for @no_comments_yet.
  ///
  /// In en, this message translates to:
  /// **'No comments yet'**
  String get no_comments_yet;

  /// No description provided for @write_a_comment.
  ///
  /// In en, this message translates to:
  /// **'Write a comment'**
  String get write_a_comment;

  /// No description provided for @review.
  ///
  /// In en, this message translates to:
  /// **'Review'**
  String get review;

  /// No description provided for @rating.
  ///
  /// In en, this message translates to:
  /// **'Rating'**
  String get rating;

  /// No description provided for @logout_dialog.
  ///
  /// In en, this message translates to:
  /// **'Logout?'**
  String get logout_dialog;

  /// No description provided for @are_you_sure_logout_from_device.
  ///
  /// In en, this message translates to:
  /// **'Are you sure you want to logout from this device?'**
  String get are_you_sure_logout_from_device;

  /// No description provided for @lessons.
  ///
  /// In en, this message translates to:
  /// **'Lessons'**
  String get lessons;

  /// No description provided for @lessons_browsers.
  ///
  /// In en, this message translates to:
  /// **'Lessons browsers'**
  String get lessons_browsers;

  /// No description provided for @enroll.
  ///
  /// In en, this message translates to:
  /// **'Enroll'**
  String get enroll;

  /// No description provided for @type_here.
  ///
  /// In en, this message translates to:
  /// **'Type here'**
  String get type_here;

  /// No description provided for @dark_mode.
  ///
  /// In en, this message translates to:
  /// **'Dark mode'**
  String get dark_mode;

  /// No description provided for @something_wrong.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong'**
  String get something_wrong;

  /// No description provided for @setting.
  ///
  /// In en, this message translates to:
  /// **'Setting'**
  String get setting;

  /// No description provided for @settings.
  ///
  /// In en, this message translates to:
  /// **'Settings'**
  String get settings;

  /// No description provided for @transfer_domain.
  ///
  /// In en, this message translates to:
  /// **'Transfer Domain'**
  String get transfer_domain;

  /// No description provided for @total_course.
  ///
  /// In en, this message translates to:
  /// **'Total Courses'**
  String get total_course;

  /// No description provided for @expertise.
  ///
  /// In en, this message translates to:
  /// **'Expertise'**
  String get expertise;

  /// No description provided for @biography.
  ///
  /// In en, this message translates to:
  /// **'Biography'**
  String get biography;

  /// No description provided for @total_instructor.
  ///
  /// In en, this message translates to:
  /// **'Total Instructor'**
  String get total_instructor;

  /// No description provided for @total_students.
  ///
  /// In en, this message translates to:
  /// **'Total Students'**
  String get total_students;

  /// No description provided for @total_enrollments.
  ///
  /// In en, this message translates to:
  /// **'Total Enrollments'**
  String get total_enrollments;

  /// No description provided for @popular_books.
  ///
  /// In en, this message translates to:
  /// **'Popular Books'**
  String get popular_books;

  /// No description provided for @book_review.
  ///
  /// In en, this message translates to:
  /// **'Book Review'**
  String get book_review;

  /// No description provided for @search_item.
  ///
  /// In en, this message translates to:
  /// **'Search item'**
  String get search_item;

  /// No description provided for @expressions_for_discussion.
  ///
  /// In en, this message translates to:
  /// **'Expressions for Discussion'**
  String get expressions_for_discussion;

  /// No description provided for @high_rated_books.
  ///
  /// In en, this message translates to:
  /// **'High Rated Books'**
  String get high_rated_books;

  /// No description provided for @in_stock.
  ///
  /// In en, this message translates to:
  /// **'In Stock'**
  String get in_stock;

  /// No description provided for @show_more.
  ///
  /// In en, this message translates to:
  /// **'Show more'**
  String get show_more;

  /// No description provided for @show_less.
  ///
  /// In en, this message translates to:
  /// **'Show less'**
  String get show_less;

  /// No description provided for @title.
  ///
  /// In en, this message translates to:
  /// **'Title'**
  String get title;

  /// No description provided for @publisher.
  ///
  /// In en, this message translates to:
  /// **'Publisher'**
  String get publisher;

  /// No description provided for @edition.
  ///
  /// In en, this message translates to:
  /// **'Edition'**
  String get edition;

  /// No description provided for @page.
  ///
  /// In en, this message translates to:
  /// **'Page'**
  String get page;

  /// No description provided for @cancel.
  ///
  /// In en, this message translates to:
  /// **'Cancel'**
  String get cancel;

  /// No description provided for @country.
  ///
  /// In en, this message translates to:
  /// **'Country'**
  String get country;

  /// No description provided for @applied_coupon.
  ///
  /// In en, this message translates to:
  /// **'Coupon Applied'**
  String get applied_coupon;

  /// No description provided for @copied_to_clipboard.
  ///
  /// In en, this message translates to:
  /// **'Copied to clipboard'**
  String get copied_to_clipboard;

  /// No description provided for @are_you_sure_to_delete.
  ///
  /// In en, this message translates to:
  /// **'Are you sure want to delete account'**
  String get are_you_sure_to_delete;

  /// No description provided for @type_a_message.
  ///
  /// In en, this message translates to:
  /// **'Type a message'**
  String get type_a_message;

  /// No description provided for @search_course.
  ///
  /// In en, this message translates to:
  /// **'Search course'**
  String get search_course;

  /// No description provided for @courses_found.
  ///
  /// In en, this message translates to:
  /// **'Courses found'**
  String get courses_found;

  /// No description provided for @course_category.
  ///
  /// In en, this message translates to:
  /// **'Course Category'**
  String get course_category;

  /// No description provided for @course_collection.
  ///
  /// In en, this message translates to:
  /// **'Course Collection'**
  String get course_collection;

  /// No description provided for @category.
  ///
  /// In en, this message translates to:
  /// **'Category'**
  String get category;

  /// No description provided for @no_category_found.
  ///
  /// In en, this message translates to:
  /// **'No category found'**
  String get no_category_found;

  /// No description provided for @back_to_homepage.
  ///
  /// In en, this message translates to:
  /// **'Back To Homepage'**
  String get back_to_homepage;

  /// No description provided for @back.
  ///
  /// In en, this message translates to:
  /// **'Back'**
  String get back;

  /// No description provided for @quiz.
  ///
  /// In en, this message translates to:
  /// **'Quiz'**
  String get quiz;

  /// No description provided for @quiz_in_course.
  ///
  /// In en, this message translates to:
  /// **'Quiz in course'**
  String get quiz_in_course;

  /// No description provided for @quiz_in_chapter.
  ///
  /// In en, this message translates to:
  /// **'Quiz in chapters'**
  String get quiz_in_chapter;

  /// No description provided for @this_chapter_has_no_quiz.
  ///
  /// In en, this message translates to:
  /// **'This chapter has no quiz'**
  String get this_chapter_has_no_quiz;

  /// No description provided for @chapter.
  ///
  /// In en, this message translates to:
  /// **'Chapter'**
  String get chapter;

  /// No description provided for @results.
  ///
  /// In en, this message translates to:
  /// **'Results'**
  String get results;

  /// No description provided for @start_quiz.
  ///
  /// In en, this message translates to:
  /// **'Start Quiz'**
  String get start_quiz;

  /// No description provided for @your_time.
  ///
  /// In en, this message translates to:
  /// **'Your Time'**
  String get your_time;

  /// No description provided for @your_score.
  ///
  /// In en, this message translates to:
  /// **'Your Score'**
  String get your_score;

  /// No description provided for @your_result.
  ///
  /// In en, this message translates to:
  /// **'Your Result'**
  String get your_result;

  /// No description provided for @your_profile_update_successfully.
  ///
  /// In en, this message translates to:
  /// **'Your profile update successfully'**
  String get your_profile_update_successfully;

  /// No description provided for @total_score.
  ///
  /// In en, this message translates to:
  /// **'Total Score'**
  String get total_score;

  /// No description provided for @continue_course.
  ///
  /// In en, this message translates to:
  /// **'Continue Course'**
  String get continue_course;

  /// No description provided for @passed.
  ///
  /// In en, this message translates to:
  /// **'Passed'**
  String get passed;

  /// No description provided for @failed.
  ///
  /// In en, this message translates to:
  /// **'failed'**
  String get failed;

  /// No description provided for @write_your_ans.
  ///
  /// In en, this message translates to:
  /// **'Write your answer'**
  String get write_your_ans;

  /// No description provided for @added_to_cart.
  ///
  /// In en, this message translates to:
  /// **'added to cart'**
  String get added_to_cart;

  /// No description provided for @audio_file_error.
  ///
  /// In en, this message translates to:
  /// **'Can\'t play this audio'**
  String get audio_file_error;

  /// No description provided for @failed_to_download.
  ///
  /// In en, this message translates to:
  /// **'File error. Can\'t download this file.'**
  String get failed_to_download;

  /// No description provided for @change_email.
  ///
  /// In en, this message translates to:
  /// **'Change email'**
  String get change_email;

  /// No description provided for @change_your_detail.
  ///
  /// In en, this message translates to:
  /// **'Change your detail'**
  String get change_your_detail;

  /// No description provided for @change_your_password.
  ///
  /// In en, this message translates to:
  /// **'Change your password'**
  String get change_your_password;

  /// No description provided for @current_password.
  ///
  /// In en, this message translates to:
  /// **'Current Password'**
  String get current_password;

  /// No description provided for @choose_password.
  ///
  /// In en, this message translates to:
  /// **'Choose Password'**
  String get choose_password;

  /// No description provided for @enter_your_email.
  ///
  /// In en, this message translates to:
  /// **'Enter your email'**
  String get enter_your_email;

  /// No description provided for @enter_your_password.
  ///
  /// In en, this message translates to:
  /// **'Enter your password'**
  String get enter_your_password;

  /// No description provided for @enter_your_new_password.
  ///
  /// In en, this message translates to:
  /// **'Enter your password'**
  String get enter_your_new_password;

  /// No description provided for @confirm_your_password.
  ///
  /// In en, this message translates to:
  /// **'Confirm your password'**
  String get confirm_your_password;

  /// No description provided for @enter_your_user_name.
  ///
  /// In en, this message translates to:
  /// **'Enter your user name'**
  String get enter_your_user_name;

  /// No description provided for @password_should_be.
  ///
  /// In en, this message translates to:
  /// **'Password should be include 8 characters'**
  String get password_should_be;

  /// No description provided for @save_and_change.
  ///
  /// In en, this message translates to:
  /// **'Save & Change'**
  String get save_and_change;

  /// No description provided for @view_cart.
  ///
  /// In en, this message translates to:
  /// **'View Cart'**
  String get view_cart;

  /// No description provided for @no_notification.
  ///
  /// In en, this message translates to:
  /// **'No notification for you'**
  String get no_notification;

  /// No description provided for @empty_wishlist.
  ///
  /// In en, this message translates to:
  /// **'Your wishlist is empty.'**
  String get empty_wishlist;

  /// No description provided for @login_to_add_this_to_cart.
  ///
  /// In en, this message translates to:
  /// **'Login first to add this course to your cart.'**
  String get login_to_add_this_to_cart;

  /// No description provided for @enroll_now.
  ///
  /// In en, this message translates to:
  /// **'Enroll Now'**
  String get enroll_now;

  /// No description provided for @view_details.
  ///
  /// In en, this message translates to:
  /// **'View Details'**
  String get view_details;

  /// No description provided for @total_mark.
  ///
  /// In en, this message translates to:
  /// **'Total Mark'**
  String get total_mark;

  /// No description provided for @pass_mark.
  ///
  /// In en, this message translates to:
  /// **'Pass Mark'**
  String get pass_mark;

  /// No description provided for @pick_file.
  ///
  /// In en, this message translates to:
  /// **'Pick File'**
  String get pick_file;

  /// No description provided for @review_added_successfully.
  ///
  /// In en, this message translates to:
  /// **'Review Added Successfully'**
  String get review_added_successfully;

  /// No description provided for @no_resource_available_yet.
  ///
  /// In en, this message translates to:
  /// **'No Resource Available Yet'**
  String get no_resource_available_yet;

  /// No description provided for @payment_gateway.
  ///
  /// In en, this message translates to:
  /// **'Payment Gateway'**
  String get payment_gateway;

  /// No description provided for @start_learning.
  ///
  /// In en, this message translates to:
  /// **'Start Learning'**
  String get start_learning;

  /// No description provided for @start_learning_successful.
  ///
  /// In en, this message translates to:
  /// **'Start learning successful'**
  String get start_learning_successful;

  /// No description provided for @no_certificate_found.
  ///
  /// In en, this message translates to:
  /// **'No Certificate Found'**
  String get no_certificate_found;

  /// No description provided for @wishlist_added_successfully.
  ///
  /// In en, this message translates to:
  /// **'Wishlist Added Successfully'**
  String get wishlist_added_successfully;

  /// No description provided for @wishlist_removed_successfully.
  ///
  /// In en, this message translates to:
  /// **'Wishlist Removed Successfully'**
  String get wishlist_removed_successfully;

  /// No description provided for @empty_cart.
  ///
  /// In en, this message translates to:
  /// **'Your cart is empty.'**
  String get empty_cart;

  /// No description provided for @you_dont_have_any_meeting_yet.
  ///
  /// In en, this message translates to:
  /// **'You Don\'t Have Any Meeting Yet'**
  String get you_dont_have_any_meeting_yet;

  /// No description provided for @no_favorites.
  ///
  /// In en, this message translates to:
  /// **'No Favorites'**
  String get no_favorites;

  /// No description provided for @you_have_not_liked_any_items_yet.
  ///
  /// In en, this message translates to:
  /// **'You Have Not Liked Any Items Yet'**
  String get you_have_not_liked_any_items_yet;

  /// No description provided for @no_notifications_yet.
  ///
  /// In en, this message translates to:
  /// **'No notifications yet'**
  String get no_notifications_yet;

  /// No description provided for @you_have_no_notifications.
  ///
  /// In en, this message translates to:
  /// **'You have no notifications right now'**
  String get you_have_no_notifications;

  /// No description provided for @no_result_found.
  ///
  /// In en, this message translates to:
  /// **'No Result Found'**
  String get no_result_found;

  /// No description provided for @please_try_again_with_another_keywords.
  ///
  /// In en, this message translates to:
  /// **'Please try again with another keywords\nor maybe use generic term'**
  String get please_try_again_with_another_keywords;

  /// No description provided for @you_have_not_added_anything.
  ///
  /// In en, this message translates to:
  /// **'You have not added anything to your cart.\nGo ahead & explore top categories'**
  String get you_have_not_added_anything;

  /// No description provided for @profile_updated_successfully.
  ///
  /// In en, this message translates to:
  /// **'Profile Update Successfully'**
  String get profile_updated_successfully;

  /// No description provided for @submitted_successful.
  ///
  /// In en, this message translates to:
  /// **'Submitted Successfully'**
  String get submitted_successful;

  /// No description provided for @change_theme.
  ///
  /// In en, this message translates to:
  /// **'Change Theme'**
  String get change_theme;

  /// No description provided for @change_language.
  ///
  /// In en, this message translates to:
  /// **'Change Language'**
  String get change_language;

  /// No description provided for @resource.
  ///
  /// In en, this message translates to:
  /// **'Resource'**
  String get resource;

  /// No description provided for @light_mode.
  ///
  /// In en, this message translates to:
  /// **'Light Mode'**
  String get light_mode;

  /// No description provided for @user_name.
  ///
  /// In en, this message translates to:
  /// **'Username'**
  String get user_name;

  /// No description provided for @online_mode.
  ///
  /// In en, this message translates to:
  /// **'Online Mode'**
  String get online_mode;

  /// No description provided for @offline_mode.
  ///
  /// In en, this message translates to:
  /// **'Offline Mode'**
  String get offline_mode;

  /// No description provided for @tap_to_online.
  ///
  /// In en, this message translates to:
  /// **'Tap to Online'**
  String get tap_to_online;

  /// No description provided for @tap_to_offline.
  ///
  /// In en, this message translates to:
  /// **'Tap to Offline'**
  String get tap_to_offline;

  /// No description provided for @question_count.
  ///
  /// In en, this message translates to:
  /// **'Question count'**
  String get question_count;

  /// No description provided for @forum.
  ///
  /// In en, this message translates to:
  /// **'Forum'**
  String get forum;

  /// No description provided for @no_forums_found.
  ///
  /// In en, this message translates to:
  /// **'No forums found'**
  String get no_forums_found;

  /// No description provided for @social.
  ///
  /// In en, this message translates to:
  /// **'Social'**
  String get social;

  /// No description provided for @group.
  ///
  /// In en, this message translates to:
  /// **'Group'**
  String get group;

  /// No description provided for @switch_domain.
  ///
  /// In en, this message translates to:
  /// **'Switch Domain'**
  String get switch_domain;

  /// No description provided for @switch_domain_message.
  ///
  /// In en, this message translates to:
  /// **'This feature lets partners use their own domain. Verification may take a few minutes.'**
  String get switch_domain_message;

  /// No description provided for @prompt_update_profile_message.
  ///
  /// In en, this message translates to:
  /// **'Update your profile to enjoy a better experience.'**
  String get prompt_update_profile_message;

  /// No description provided for @ascending.
  ///
  /// In en, this message translates to:
  /// **'Ascending'**
  String get ascending;

  /// No description provided for @can_not_launch.
  ///
  /// In en, this message translates to:
  /// **'Cannot launch'**
  String get can_not_launch;

  /// No description provided for @clear_all.
  ///
  /// In en, this message translates to:
  /// **'Clear all'**
  String get clear_all;

  /// No description provided for @connection_to_api_server_failed.
  ///
  /// In en, this message translates to:
  /// **'Connection to server failed. Please try again.'**
  String get connection_to_api_server_failed;

  /// No description provided for @courses.
  ///
  /// In en, this message translates to:
  /// **'Courses'**
  String get courses;

  /// No description provided for @deadline.
  ///
  /// In en, this message translates to:
  /// **'Deadline'**
  String get deadline;

  /// No description provided for @deleteYourAccount.
  ///
  /// In en, this message translates to:
  /// **'Delete your account'**
  String get deleteYourAccount;

  /// No description provided for @descending.
  ///
  /// In en, this message translates to:
  /// **'Descending'**
  String get descending;

  /// No description provided for @discovery.
  ///
  /// In en, this message translates to:
  /// **'Discovery'**
  String get discovery;

  /// No description provided for @empty_notifications.
  ///
  /// In en, this message translates to:
  /// **'No notifications'**
  String get empty_notifications;

  /// No description provided for @enter_valid_information.
  ///
  /// In en, this message translates to:
  /// **'Please enter valid information'**
  String get enter_valid_information;

  /// No description provided for @enter_your_first_name.
  ///
  /// In en, this message translates to:
  /// **'Enter your first name'**
  String get enter_your_first_name;

  /// No description provided for @enter_your_last_name.
  ///
  /// In en, this message translates to:
  /// **'Enter your last name'**
  String get enter_your_last_name;

  /// No description provided for @hours.
  ///
  /// In en, this message translates to:
  /// **'Hours'**
  String get hours;

  /// No description provided for @join.
  ///
  /// In en, this message translates to:
  /// **'Join'**
  String get join;

  /// No description provided for @legal.
  ///
  /// In en, this message translates to:
  /// **'Legal'**
  String get legal;

  /// No description provided for @login_to_continue.
  ///
  /// In en, this message translates to:
  /// **'Login to continue'**
  String get login_to_continue;

  /// No description provided for @membership.
  ///
  /// In en, this message translates to:
  /// **'Membership'**
  String get membership;

  /// No description provided for @news.
  ///
  /// In en, this message translates to:
  /// **'News'**
  String get news;

  /// No description provided for @no_data_found.
  ///
  /// In en, this message translates to:
  /// **'No data found'**
  String get no_data_found;

  /// No description provided for @page_not_found.
  ///
  /// In en, this message translates to:
  /// **'Page not found'**
  String get page_not_found;

  /// No description provided for @quizzes.
  ///
  /// In en, this message translates to:
  /// **'Quizzes'**
  String get quizzes;

  /// No description provided for @resend_it.
  ///
  /// In en, this message translates to:
  /// **'Resend'**
  String get resend_it;

  /// No description provided for @see_all.
  ///
  /// In en, this message translates to:
  /// **'See all'**
  String get see_all;

  /// No description provided for @search.
  ///
  /// In en, this message translates to:
  /// **'Search'**
  String get search;

  /// No description provided for @skip.
  ///
  /// In en, this message translates to:
  /// **'Skip'**
  String get skip;

  /// No description provided for @sorry.
  ///
  /// In en, this message translates to:
  /// **'Sorry'**
  String get sorry;

  /// No description provided for @sorry_your_order_history_is_Empty.
  ///
  /// In en, this message translates to:
  /// **'Sorry, your order history is empty'**
  String get sorry_your_order_history_is_Empty;

  /// No description provided for @subject.
  ///
  /// In en, this message translates to:
  /// **'Subject'**
  String get subject;

  /// No description provided for @subscribed_successfully.
  ///
  /// In en, this message translates to:
  /// **'Subscribed successfully'**
  String get subscribed_successfully;

  /// No description provided for @suggestions.
  ///
  /// In en, this message translates to:
  /// **'Suggestions'**
  String get suggestions;

  /// No description provided for @update_is_available.
  ///
  /// In en, this message translates to:
  /// **'Update is available'**
  String get update_is_available;

  /// No description provided for @update_now.
  ///
  /// In en, this message translates to:
  /// **'Update now'**
  String get update_now;

  /// No description provided for @we_are_under_maintenance.
  ///
  /// In en, this message translates to:
  /// **'We are under maintenance'**
  String get we_are_under_maintenance;

  /// No description provided for @we_will_be_right_back.
  ///
  /// In en, this message translates to:
  /// **'We will be right back'**
  String get we_will_be_right_back;

  /// No description provided for @you_are_not_logged_in.
  ///
  /// In en, this message translates to:
  /// **'You are not logged in'**
  String get you_are_not_logged_in;

  /// No description provided for @you_dont_have_any_purchased_course.
  ///
  /// In en, this message translates to:
  /// **'You don\'t have any purchased course'**
  String get you_dont_have_any_purchased_course;

  /// No description provided for @your_app_needs_to_update.
  ///
  /// In en, this message translates to:
  /// **'Your app needs to update'**
  String get your_app_needs_to_update;

  /// No description provided for @your_cart_is_empty.
  ///
  /// In en, this message translates to:
  /// **'Your cart is empty'**
  String get your_cart_is_empty;

  /// No description provided for @trying_to_connect.
  ///
  /// In en, this message translates to:
  /// **'Trying to connect...'**
  String get trying_to_connect;

  /// No description provided for @store_available.
  ///
  /// In en, this message translates to:
  /// **'The store is available.'**
  String get store_available;

  /// No description provided for @store_unavailable.
  ///
  /// In en, this message translates to:
  /// **'The store is unavailable.'**
  String get store_unavailable;

  /// No description provided for @not_connected.
  ///
  /// In en, this message translates to:
  /// **'Not connected'**
  String get not_connected;

  /// No description provided for @unable_to_connect_payments.
  ///
  /// In en, this message translates to:
  /// **'Unable to connect to the payments processor. Has this app been configured correctly? See the example README for instructions.'**
  String get unable_to_connect_payments;

  /// No description provided for @restore_purchase.
  ///
  /// In en, this message translates to:
  /// **'Restore purchase'**
  String get restore_purchase;

  /// No description provided for @make_payment.
  ///
  /// In en, this message translates to:
  /// **'Make Payment'**
  String get make_payment;

  /// No description provided for @no_internet_connection.
  ///
  /// In en, this message translates to:
  /// **'No internet connection'**
  String get no_internet_connection;

  /// No description provided for @cant_open_link.
  ///
  /// In en, this message translates to:
  /// **'Can\'t open link.'**
  String get cant_open_link;

  /// No description provided for @error_occurred.
  ///
  /// In en, this message translates to:
  /// **'Error: {message}'**
  String error_occurred(String message);

  /// No description provided for @by_sign_up_agree.
  ///
  /// In en, this message translates to:
  /// **'By signing up, you agree to our '**
  String get by_sign_up_agree;

  /// No description provided for @and_text.
  ///
  /// In en, this message translates to:
  /// **'and'**
  String get and_text;

  /// No description provided for @registration_successful.
  ///
  /// In en, this message translates to:
  /// **'Registration successful'**
  String get registration_successful;

  /// No description provided for @set_new_password_success.
  ///
  /// In en, this message translates to:
  /// **'Set new password successfully'**
  String get set_new_password_success;

  /// No description provided for @change_password_success.
  ///
  /// In en, this message translates to:
  /// **'Changed password successfully, please login again'**
  String get change_password_success;

  /// No description provided for @otp_expired.
  ///
  /// In en, this message translates to:
  /// **'Your OTP has expired.'**
  String get otp_expired;

  /// No description provided for @attempts_left.
  ///
  /// In en, this message translates to:
  /// **'You have {count} attempts left.'**
  String attempts_left(int count);

  /// No description provided for @too_many_attempts.
  ///
  /// In en, this message translates to:
  /// **'Incorrect OTP. Too many attempts. Try another email.'**
  String get too_many_attempts;

  /// No description provided for @password_missing.
  ///
  /// In en, this message translates to:
  /// **'Your current password is missing'**
  String get password_missing;

  /// No description provided for @check_out.
  ///
  /// In en, this message translates to:
  /// **'Check Out'**
  String get check_out;

  /// No description provided for @connected_to_internet.
  ///
  /// In en, this message translates to:
  /// **'You are connected to the internet'**
  String get connected_to_internet;

  /// No description provided for @grid_view_pagination.
  ///
  /// In en, this message translates to:
  /// **'Grid View'**
  String get grid_view_pagination;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['en', 'vi'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'en':
      return AppLocalizationsEn();
    case 'vi':
      return AppLocalizationsVi();
  }

  throw FlutterError(
      'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
      'an issue with the localizations generation tool. Please file an issue '
      'on GitHub with a reproducible sample app and the gen-l10n configuration '
      'that was used.');
}
