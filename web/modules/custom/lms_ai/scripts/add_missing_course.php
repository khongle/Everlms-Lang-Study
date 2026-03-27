<?php

/**
 * Script to add an existing Drupal course to ai_curriculum.json
 * 
 * Usage:
 * ddev drush scr web/modules/custom/lms_ai/scripts/add_missing_course.php
 */

$course_name = "AI for Developers 101: GitHub Copilot & Cursor";

echo "Bắt đầu lấy dữ liệu khóa học '$course_name' từ Drupal...\n";

$entity_type_manager = \Drupal::entityTypeManager();
$course_storage = $entity_type_manager->getStorage('lms_course');

$courses = $course_storage->loadByProperties(['name' => $course_name]);
$course = reset($courses);

if (!$course) {
  echo "[LỖI] Không tìm thấy khóa học nào có tên: $course_name\n";
  exit;
}

$course_id = $course->id();
echo "Đã tìm thấy Course ID: $course_id\n";

$chapters_data = [];
$chapter_refs = $course->get('field_chapters')->referencedEntities();
foreach ($chapter_refs as $chapter) {
  $lessons_data = [];
  $lesson_refs = $chapter->get('field_lessons')->referencedEntities();
  foreach ($lesson_refs as $lesson) {
    $lessons_data[] = [
      'id' => $lesson->id(),
      'lesson_title' => $lesson->label(),
      'lesson_description' => ''
    ];
  }

  $chapters_data[] = [
    'id' => $chapter->id(),
    'chapter_title' => $chapter->label(),
    'chapter_description' => '',
    'lessons' => $lessons_data
  ];
}

$course_json_data = [
  'id' => $course_id,
  'course_title' => $course->label(),
  'is_detail_generated' => true,
  'chapters' => $chapters_data
];

// Load current JSON file
$module_handler = \Drupal::service('module_handler');
$module_path = $module_handler->getModule('lms_ai')->getPath();
$json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

if (file_exists($json_file)) {
  $json_content = file_get_contents($json_file);
  $data = json_decode($json_content, TRUE);
  
  if (!is_array($data)) { 
    $data = []; 
  }
  
  // Check if course already exists directly
  $already_exists = false;
  foreach ($data as $cat) {
    if (!empty($cat['courses'])) {
      foreach ($cat['courses'] as $crs) {
        if ($crs['id'] == $course_id || $crs['course_title'] == $course_name) {
          $already_exists = true;
          break 2;
        }
      }
    }
  }

  if ($already_exists) {
    echo "[INFO] Khóa học đã tồn tại trong file JSON. Đang bỏ qua...\n";
    exit;
  }

  $target_category = 'Manual Inserted Courses';
  $category_found = false;
  foreach ($data as &$cat) {
    if ($cat['category'] == $target_category) {
      $cat['courses'][] = $course_json_data;
      $category_found = true;
      break;
    }
  }
  
  if (!$category_found) {
    $data[] = [
      'category' => $target_category,
      'courses' => [$course_json_data]
    ];
  }
  
  file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  echo "✅ Đã ghim thành công cấu trúc rỗng (Course -> Chapters -> Lessons) của khóa học này vào file JSON!\n";
} else {
  echo "[LỖI] Không tìm thấy file JSON ở đường dẫn: $json_file\n";
}

echo "Xong! Bây giờ bạn có thể thử Generate Slide Content cho khóa này trên Form AI nha.\n";
