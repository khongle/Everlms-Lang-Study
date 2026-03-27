<?php

/**
 * Script to test the Google Slide Builder Service.
 *
 * Usage:
 * ddev drush scr web/modules/custom/lms_ai/scripts/test_google_slide.php
 */

use Drupal\Core\Url;

try {
  echo "--- BẮT ĐẦU TEST KHỞI TẠO GOOGLE SLIDE ---\n";
  /** @var \Drupal\lms_ai\Service\GoogleSlideBuilderService $google_slide_builder */
  $google_slide_builder = \Drupal::service('lms_ai.google_slide_builder');

  // Hardcode Template ID của bạn từ URL (thay ID dưới đây cho chuẩn nếu cần)
  $template_id = '1bVYcpMICXEea477wLCie0TuXpbJjJ_Ii'; 
  
  // Dữ liệu giả lập test mảng Nội Dung Động (giống Prompt slide_content)
  $mock_json = [
    "slide_content" => [
      [
        "title" => "Slide 1: Khởi đầu",
        "html" => "<p>Đây là một đoạn giới thiệu.</p><ul><li>Gạch đầu dòng 1</li><li>Gạch đầu dòng 2</li></ul>"
      ],
      [
        "title" => "Slide 2: Kết thúc",
        "html" => "<p>Bạn đã hoàn thành bài học tự tạo bằng API! Cảm ơn bạn.</p>"
      ]
    ]
  ];

  echo "Tiến hành Duplicate & Replace Text... (quá trình này có thể mất 3-5 giây)\n";
  
  // Mặc định ID của Google API Client Account đầu tiên (OAuth) thường là 1
  $google_api_client_id = 1;
  echo "Đang dùng OAuth Client ID: " . $google_api_client_id . "\n";

  $new_slide_id = $google_slide_builder->buildSlideFromTemplate(
    $template_id, 
    $mock_json, 
    '[EverLMS Auto] Test Generated Slide',
    $google_api_client_id
  );

  if ($new_slide_id) {
    echo "\n=== THÀNH CÔNG RỰC RỠ ===\n";
    echo "ID File mới tạo: " . $new_slide_id . "\n";
    echo "Link xem Iframe: https://docs.google.com/presentation/d/{$new_slide_id}/embed?start=false&loop=false&delayms=3000\n";
    echo "Bạn có thể dán link trên vào trình duyệt để chiêm ngưỡng kết quả gốc!\n";
  } else {
    echo "\n[CẢNH BÁO] Có lỗi xảy ra. Hãy kiểm tra lại file private/google_service_account.json đã đúng vị trí chưa và bạn đã CẤP QUYỀN ĐỐI TÁC (Share Editor) cho email Service Account tại cái file Template gốc chưa nhé.\n";
    echo "Bạn cũng có thể xem log lỗi chi tiết tại tab Reports -> Recent log messages trên Drupal Admin.\n";
  }

} catch (\Exception $e) {
  echo "\n[ERROR] Crash toàn tập: " . $e->getMessage() . "\n";
}
