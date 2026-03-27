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

$prompt_path = \Drupal::root() . '/modules/custom/lms_ai/prompt/course_detail.txt';
if (!file_exists($prompt_path)) {
  echo "Prompt file not found: $prompt_path\n";
  exit(1);
}
$prompt_template = file_get_contents($prompt_path);

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

$curriculum = json_decode(file_get_contents($json_path), TRUE);
$training_program = $config->get('training_program') ?: 'LMS Training';
$langcode = $config->get('langcode') ?: 'vi';
$langcodes_list = "vi, en";

// Prepare context for the prompt
$categories_courses = [];
$curriculum_context = []; // simplified version of curriculum without detail blobs
foreach ($curriculum as $cat) {
  $c_list = [];
  $cat_context = ['category' => $cat['category'], 'courses' => []];
  
  foreach ($cat['courses'] as $c) {
    if (isset($c['course_title'])) {
      $c_list[] = $c['course_title'];
      $course_ctx = ['course_title' => $c['course_title']];
      if (!empty($c['chapters'])) {
        $course_ctx['chapters'] = array_map(function($ch) {
          return $ch['chapter_title'] ?? '';
        }, $c['chapters']);
      }
      $cat_context['courses'][] = $course_ctx;
    }
  }
  $categories_courses[$cat['category']] = $c_list;
  $curriculum_context[] = $cat_context;
}

$categories_courses_str = json_encode($categories_courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$courses_list_suggestion = json_encode($curriculum_context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$updated = false;

foreach ($curriculum as &$cat) {
  $cat_name = $cat['category'];
  if (empty($cat['courses'])) {
    continue;
  }
  
  foreach ($cat['courses'] as &$course) {
    if (empty($course['is_detail_generated']) || empty($course['details'])) {
      $course_title = $course['course_title'] ?? 'Unknown Course';
      echo ">> Generating details for course: $course_title ...\n";

      $prompt = str_replace(
        ['{{training_program}}', '{{langcode}}', '{{langcodes_list}}', '{{courses_list_suggestion}}', '{{categories_courses}}', '{{course_name}}', '{{category_name}}'],
        [$training_program, $langcode, $langcodes_list, $courses_list_suggestion, $categories_courses_str, $course_title, $cat_name],
        $prompt_template
      );

      $response = $provider->generateJson($prompt);

      if ($response && is_array($response)) {
        // Evaluate typical structures since prompt asks for [ {"vi": {...}} ]
        $details_data = isset($response[0]) ? $response[0] : $response;
        
        $course_details = null;
        if (isset($details_data[$langcode])) {
          $course_details = $details_data[$langcode];
        } elseif (isset($details_data['course_title'])) {
          // Flattened structure fallback
          $course_details = $details_data;
        }

        if ($course_details) {
          $course['details'] = $course_details;
          $course['is_detail_generated'] = true;
          $updated = true;
          echo "   -> Success!\n";
          
          // Save incrementally so we don't lose data if script crashes or stops
          file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
          echo "   -> Failed: Could not extract details correctly. Structure was:\n";
          print_r($response);
        }
      } else {
        echo "   -> Failed to generate or parse JSON array from AI.\n";
      }
      
      // Delay slightly to respect rate limits
      sleep(4);
    }
  }
}

if ($updated) {
  echo "\nAll missing courses have been processed and saved to ai_curriculum.json!\n";
} else {
  echo "\nNo missing courses were found. Everything is up to date.\n";
}
