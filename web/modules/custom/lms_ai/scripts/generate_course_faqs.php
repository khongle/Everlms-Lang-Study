<?php
use Drupal\Component\Serialization\Json;

$config = \Drupal::config('lms_ai.settings');
$plugin_manager = \Drupal::service('plugin.manager.lms_ai_provider');
$provider_id = $config->get('default_provider') ?: 'gemini';

try {
  /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
  $provider = $plugin_manager->createInstance($provider_id);
} catch (\Exception $e) {
  echo "Error loading AI provider '$provider_id': " . $e->getMessage() . "\n";
  exit(1);
}

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

$curriculum = json_decode(file_get_contents($json_path), TRUE);
$updated = false;

echo "Bắt đầu tiến trình quét và tạo FAQ cho các khóa học...\n";
echo "Lưu ý: Để tránh lỗi quá tải API (429 Rate Limit), script sẽ tự động nghỉ 45 giây sau mỗi lần gọi AI thành công.\n\n";

foreach ($curriculum as &$cat) {
  if (empty($cat['courses'])) continue;
  
  foreach ($cat['courses'] as &$course) {
    // Check if FAQ is already generated
    if (empty($course['is_faq_generated']) || empty($course['faq'])) {
      $course_title = $course['course_title'] ?? 'Unknown Course';
      echo ">> Đang tạo FAQ cho khóa học: $course_title ...\n";

      // Build specific Prompt for FAQ
      $prompt = "Bạn là chuyên gia phát triển khóa học trực tuyến. Khóa học có tên là: \"{$course_title}\".\n";
      $prompt .= "Nhiệm vụ: Viết một mục FAQ (Câu hỏi thường gặp) với chính xác 5 câu hỏi và câu trả lời chi tiết, bám sát và phù hợp hoàn toàn với nội dung của khóa học này.\n";
      $prompt .= "Ngôn ngữ: Tiếng Việt.\n";
      $prompt .= "ĐỊNH DẠNG BẮT BUỘC: Bạn CHỈ ĐƯỢC PHÉP trả về một mảng JSON thuần túy. KHÔNG kèm theo Markdown (như ```json) hoặc bất kỳ chữ giải thích nào khác. Cấu trúc như sau:\n";
      $prompt .= "[\n  {\n    \"question\": \"Câu hỏi 1?\",\n    \"answer\": \"Trả lời 1.\"\n  }\n]";

      // Send to AI Provider
      $response = $provider->generateJson($prompt);

      if ($response && is_array($response)) {
        // Handle different possible AI output wrapping
        $faq_data = $response;
        // Sometimes JSON array is wrapped in an object or multidimensional array
        if (isset($response['faq'])) {
            $faq_data = $response['faq'];
        } elseif (isset($response[0]) && is_array($response[0]) && !isset($response[0]['question']) && isset($response[0][0]['question'])) {
            $faq_data = $response[0]; // unwrap double array
        }

        // Validate basic structure
        if (is_array($faq_data) && isset($faq_data[0]['question'])) {
          $course['faq'] = $faq_data;
          $course['is_faq_generated'] = true;
          $updated = true;
          echo "   -> Thành công! Đã thêm " . count($faq_data) . " câu hỏi FAQ.\n";
          
          // Save incrementally to avoid losing data
          file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
          
          echo "   -> Đang ngủ 45 giây để tránh lỗi Rate Limit của AI...\n";
          sleep(45); // Rate Limit Bypass
        } else {
          echo "   -> Thất bại: AI trả về JSON nhưng cấu trúc không đúng mong đợi.\n";
          print_r($response);
          sleep(5); // Sleep a bit anyway
        }
      } else {
        echo "   -> Thất bại: AI không trả về JSON hợp lệ.\n";
        sleep(5);
      }
    }
  }
}

if ($updated) {
  echo "\nTuyệt vời! Đã hoàn thành việc tạo và lưu FAQ cho tất cả các khóa học thiếu vào ai_curriculum.json!\n";
} else {
  echo "\nHoàn tất kiểm tra. Không có khóa học nào cần tạo thêm FAQ.\n";
}
