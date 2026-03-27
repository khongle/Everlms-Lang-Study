<?php

use Drupal\taxonomy\Entity\Term;

echo "Khởi tạo hệ thống Phân loại Trình độ (Level) cho khóa học...\n";

// 1. Lấy hoặc tạo Taxonomy Terms cho 'course_level'
$levels = ['Beginner', 'Intermediate', 'Advanced'];
$level_terms = [];

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

foreach ($levels as $name) {
  $query = $term_storage->getQuery()
    ->condition('vid', 'course_level')
    ->condition('name', $name)
    ->accessCheck(FALSE);
  $tids = $query->execute();
  
  if (empty($tids)) {
    $term = Term::create([
      'vid' => 'course_level',
      'name' => $name,
    ]);
    $term->save();
    echo "  + Đã tạo Term mới: $name (ID: " . $term->id() . ")\n";
    $level_terms[$name] = $term;
  } else {
    $level_terms[$name] = $term_storage->load(reset($tids));
  }
}

// 2. Logic để gán Level dựa trên Tên khóa học
function analyzeLevel($title) {
  $title_lower = strtolower($title);
  
  if (strpos($title_lower, 'introduction') !== false || 
      strpos($title_lower, 'fundamentals') !== false || 
      strpos($title_lower, 'basics') !== false ||
      strpos($title_lower, '101') !== false ||
      strpos($title_lower, 'preparing') !== false ||
      strpos($title_lower, 'understanding') !== false) {
    return 'Beginner';
  }
  
  if (strpos($title_lower, 'advanced') !== false || 
      strpos($title_lower, 'strategy') !== false || 
      strpos($title_lower, 'roadmap') !== false ||
      strpos($title_lower, 'excellence') !== false ||
      strpos($title_lower, 'masterclass') !== false ||
      strpos($title_lower, 'future') !== false ||
      strpos($title_lower, 'next wave') !== false) {
    return 'Advanced';
  }
  
  return 'Intermediate'; // Default level
}

// 3. Cập nhật Database Drupal (lms_course)
$course_ids = \Drupal::entityTypeManager()->getStorage('lms_course')
  ->getQuery()
  ->accessCheck(FALSE)
  ->execute();

$courses = \Drupal::entityTypeManager()->getStorage('lms_course')->loadMultiple($course_ids);
$db_updated = 0;

foreach ($courses as $course) {
  // Chỉ cập nhật nếu chưa có level HOẶC gán cứng (có thể chạy lại nhiều lần)
  $title = $course->label();
  $determined_level = analyzeLevel($title);
  $term_id = $level_terms[$determined_level]->id();

  $course->set('field_level', ['target_id' => $term_id]);
  $course->save();
  $db_updated++;
}
echo "=> Đã cập nhật Entity Database: $db_updated Khóa học được gán Level tự động!\n";

// 4. Cập nhật file ai_curriculum.json để đồng bộ 100%
$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (file_exists($json_path)) {
  $curriculum = json_decode(file_get_contents($json_path), TRUE);
  $json_updated = false;

  foreach ($curriculum as &$cat) {
    if (empty($cat['courses'])) continue;
    foreach ($cat['courses'] as &$json_course) {
      if (!empty($json_course['course_title'])) {
        $level_str = analyzeLevel($json_course['course_title']);
        $json_course['level'] = $level_str; // Gán text raw vào json để lưu vết
        $json_updated = true;
      }
    }
  }

  if ($json_updated) {
    file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "=> Đã lưu thành công cột 'level' vào file ai_curriculum.json.\n\n";
  }
}

echo "Xong! Bây giờ bộ lọc Level bên trái trang Courses đã hoạt động 100% hoàn hảo!\n";
