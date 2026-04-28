// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Vietnamese (`vi`).
class AppLocalizationsVi extends AppLocalizations {
  AppLocalizationsVi([String locale = 'vi']) : super(locale);

  @override
  String get app_name => 'Faculty';

  @override
  String get intro_screen_text_1 =>
      'Mở khóa sự nghiệp của bạn với ứng dụng Faculty';

  @override
  String get intro_screen_text_2 => 'Chuẩn bị nhập học không rắc rối';

  @override
  String get intro_screen_text_3 =>
      'Tham gia các lớp học về cuộc sống hàng ngày';

  @override
  String get intro_screen_subtitle_1 => 'Nâng cao kỹ năng của bạn';

  @override
  String get intro_screen_subtitle_2 => 'trong lòng bàn tay của bạn';

  @override
  String get intro_screen_subtitle_3 => 'và giữ cho mình bận rộn';

  @override
  String get home => 'Trang chủ';

  @override
  String get bestseller => 'Bestseller';

  @override
  String get login => 'Đăng nhập';

  @override
  String get forgot_password => 'Quên mật khẩu?';

  @override
  String get email_address => 'Địa chỉ email';

  @override
  String get password => 'Mật khẩu';

  @override
  String get confirm_password => 'Xác nhận mật khẩu';

  @override
  String get sign_in_with_phone => 'Đăng nhập bằng điện thoại';

  @override
  String get sign_up_with_phone => 'Đăng ký bằng điện thoại';

  @override
  String get new_user => 'Người dùng mới?';

  @override
  String get account_exists => 'Tài khoản đã tồn tại';

  @override
  String get account_exists_message =>
      'Một tài khoản với email này đã tồn tại. Vui lòng sử dụng email khác hoặc đăng nhập';

  @override
  String get account_not_found => 'Không tìm thấy tài khoản';

  @override
  String get account_not_found_message =>
      'Chúng tôi không tìm thấy tài khoản với địa chỉ email này. Vui lòng đảm bảo bạn đã nhập đúng email.';

  @override
  String get account_block => 'Tài khoản bị khóa';

  @override
  String get account_block_message =>
      'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ.';

  @override
  String get too_many_fail_attempts =>
      'Quá nhiều lần thử thất bại. Tài khoản của bạn đã bị khóa, vui lòng liên hệ quản trị viên';

  @override
  String get create_an_account => 'Tạo tài khoản';

  @override
  String get or_continue_with => 'Hoặc tiếp tục với';

  @override
  String get continueAction => 'Tiếp tục';

  @override
  String get sign_up => 'Đăng ký';

  @override
  String get full_name => 'Họ và tên';

  @override
  String get first_name => 'Tên';

  @override
  String get last_name => 'Họ';

  @override
  String get sign_in_here => 'Đăng nhập tại đây';

  @override
  String get already_have_an_account => 'Đã có tài khoản?';

  @override
  String get phone_number => 'Số điện thoại';

  @override
  String get submit => 'Gửi';

  @override
  String get continue_as_a_guest => 'Tiếp tục với tư cách là Khách';

  @override
  String get next => 'Tiếp theo';

  @override
  String get get_started => 'Bắt đầu';

  @override
  String get back_to_login => 'Quay lại đăng nhập';

  @override
  String get incorrect_otp => 'OTP không chính xác.';

  @override
  String alert_block_user(String time) {
    return 'OTP không chính xác. Còn $time lần thử trước khi tài khoản bị khóa.';
  }

  @override
  String get invalid_email => 'Email không hợp lệ';

  @override
  String get invalid_email_message => 'Vui lòng nhập email hợp lệ để tiếp tục';

  @override
  String get my_courses => 'Các khóa học của tôi';

  @override
  String get learn => 'Học tập';

  @override
  String get my_learning_courses => 'Các khóa đang học của tôi';

  @override
  String get wait_a_moment => 'Vui lòng đợi một chút...';

  @override
  String get download_course => 'Tải xuống khóa học';

  @override
  String get remove_offline_course => 'Xóa khóa học ngoại tuyến';

  @override
  String get remove => 'Xóa';

  @override
  String get available_offline => 'Có sẵn ngoại tuyến';

  @override
  String get process_take_few_minutes => 'Quá trình này có thể mất vài phút.';

  @override
  String get back_press_again_to_exit => 'Nhấn nút quay lại lần nữa để thoát';

  @override
  String get categories => 'Các danh mục';

  @override
  String get top_courses => 'Các khóa học hàng đầu';

  @override
  String get latest_courses => 'Các khóa học mới nhất';

  @override
  String get no_course_found => 'Không có khóa học đã tìm thấy';

  @override
  String get not_yet_course => 'Bạn chưa đăng ký khóa học';

  @override
  String get course_curriculum => 'Giáo trình khóa học';

  @override
  String get curriculum => 'Giáo trình khóa học';

  @override
  String get basic_knowledge => 'Kiến thức cơ bản';

  @override
  String get course_features => 'Tính năng khóa học';

  @override
  String get course_instructor => 'Giảng viên khóa học';

  @override
  String get course_resource => 'Tài nguyên khóa học';

  @override
  String get course_details => 'Chi tiết khóa học';

  @override
  String get course_review => 'Đánh giá khóa học';

  @override
  String get write_review => 'Viết đánh giá';

  @override
  String get related_course => 'Khóa học liên quan';

  @override
  String get see_more => 'Xem thêm';

  @override
  String get read_less => 'Đọc ít hơn';

  @override
  String get organization => 'Tổ chức';

  @override
  String get faq => 'Câu hỏi thường gặp';

  @override
  String get what_you_will_learn => 'Những gì bạn sẽ học';

  @override
  String get what_do_you_want_to_learn => 'Bạn muốn học gì?';

  @override
  String get requirements => 'Yêu cầu';

  @override
  String get description => 'Mô tả';

  @override
  String get additional_information => 'Các thông tin thêm';

  @override
  String get frequently_ask_question => 'Câu hỏi thường gặp (FAQ)';

  @override
  String get featured_courses => 'Các khóa học nổi bật';

  @override
  String get information => 'Thông tin';

  @override
  String get instructors => 'Giảng viên';

  @override
  String get offer_courses => 'Các khóa học được cung cấp';

  @override
  String get suggested_courses => 'Các khóa học được đề xuất';

  @override
  String get explore_by_category => 'Khám phá theo danh mục';

  @override
  String get explore_by_course_collection =>
      'Khám phá theo bộ sưu tập khóa học';

  @override
  String get free_course => 'Khóa học miễn phí';

  @override
  String get free => 'Miễn phí';

  @override
  String get content => 'Nội dung học';

  @override
  String get comments => 'Bình luận';

  @override
  String get progress => 'Quá trình';

  @override
  String get progress_report => 'Tóm tắt quá trình học';

  @override
  String get overview => 'Tổng quan';

  @override
  String get complete => 'Hoàn thành';

  @override
  String get learners => 'Số người học';

  @override
  String get updated => 'Ngày cập nhật';

  @override
  String get start_learning_with_free_courses =>
      'Bắt đầu học với các khóa học miễn phí';

  @override
  String get explore_by_instructor => 'Khám phá theo giảng viên';

  @override
  String get explore_course => 'Khám phá khóa học';

  @override
  String get book_by_category => 'Sách theo danh mục';

  @override
  String get my_course => 'Khóa học của tôi';

  @override
  String get downloaded => 'Đã tải xuống';

  @override
  String get purchase_course => 'Mua khóa học';

  @override
  String get wishlist => 'Danh sách mong muốn';

  @override
  String get add_to_cart => 'Thêm vào giỏ hàng';

  @override
  String get close => 'Đóng';

  @override
  String get explore => 'Khám phá';

  @override
  String get more => 'Thêm';

  @override
  String get account => 'Tài khoản';

  @override
  String get your_account => 'Thông tin tài khoản';

  @override
  String get download_certificates => 'Tải xuống các chứng chỉ';

  @override
  String get your_certificates => 'Các chứng chỉ của tui';

  @override
  String get are_you_sure_to_logout => 'Bạn có chắc chắn muốn đăng xuất không';

  @override
  String get your_account_remove_successfully =>
      'Tài khoản của bạn đã được xóa thành công';

  @override
  String get about_us => 'Giới thiệu về chúng tôi';

  @override
  String get about => 'Giới thiệu';

  @override
  String get terms_of_use => 'Điều khoản sử dụng';

  @override
  String get privacy_policy => 'Chính sách bảo mật';

  @override
  String get private_messages => 'Tin nhắn riêng';

  @override
  String get help_and_support => 'Trợ giúp và hỗ trợ';

  @override
  String get logout => 'Đăng xuất';

  @override
  String get log_out => 'Đăng xuất';

  @override
  String get sign_in => 'Đăng nhập';

  @override
  String get help => 'Hỗ trợ';

  @override
  String get profile => 'Hồ sơ';

  @override
  String get profile_and_security => 'Hồ sơ & bảo mật';

  @override
  String get book_store => 'Cửa hàng sách';

  @override
  String get policy_policy => 'Chính sách bảo mật';

  @override
  String get language => 'Ngôn ngữ';

  @override
  String get my_certificates => 'Chứng chỉ của tôi';

  @override
  String get follow => 'Theo dõi';

  @override
  String get unfollow => 'Bỏ theo dõi';

  @override
  String get message => 'Tin nhắn';

  @override
  String get course => 'Các khóa học';

  @override
  String get student => 'Sinh viên';

  @override
  String get follower => 'Người theo dõi';

  @override
  String get appearance => 'Giao diện';

  @override
  String get download => 'Tải xuống';

  @override
  String get books => 'Sách';

  @override
  String get favorite => 'Yêu thích';

  @override
  String get notification => 'Thông báo';

  @override
  String get orderHistory => 'Lịch sử đơn hàng';

  @override
  String get voucher => 'Phiếu mua hàng';

  @override
  String get meeting => 'Cuộc họp';

  @override
  String get changePassword => 'Đổi mật khẩu';

  @override
  String get delete => 'Xóa';

  @override
  String get delete_account => 'Xóa tài khoản';

  @override
  String get otherBenefit => 'Lợi ích khác';

  @override
  String get other_courses => 'Các khóa học khác';

  @override
  String get instructorDetails => 'Chi tiết về giảng viên';

  @override
  String get apply => 'Áp dụng';

  @override
  String get go_to_payment => 'Đi đến mục Thanh toán';

  @override
  String get total => 'Tổng cộng';

  @override
  String get coupon => 'Phiếu giảm giá';

  @override
  String get discount => 'Giảm giá';

  @override
  String get sub_total => 'Tổng phụ';

  @override
  String get apply_coupon => 'Áp dụng Phiếu giảm giá';

  @override
  String get cart_item => 'Mặt hàng trong giỏ hàng';

  @override
  String get complete_payment => 'Hoàn tất thanh toán';

  @override
  String get payment_get_way => 'Thanh toán GetWay';

  @override
  String get contact => 'Liên hệ';

  @override
  String get go_to_playlist => 'Đi đến danh sách phát';

  @override
  String get got_it => 'Đã hiểu';

  @override
  String get password_mismatch => 'Xác nhận mật khẩu không khớp';

  @override
  String get password_mismatch_message =>
      'Mật khẩu không khớp. Vui lòng thử lại.';

  @override
  String get all_books => 'Tất cả sách';

  @override
  String get reset_password => 'Đặt lại mật khẩu';

  @override
  String get new_password => 'Mật khẩu mới';

  @override
  String get create_a_new_password => 'Tạo mật khẩu mới';

  @override
  String get set_new_password => 'Đặt mật khẩu mới';

  @override
  String get save => 'Lưu';

  @override
  String get order_history => 'Lịch sử đơn hàng';

  @override
  String get conversation => 'Cuộc trò chuyện';

  @override
  String get no_conversation_found => 'Không tìm thấy cuộc trò chuyện nào';

  @override
  String get no_news_found => 'Không tìm thấy tin tức';

  @override
  String get copy => 'Sao chép';

  @override
  String get did_not_receive_the_code => 'Không nhận được mã';

  @override
  String get resend_code => 'Gửi lại mã';

  @override
  String get enter_the_verification_code => 'Nhập mã 4 chữ số đã gửi đến';

  @override
  String get otp_verification => 'Xác nhận số điện thoại của bạn';

  @override
  String get email_otp_verification => 'Xác nhận địa chỉ email của bạn';

  @override
  String get confirm_email => 'Xác nhận email';

  @override
  String get send_code_too_much =>
      'Yêu cầu mã quá nhiều. Vui lòng thử lại sau hoặc dùng email khác.';

  @override
  String get try_another_email => 'Vui lòng thử lại sau hoặc dùng email khác.';

  @override
  String get trending_of_this_month => 'Xu hướng của tháng này';

  @override
  String get view_all => 'Xem tất cả';

  @override
  String get recent_published => 'Xuất bản gần đây';

  @override
  String get top_sales_books => 'Sách bán chạy nhất';

  @override
  String get book_details => 'Chi tiết sách';

  @override
  String get related_book => 'Sách liên quan';

  @override
  String get author => 'Tác giả';

  @override
  String get yes => 'Có';

  @override
  String get no => 'Không';

  @override
  String get summary => 'Tóm tắt';

  @override
  String get invoice => 'Hóa đơn';

  @override
  String get name => 'Tên';

  @override
  String get email => 'Email';

  @override
  String get phone => 'Điện thoại';

  @override
  String get verify => 'Xác minh';

  @override
  String get verify_your_email => 'Xác minh email của bạn';

  @override
  String get payment_type => 'Loại thanh toán';

  @override
  String get meeting_list => 'Danh sách cuộc họp';

  @override
  String get certificates => 'Chứng chỉ';

  @override
  String get gender => 'Giới tính';

  @override
  String get select_gender => 'Chọn giới tính';

  @override
  String get update_profile => 'Cập nhật hồ sơ';

  @override
  String get date_of_birth => 'Ngày sinh';

  @override
  String get address => 'Địa chỉ';

  @override
  String get post_review => 'Post Review';

  @override
  String get write_something => 'Viết gì đó';

  @override
  String get no_comments_yet => 'Không có bình luận';

  @override
  String get write_a_comment => 'Viết bình luận';

  @override
  String get review => 'Đánh giá';

  @override
  String get rating => 'Xếp hạng';

  @override
  String get logout_dialog => 'Đăng xuất?';

  @override
  String get are_you_sure_logout_from_device =>
      'Bạn có chắc chắn muốn đăng xuất khỏi thiết bị này không?';

  @override
  String get lessons => 'Bài học';

  @override
  String get lessons_browsers => 'Trình duyệt bài học';

  @override
  String get enroll => 'Đăng ký';

  @override
  String get type_here => 'Nhập tại đây';

  @override
  String get dark_mode => 'Chế độ tối';

  @override
  String get something_wrong => 'Đã xảy ra lỗi';

  @override
  String get setting => 'Cài đặt';

  @override
  String get settings => 'Cài đặt';

  @override
  String get transfer_domain => 'Chuyển tên miền';

  @override
  String get total_course => 'Tổng số khóa học';

  @override
  String get expertise => 'Chuyên môn';

  @override
  String get biography => 'Tiểu sử';

  @override
  String get total_instructor => 'Tổng số giảng viên';

  @override
  String get total_students => 'Tổng số học viên';

  @override
  String get total_enrollments => 'Tổng số đăng ký';

  @override
  String get popular_books => 'Sách phổ biến';

  @override
  String get book_review => 'Đánh giá sách';

  @override
  String get search_item => 'Tìm kiếm mục';

  @override
  String get expressions_for_discussion => 'Biểu thức để thảo luận';

  @override
  String get high_rated_books => 'Sách được đánh giá cao';

  @override
  String get in_stock => 'Còn hàng';

  @override
  String get show_more => 'Hiển thị thêm';

  @override
  String get show_less => 'Ẩn bớt';

  @override
  String get title => 'Tiêu đề';

  @override
  String get publisher => 'Nhà xuất bản';

  @override
  String get edition => 'Phiên bản';

  @override
  String get page => 'Trang';

  @override
  String get cancel => 'Hủy';

  @override
  String get country => 'Quốc gia';

  @override
  String get applied_coupon => 'Đã áp dụng phiếu giảm giá';

  @override
  String get copied_to_clipboard => 'Đã sao chép vào bảng tạm';

  @override
  String get are_you_sure_to_delete =>
      'Bạn có chắc chắn muốn xóa tài khoản không';

  @override
  String get type_a_message => 'Nhập tin nhắn';

  @override
  String get search_course => 'Tìm kiếm khóa học';

  @override
  String get courses_found => 'Đã tìm thấy khóa học';

  @override
  String get course_category => 'Danh mục khóa học';

  @override
  String get course_collection => 'Bộ sưu tập khóa học';

  @override
  String get category => 'Danh mục';

  @override
  String get no_category_found => 'Không tìm thấy danh mục nào';

  @override
  String get back_to_homepage => 'Quay lại trang chủ';

  @override
  String get back => 'Quay về';

  @override
  String get quiz => 'Bài kiểm tra';

  @override
  String get quiz_in_course => 'Bài kiểm tra của khóa học';

  @override
  String get quiz_in_chapter => 'Bài kiểm tra của mỗi chương';

  @override
  String get this_chapter_has_no_quiz => 'Chương này không có bài kiểm tra';

  @override
  String get chapter => 'Chương';

  @override
  String get results => 'Kết quả';

  @override
  String get start_quiz => 'Bắt đầu bài kiểm tra';

  @override
  String get your_time => 'Thời gian của bạn';

  @override
  String get your_score => 'Điểm của bạn';

  @override
  String get your_result => 'Kết quả của bạn';

  @override
  String get your_profile_update_successfully =>
      'Thông tin của bạn được chỉnh sửa thành công';

  @override
  String get total_score => 'Tổng điểm';

  @override
  String get continue_course => 'Tiếp tục khóa học';

  @override
  String get passed => 'Đã vượt qua';

  @override
  String get failed => 'đã thất bại';

  @override
  String get write_your_ans => 'Viết câu trả lời của bạn';

  @override
  String get added_to_cart => 'đã thêm vào giỏ hàng';

  @override
  String get audio_file_error => 'Không thể phát âm thanh này';

  @override
  String get failed_to_download => 'Lỗi tệp. Không thể tải xuống tệp này.';

  @override
  String get change_email => 'Đổi email';

  @override
  String get change_your_detail => 'Thay đổi thông tin';

  @override
  String get change_your_password => 'Đổi mật khẩu';

  @override
  String get current_password => 'Mật khẩu hiện tại';

  @override
  String get choose_password => 'Nhập mật khẩu';

  @override
  String get enter_your_email => 'Nhập email của bạn';

  @override
  String get enter_your_password => 'Nhập mật khẩu của bạn';

  @override
  String get enter_your_new_password => 'Nhập mật khẩu mới';

  @override
  String get confirm_your_password => 'Xác nhận mật khẩu của bạn';

  @override
  String get enter_your_user_name => 'Nhập tên người dùng';

  @override
  String get password_should_be => 'Mật khẩu phải bao gồm 8 ký tự';

  @override
  String get save_and_change => 'Lưu & Thay đổi';

  @override
  String get view_cart => 'Xem Giỏ hàng';

  @override
  String get no_notification => 'Không có thông báo nào cho bạn';

  @override
  String get empty_wishlist => 'Danh sách mong muốn của bạn trống.';

  @override
  String get login_to_add_this_to_cart =>
      'Trước tiên, hãy đăng nhập để thêm khóa học này vào giỏ hàng của bạn.';

  @override
  String get enroll_now => 'Đăng ký ngay';

  @override
  String get view_details => 'Xem Chi tiết';

  @override
  String get total_mark => 'Tổng điểm';

  @override
  String get pass_mark => 'Điểm đạt';

  @override
  String get pick_file => 'Chọn Tệp';

  @override
  String get review_added_successfully => 'Đã thêm bài đánh giá thành công';

  @override
  String get no_resource_available_yet => 'Chưa có tài nguyên nào khả dụng';

  @override
  String get payment_gateway => 'Cổng thanh toán';

  @override
  String get start_learning => 'Bắt đầu học';

  @override
  String get start_learning_successful => 'Bắt đầu học thành công';

  @override
  String get no_certificate_found => 'Không tìm thấy chứng chỉ nào';

  @override
  String get wishlist_added_successfully =>
      'Đã thêm danh sách mong muốn thành công';

  @override
  String get wishlist_removed_successfully =>
      'Đã xóa danh sách mong muốn thành công';

  @override
  String get empty_cart => 'Giỏ hàng của bạn trống';

  @override
  String get you_dont_have_any_meeting_yet => 'Bạn chưa có cuộc họp nào';

  @override
  String get no_favorites => 'Không có mục yêu thích nào';

  @override
  String get you_have_not_liked_any_items_yet =>
      'Bạn chưa thích bất kỳ mục nào';

  @override
  String get no_notifications_yet => 'Không thông báo chưa';

  @override
  String get you_have_no_notifications => 'Bạn hiện không có thông báo nào';

  @override
  String get no_result_found => 'Không tìm thấy kết quả nào';

  @override
  String get please_try_again_with_another_keywords =>
      'Vui lòng thử lại với từ khóa khác hoặc có thể sử dụng thuật ngữ chung chung';

  @override
  String get you_have_not_added_anything =>
      'Bạn chưa thêm bất kỳ thứ gì vào giỏ hàng.\nTiếp tục và khám phá các danh mục hàng đầu';

  @override
  String get profile_updated_successfully => 'Cập nhật hồ sơ thành công';

  @override
  String get submitted_successful => 'Gửi thành công';

  @override
  String get change_theme => 'Thay đổi chủ đề';

  @override
  String get change_language => 'Thay đổi ngôn ngữ';

  @override
  String get resource => 'Tài nguyên';

  @override
  String get light_mode => 'Chế độ sáng';

  @override
  String get user_name => 'Tên người dùng';

  @override
  String get online_mode => 'Chế độ trực tuyến';

  @override
  String get offline_mode => 'Chế độ ngoại tuyến';

  @override
  String get tap_to_online => 'Nhấn để trực tuyến';

  @override
  String get tap_to_offline => 'Nhấn để ngoại tuyến';

  @override
  String get question_count => 'Số câu hỏi';

  @override
  String get forum => 'Diễn đàn';

  @override
  String get no_forums_found => 'Không tìm thấy diễn đàn nào';

  @override
  String get social => 'Mạng xã hội';

  @override
  String get group => 'Nhóm';

  @override
  String get switch_domain => 'Chuyển tên miền';

  @override
  String get switch_domain_message =>
      'Tính năng này cho phép đối tác sử dụng tên miền riêng. Quá trình xác minh có thể mất vài phút.';

  @override
  String get prompt_update_profile_message =>
      'Cập nhật hồ sơ của bạn để có trải nghiệm tốt hơn.';

  @override
  String get ascending => 'Tăng dần';

  @override
  String get can_not_launch => 'Không thể mở';

  @override
  String get clear_all => 'Xóa tất cả';

  @override
  String get connection_to_api_server_failed =>
      'Kết nối đến máy chủ thất bại. Vui lòng thử lại.';

  @override
  String get courses => 'Các khóa học';

  @override
  String get deadline => 'Hạn chót';

  @override
  String get deleteYourAccount => 'Xóa tài khoản của bạn';

  @override
  String get descending => 'Giảm dần';

  @override
  String get discovery => 'Khám phá';

  @override
  String get empty_notifications => 'Không có thông báo';

  @override
  String get enter_valid_information => 'Vui lòng nhập thông tin hợp lệ';

  @override
  String get enter_your_first_name => 'Nhập tên của bạn';

  @override
  String get enter_your_last_name => 'Nhập họ của bạn';

  @override
  String get hours => 'Giờ';

  @override
  String get join => 'Tham gia';

  @override
  String get legal => 'Pháp lý';

  @override
  String get login_to_continue => 'Đăng nhập để tiếp tục';

  @override
  String get membership => 'Thành viên';

  @override
  String get news => 'Tin tức';

  @override
  String get no_data_found => 'Không tìm thấy dữ liệu';

  @override
  String get page_not_found => 'Không tìm thấy trang';

  @override
  String get quizzes => 'Các bài kiểm tra';

  @override
  String get resend_it => 'Gửi lại';

  @override
  String get see_all => 'Xem tất cả';

  @override
  String get search => 'Tìm kiếm';

  @override
  String get skip => 'Bỏ qua';

  @override
  String get sorry => 'Xin lỗi';

  @override
  String get sorry_your_order_history_is_Empty =>
      'Xin lỗi, lịch sử đơn hàng của bạn trống';

  @override
  String get subject => 'Chủ đề';

  @override
  String get subscribed_successfully => 'Đăng ký thành công';

  @override
  String get suggestions => 'Gợi ý';

  @override
  String get update_is_available => 'Có bản cập nhật mới';

  @override
  String get update_now => 'Cập nhật ngay';

  @override
  String get we_are_under_maintenance => 'Chúng tôi đang bảo trì';

  @override
  String get we_will_be_right_back => 'Chúng tôi sẽ quay lại ngay';

  @override
  String get you_are_not_logged_in => 'Bạn chưa đăng nhập';

  @override
  String get you_dont_have_any_purchased_course =>
      'Bạn không có khóa học nào đã mua.';

  @override
  String get your_app_needs_to_update => 'Ứng dụng của bạn cần cập nhật';

  @override
  String get your_cart_is_empty => 'Giỏ hàng của bạn trống';

  @override
  String get trying_to_connect => 'Đang kết nối...';

  @override
  String get store_available => 'Cửa hàng hiện khả dụng.';

  @override
  String get store_unavailable => 'Cửa hàng hiện không khả dụng.';

  @override
  String get not_connected => 'Chưa kết nối';

  @override
  String get unable_to_connect_payments =>
      'Không thể kết nối tới hệ thống xử lý thanh toán. Ứng dụng đã được cấu hình đúng chưa? Xem README mẫu để biết hướng dẫn.';

  @override
  String get restore_purchase => 'Khôi phục mua hàng';

  @override
  String get make_payment => 'Thanh toán';

  @override
  String get no_internet_connection => 'Không có kết nối internet';

  @override
  String get cant_open_link => 'Không thể mở liên kết.';

  @override
  String error_occurred(String message) {
    return 'Lỗi: $message';
  }

  @override
  String get by_sign_up_agree => 'Khi đăng ký, bạn đồng ý với ';

  @override
  String get and_text => 'và';

  @override
  String get registration_successful => 'Đăng ký thành công';

  @override
  String get set_new_password_success => 'Đặt mật khẩu mới thành công';

  @override
  String get change_password_success =>
      'Đổi mật khẩu thành công, vui lòng đăng nhập lại';

  @override
  String get otp_expired => 'OTP của bạn đã hết hạn.';

  @override
  String attempts_left(int count) {
    return 'Bạn còn $count lần thử.';
  }

  @override
  String get too_many_attempts =>
      'OTP không đúng. Quá nhiều lần thử. Hãy dùng email khác.';

  @override
  String get password_missing => 'Mật khẩu hiện tại của bạn bị thiếu';

  @override
  String get check_out => 'Thanh toán';

  @override
  String get connected_to_internet => 'Bạn đã kết nối với internet';

  @override
  String get grid_view_pagination => 'Lưới';
}
