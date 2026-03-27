<?php
use Drupal\file\Entity\File;

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

$curriculum = json_decode(file_get_contents($json_path), TRUE);
$course_storage = \Drupal::entityTypeManager()->getStorage('lms_course');

$file_system = \Drupal::service('file_system');
$course_images_pub_dir = 'public://course_images';
$file_system->prepareDirectory($course_images_pub_dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

// Thư mục chứa hình ảnh khóa học đã được copy vào project
$image_dir = \Drupal::root() . '/modules/custom/lms_ai/scripts/course_images';

if (!is_dir($image_dir)) {
  echo "Image directory not found: $image_dir\n";
  exit(1);
}

$updated = false;

foreach ($curriculum as $cat) {
  if (empty($cat['courses'])) continue;
  foreach ($cat['courses'] as $course) {
    if (!empty($course['id'])) {
      $cid = $course['id'];
      
      $course_entity = $course_storage->load($cid);
      if (!$course_entity) continue;
      
      // Auto-detect the image field on the course entity
      $image_field = null;
      $possible_fields = ['field_image', 'field_thumbnail', 'field_course_image', 'field_cover', 'field_media'];
      foreach ($possible_fields as $f) {
        if ($course_entity->hasField($f)) {
          $image_field = $f;
          break;
        }
      }
      
      if (!$image_field) {
        echo "- No recognized image field found for course ID $cid\n";
        continue;
      }
      
      // Prevent overwriting existing images if it's already set
      if (!$course_entity->get($image_field)->isEmpty()) {
        echo "- Course ID $cid already has an image attached. Skipping.\n";
        continue;
      }

      $prefix = "course_img_{$cid}_";
      $files = glob("$image_dir/{$prefix}*.png");
      if (empty($files)) {
          $files = glob("$image_dir/{$prefix}*.jpg");
      }
      
      if (!empty($files)) {
        $source_file = $files[0];
        $file_uri = $course_images_pub_dir . '/' . basename($source_file);
        
        $copied_uri = $file_system->copy($source_file, $file_uri, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
        if ($copied_uri) {
          // Find existing file entity with this uri
          $existing_files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $copied_uri]);
          $file = reset($existing_files);
          
          if (!$file) {
            $file = File::create([
              'uri' => $copied_uri,
              'status' => 1,
            ]);
            $file->save();
          }
          
          $course_entity->set($image_field, [
            'target_id' => $file->id(),
            'alt' => $course_entity->label() . ' Cover Image',
          ]);
          $course_entity->save();
          echo "=> Assigned new image to Course ID $cid: {$course_entity->label()}\n";
          $updated = true;
        } else {
            echo "- Failed to copy image into public uri for Course ID $cid\n";
        }
      } else {
        echo "- No image file found in $image_dir for course ID $cid (looked for $prefix*)\n";
      }
    }
  }
}

if ($updated) {
    echo "\nFinished importing course images successfully!\n";
} else {
    echo "\nFinished checking. No images were imported.\n";
}

