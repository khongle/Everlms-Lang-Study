<?php

/**
 * Script để tự động fix các Category bị trống khóa học.
 * Chạy bằng lệnh: ddev drush php-script scripts/fix_empty_categories.php
 */

use Drupal\taxonomy\Entity\Term;
use Drupal\lms_course\Entity\Course;

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$course_storage = \Drupal::entityTypeManager()->getStorage('lms_course');

echo "Bắt đầu quét và sửa chữa Category...\n";
echo "------------------------------------------------\n";

// 1. Map các Category tự động tạo sai về đúng Category chuẩn ban đầu.
$semantic_mapping = [
  'Communication' => 'Communication & Culture',
  'Emergency' => 'Emergency & First Aid',
  'Healthcare' => 'Basic Geriatric Care & Hygiene',
];

foreach ($semantic_mapping as $from_name => $to_name) {
  $from_terms = $term_storage->loadByProperties(['name' => $from_name, 'vid' => 'course_category']);
  $from_term = !empty($from_terms) ? reset($from_terms) : null;

  $to_terms = $term_storage->loadByProperties(['name' => $to_name, 'vid' => 'course_category']);
  $to_term = !empty($to_terms) ? reset($to_terms) : null;

  if ($from_term && $to_term) {
    $course_ids = $course_storage->getQuery()->condition('field_category', $from_term->id())->accessCheck(FALSE)->execute();
    
    if (!empty($course_ids)) {
      $courses = $course_storage->loadMultiple($course_ids);
      foreach ($courses as $course) {
        $course->set('field_category', ['target_id' => $to_term->id()]);
        $course->save();
        echo "✅ Đã chuyển khóa học '{$course->label()}' từ '$from_name' sang '$to_name'.\n";
      }
      // Xóa Category cũ sau khi đã chuyển hết khóa học
      $from_term->delete();
      echo "🗑️  Đã xóa danh mục thừa '$from_name'.\n";
    }
  }
}

echo "\nKiểm tra lại các Category còn trống để tự động lấy khóa học bù vào...\n";
echo "------------------------------------------------\n";

// 2. Tìm tất cả các category hiện tại
$all_terms = $term_storage->loadByProperties(['vid' => 'course_category']);

foreach ($all_terms as $term) {
  $count = $course_storage->getQuery()->condition('field_category', $term->id())->accessCheck(FALSE)->count()->execute();
  
  if ($count == 0) {
    echo "⚠️ Danh mục '{$term->label()}' đang trống.\n";
    
    // Tìm các khóa học đang thuộc về các category "giàu có" (có nhiều hơn 2 khóa) để xin bớt 1 khóa
    $abundant_course_ids = $course_storage->getQuery()
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->range(0, 50)
      ->execute();
      
    $found_course = null;
    foreach ($abundant_course_ids as $cid) {
       $c = $course_storage->load($cid);
       if ($c) {
         if (!$c->hasField('field_category') || $c->get('field_category')->isEmpty()) {
           // Khóa học hoàn toàn chưa có category, tuyệt vời!
           $found_course = $c;
           break;
         }
         
         $c_term_id = $c->get('field_category')->target_id;
         $c_term_count = $course_storage->getQuery()->condition('field_category', $c_term_id)->accessCheck(FALSE)->count()->execute();
         
         // Nếu category của khóa học này đang có ít nhất 2 khóa học, ta có thể rút 1 khóa đi
         if ($c_term_count >= 2) {
             $found_course = $c;
             break;
         }
       }
    }
    
    if ($found_course) {
       $old_cat_id = $found_course->get('field_category')->target_id;
       $old_cat_name = "Không có";
       if ($old_cat_id) {
           $old_cat = $term_storage->load($old_cat_id);
           if ($old_cat) $old_cat_name = $old_cat->label();
       }
       
       $found_course->set('field_category', ['target_id' => $term->id()]);
       $found_course->save();
       echo "  ✅ Đã tự động điều phối khóa học '{$found_course->label()}' (từ '$old_cat_name') sang cho '{$term->label()}'.\n";
    } else {
       echo "  ❌ Không tìm thấy khóa học nào phù hợp để chia sẻ qua danh mục này.\n";
    }
  }
}

echo "\n🎉 Hoàn tất quá trình tối ưu danh mục khóa học.\n";
