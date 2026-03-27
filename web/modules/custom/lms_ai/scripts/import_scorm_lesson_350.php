<?php

use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;

/**
 * Script to generate SCORM package from scorm_lesson_350.json
 * and attach it to Lesson 350.
 * 
 * Run with: drush scr web/modules/custom/lms_ai/scripts/import_scorm_lesson_350.php
 */

function generateScormPackageForLesson350() {
  $module_path = \Drupal::service('extension.list.module')->getPath('lms_ai');
  $json_file = \Drupal::root() . '/' . $module_path . '/JSON/scorm_lesson_350.json';

  if (!file_exists($json_file)) {
    echo "Error: scorm_lesson_350.json not found.\n";
    return;
  }

  $data = json_decode(file_get_contents($json_file), TRUE);
  if (!$data || empty($data['slides'])) {
    echo "Error: Invalid JSON data.\n";
    return;
  }

  $lesson_id = $data['lesson_id'];
  $lesson_title = $data['lesson_title'];

  /** @var \Drupal\lms_ai\Service\ScormBuilderService $scorm_builder */
  $scorm_builder = \Drupal::service('lms_ai.scorm_builder');
  
  $zip_uri = $scorm_builder->buildScormFromSlides($data);

  if (!$zip_uri) {
    echo "Error: Failed to generate SCORM package.\n";
    return;
  }

  echo "SCORM Zip created at: $zip_uri\n";

  // 5. Create File Entity
  $file = File::create([
    'uri' => $zip_uri,
    'uid' => 1,
    'status' => \Drupal\file\FileInterface::STATUS_PERMANENT,
  ]);
  $file->save();
  echo "Managed File created. FID: {$file->id()}\n";

  // 6. Attach to Lesson 'Scorm Example'
  $entityTypeManager = \Drupal::entityTypeManager();
  $lessons = $entityTypeManager->getStorage('lms_lesson')->loadByProperties(['name' => 'Scorm Example']);
  $lesson = reset($lessons);
  
  if ($lesson) {
    if ($lesson->hasField('field_type') && $lesson->hasField('field_scorm_package')) {
        $lesson->set('field_type', 'scorm');
        $lesson->set('field_scorm_package', [
            'target_id' => $file->id(),
        ]);
        $lesson->save();
        echo "Successfully updated Lesson 'Scorm Example' (ID: " . $lesson->id() . ") with SCORM package!\n";
    } else {
        echo "Lesson structure does not match expected fields.\n";
    }
  } else {
    echo "Lesson 'Scorm Example' not found in the database.\n";
  }
}

generateScormPackageForLesson350();
echo "Done.\n";
