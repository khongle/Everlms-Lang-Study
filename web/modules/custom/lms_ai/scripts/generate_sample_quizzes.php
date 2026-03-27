<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require __DIR__ . '/../../../../autoload.php';

try {
  $autoloader = require __DIR__ . '/../../../../autoload.php';
  $request = Request::createFromGlobals();
  $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();
  $kernel->preHandle($request);
  \Drupal::setContainer($kernel->getContainer());

  $container = \Drupal::getContainer();
  $aiProviderManager = $container->get('plugin.manager.lms_ai_provider');
  $config_factory = $container->get('config.factory');
  $config = $config_factory->getEditable('lms_ai.engine.settings');

  $active_provider_id = 'gemini';
  $provider = $aiProviderManager->createInstance($active_provider_id);

  $json_path = \Drupal::service('extension.list.module')->getPath('lms_ai') . '/JSON/ai_curriculum.json';
  if (!file_exists($json_path)) {
    die("Error: ai_curriculum.json not found.\n");
  }

  $data = file_get_contents($json_path);
  $curriculum = json_decode($data, TRUE);

  $prompt_template = $config->get('quiz_prompt');
  if (empty($prompt_template)) {
    $prompt_template = <<<EOT
Hãy đọc nội dung bài học dưới đây và sinh ra tối đa 5 câu hỏi trắc nghiệm (hoặc nhiều hơn) liên quan đến bài học: {{lesson_title}}.

Kết quả trả về bắt buộc phải là một mảng JSON nguyên thủy (Array of Objects), không kèm theo giải thích hay định dạng markdown (ví dụ không dùng ```json).

Cấu trúc JSON JSON bắt buộc tuân theo định dạng sau cho từng loại câu hỏi:
[
  {
    "type": "single_choice",
    "question": "Ngôn ngữ lập trình nào sau đây chạy ở nền tảng trình duyệt?",
    "options": ["Python", "Java", "JavaScript", "C++"],
    "correct_answer": "JavaScript",
    "explanation": "JavaScript là ngôn ngữ chạy trực tiếp trên trình duyệt web."
  },
  {
    "type": "essay",
    "question": "Phân tích ưu nhược điểm của OOP.",
    "explanation": "Học viên cần nêu ra tính đóng gói, kế thừa, đa hình."
  }
]

Dưới đây là chi tiết bài học:
{{lesson_content}}
EOT;
  }

  $training_program = $config->get('training_program');
  $langcode = $config->get('program_language') ?: 'vi';

  $courses_processed = 0;
  $sample_quizzes = [];

  // Pick 1 course
  foreach ($curriculum as $cat) {
    if (!empty($cat['courses'])) {
      foreach ($cat['courses'] as $course) {
        if ($courses_processed >= 1) break 2;

        $course_title = $course['course_title'] ?? '';
        echo "Processing Course: $course_title\n";

        // Get the first chapter and first lesson
        if (!empty($course['chapters'])) {
          $first_chapter = reset($course['chapters']);
          $chapter_title = $first_chapter['chapter_title'] ?? '';

          if (!empty($first_chapter['lessons'])) {
            $first_lesson = reset($first_chapter['lessons']);
            $lesson_name = $first_lesson['lesson_title'] ?? '';
            $lesson_content = $first_lesson['lesson_detail'] ?? '';

            echo "  - Generating quiz for Lesson: $lesson_name\n";

            // Prepare prompt
            $final_prompt = $prompt_template;
            $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
            $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
            $final_prompt = str_replace('{{course_title}}', $course_title, $final_prompt);
            $final_prompt = str_replace('{{chapter_title}}', $chapter_title, $final_prompt);
            $final_prompt = str_replace('{{lesson_title}}', $lesson_name, $final_prompt);
            $final_prompt = str_replace('{{lesson_content}}', $lesson_content, $final_prompt);

            $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON array of objects. Do not include conversational text, markdown formatting like ```json, or nested keys. Just the raw array [{...}, {...}].";
            $final_prompt .= $system_prompt;

            try {
              $ai_response = $provider->generateText($final_prompt);
              $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
              $quiz_json = json_decode(trim($ai_response), TRUE);

              if (json_last_error() === JSON_ERROR_NONE && is_array($quiz_json)) {
                $sample_quizzes[] = [
                  'course_title' => $course_title,
                  'lesson_title' => $lesson_name,
                  'quiz_data' => $quiz_json
                ];
                echo "    -> Success.\n";
                // LƯU TỪNG BƯỚC VÀO FILE
                $output_file = \Drupal::service('extension.list.module')->getPath('lms_ai') . '/JSON/sample_quizzes.json';
                file_put_contents($output_file, json_encode($sample_quizzes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
              } else {
                echo "    -> Failed to decode JSON.\n";
              }
            } catch (\Exception $e) {
              echo "    -> Exception: " . $e->getMessage() . "\n";
            }
            sleep(2);
          }
        }
        $courses_processed++;
      }
    }
  }

  echo "\nFinished generating sample quizzes. Check sample_quizzes.json\n";

} catch (\Exception $e) {
  echo "Fatal Error: " . $e->getMessage() . "\n";
}
