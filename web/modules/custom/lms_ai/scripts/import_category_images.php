<?php

use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;

$image_dir = \Drupal::root() . '/modules/custom/lms_ai/scripts/course_images';

if (!is_dir($image_dir)) {
  echo "Image directory not found: $image_dir\n";
  exit(1);
}

// Map the image file names to the Category Names in Drupal
$category_map = [
  'cat_img_business_operations' => 'AI for Business Operations',
  'cat_img_data_readiness'      => 'Data & AI Readiness',
  'cat_img_foundations_ai'      => 'Foundations of AI in Business',
  'cat_img_future_trends'       => 'Future Trends & Advanced AI',
  'cat_img_ai_solutions'        => 'Implementing & Managing AI Solutions',
];

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$imported = 0;

foreach ($category_map as $file_prefix => $category_name) {
  // Find taxonomy term
  $query = $term_storage->getQuery()
    ->condition('vid', 'course_category')
    ->condition('name', $category_name)
    ->accessCheck(FALSE);
  $tids = $query->execute();

  if (empty($tids)) {
    echo "Category not found: $category_name\n";
    continue;
  }
  $term = $term_storage->load(reset($tids));

  // Find image file
  $files = glob($image_dir . '/' . $file_prefix . '*');
  if (empty($files)) {
    echo "No image found for category: $category_name (Expected prefix: $file_prefix)\n";
    continue;
  }
  
  $image_path = $files[0];
  $file_name = basename($image_path);
  $destination = 'public://category_thumbnails/' . $file_name;

  // Prepare destination folder
  \Drupal::service('file_system')->prepareDirectory(dirname($destination), \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);

  // Copy file
  try {
    $uri = \Drupal::service('file_system')->copy($image_path, $destination, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
    
    // Create File entity
    $file = File::create([
      'uri' => $uri,
      'uid' => 1,
      'status' => 1,
    ]);
    $file->save();

    // Attach to category
    $term->set('field_category_thumbnail', ['target_id' => $file->id()]);
    $term->save();
    
    echo "Successfully imported thumbnail for Category: $category_name\n";
    $imported++;

  } catch (\Exception $e) {
    echo "Failed to copy image for $category_name: " . $e->getMessage() . "\n";
  }
}

echo "\nDone! Total Category thumbnails imported: $imported\n";
