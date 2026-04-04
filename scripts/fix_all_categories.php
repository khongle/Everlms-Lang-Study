<?php

/**
 * Script tối thượng để dọn dẹp và khôi phục Category.
 * Chạy bằng: ddev drush php-script scripts/fix_all_categories.php
 */

use Drupal\taxonomy\Entity\Term;
use Drupal\lms_course\Entity\Course;
use Drupal\file\Entity\File;

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$course_storage = \Drupal::entityTypeManager()->getStorage('lms_course');

echo "Bắt đầu dọn dẹp và gom nhóm Category nâng cao...\n";
echo "------------------------------------------------\n";

// Bảng ánh xạ tự động chuyển nhà (Từ Category cũ mốc/nhái -> Category tên chuẩn và đầy đủ)
$mapping = [
  'Communication' => 'Communication & Culture',
  'Emergency' => 'Emergency & First Aid',
  'Healthcare' => 'Basic Geriatric Care & Hygiene',
  'Mental Health' => 'Mental Health & Wellbeing',
  'Palliative & End-of-Life' => 'Palliative Care & End of Life',
  'Palliative' => 'Palliative Care & End of Life',
  'Medical Knowledge' => 'Medical Knowledge & Medications',
  'Mobility' => 'Mobility & Safety',
  'Nutrition' => 'Nutrition & Hydration',
  'Ethics' => 'Ethics & Law',
  'Therapy' => 'Therapy & Activities',
  'Dementia' => 'Dementia & Cognitive Care',
];

foreach ($mapping as $short_name => $long_name) {
  $short_terms = $term_storage->loadByProperties(['name' => $short_name, 'vid' => 'course_category']);
  $long_terms = $term_storage->loadByProperties(['name' => $long_name, 'vid' => 'course_category']);
  
  $short_term = !empty($short_terms) ? reset($short_terms) : null;
  $long_term = !empty($long_terms) ? reset($long_terms) : null;
  
  // Nếu tồn tại cả term ngắn và dài, tiến hành gom về term dài
  if ($short_term && $long_term && $short_term->id() != $long_term->id()) {
    $cids = $course_storage->getQuery()->condition('field_category', $short_term->id())->accessCheck(FALSE)->execute();
    if (!empty($cids)) {
      $courses = $course_storage->loadMultiple($cids);
      foreach ($courses as $c) {
        $c->set('field_category', ['target_id' => $long_term->id()]);
        $c->save();
        echo "✅ Chuyển khóa học '{$c->label()}' từ '$short_name' sang '$long_name'.\n";
      }
    }
    
    // Nếu category dài không có ảnh, nhưng category ngắn lại có ảnh, chuyển ảnh sang!
    if ((!$long_term->hasField('field_category_thumbnail') || $long_term->get('field_category_thumbnail')->isEmpty()) &&
        ($short_term->hasField('field_category_thumbnail') && !$short_term->get('field_category_thumbnail')->isEmpty())) {
        $long_term->set('field_category_thumbnail', $short_term->get('field_category_thumbnail')->getValue());
        $long_term->save();
        echo "   -> Đã copy ảnh từ '$short_name' qua '$long_name'.\n";
    }
    
    // Xóa category dư thừa
    $short_term->delete();
    echo "🗑️  Xóa danh mục dư thừa '$short_name'.\n";
  }
}

echo "\nKhôi phục và gắn ảnh cho TẤT CẢ Category...\n";
echo "------------------------------------------------\n";

$all_terms = $term_storage->loadByProperties(['vid' => 'course_category']);

foreach ($all_terms as $term) {
  $term_name = $term->label();
  
  // Bỏ qua nếu đã có ảnh
  if ($term->hasField('field_category_thumbnail') && !$term->get('field_category_thumbnail')->isEmpty()) {
     continue;
  }

  // Tách từ khóa từ tên category. VD: "Medical Knowledge & Medications" -> ["medical knowledge", "medications"]
  $parts = explode('&', $term_name);
  $parts = array_map('trim', $parts);
  $parts = array_map('strtolower', $parts);
  
  // Lấy chữ đầu tiên để tìm kiếm rộng hơn. VD: "Medical"
  $first_word = explode(' ', $parts[0])[0];
  
  $search_keywords = array_merge([$first_word, str_replace(' ', '-', $parts[0])], $parts);
  if ($term_name == 'Basic Geriatric Care & Hygiene') {
      $search_keywords[] = 'healthcare';
      $search_keywords[] = 'hygiene';
  }
  
  $file_found = null;
  // Truy lùng qua file_managed
  foreach ($search_keywords as $keyword) {
    if (strlen($keyword) < 3) continue;
    $query = \Drupal::entityQuery('file')
      ->condition('uri', '%' . $keyword . '%', 'LIKE')
      ->condition('filemime', 'image/%', 'LIKE')
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->accessCheck(FALSE);
    $fids = $query->execute();
    if (!empty($fids)) {
      $file_found = File::load(reset($fids));
      break;
    }
  }
  
  // Nếu vẫn không có, lấy ảnh của khóa học đầu tiên
  if (!$file_found) {
    $cids = $course_storage->getQuery()->condition('field_category', $term->id())->accessCheck(FALSE)->execute();
    if (!empty($cids)) {
       $courses = $course_storage->loadMultiple($cids);
       foreach ($courses as $c) {
         if ($c->hasField('field_thumbnail') && !$c->get('field_thumbnail')->isEmpty()) {
           $file_found = $c->get('field_thumbnail')->entity;
           if ($file_found) {
               echo "   (Dùng tạm ảnh khóa học '{$c->label()}' cho '$term_name')\n";
               break;
           }
         }
       }
    }
  }
  
  if ($file_found) {
    $term->set('field_category_thumbnail', [
      'target_id' => $file_found->id(),
      'alt' => $term_name,
    ]);
    $term->save();
    echo "🎉 Đã gắn lại ảnh thành công cho '$term_name'.\n";
  } else {
    echo "❌ Không tìm thấy ảnh nào cho '$term_name' bằng mọi giá.\n";
  }
}

echo "\nQuá trình hoàn tất! Vui lòng F5 trang trình duyệt. \n";
