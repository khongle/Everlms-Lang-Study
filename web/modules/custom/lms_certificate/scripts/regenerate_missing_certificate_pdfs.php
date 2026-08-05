<?php

/**
 * Regenerate missing certificate PDFs and attach them to user certificates.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/lms_certificate/scripts/regenerate_missing_certificate_pdfs.php
 */

use Drupal\Core\File\FileSystemInterface;

$entityTypeManager = \Drupal::entityTypeManager();
$fileSystem = \Drupal::service('file_system');
$generator = \Drupal::service('lms_certificate.generator');

$storage = $entityTypeManager->getStorage('lms_user_certificate');
$ids = \Drupal::entityQuery('lms_user_certificate')
  ->accessCheck(FALSE)
  ->execute();

$checked = 0;
$generated = 0;
$alreadyValid = 0;
$skipped = 0;
$failed = [];

foreach ($storage->loadMultiple($ids) as $certificate) {
  $checked++;

  $existingFile = $certificate->get('field_pdf_file')->entity;
  if ($existingFile) {
    $existingPath = $fileSystem->realpath($existingFile->getFileUri());
    if ($existingPath && file_exists($existingPath)) {
      $alreadyValid++;
      continue;
    }
  }

  if ($certificate->get('field_revoked')->value) {
    $skipped++;
    continue;
  }

  $user = $certificate->get('uid')->entity;
  $course = $certificate->get('field_course')->entity;
  $template = $certificate->get('field_template')->entity;
  $code = (string) $certificate->get('field_certificate_number')->value;

  if (!$user || !$course || !$template || $code === '') {
    $skipped++;
    $failed[] = [
      'id' => $certificate->id(),
      'code' => $code,
      'reason' => 'missing user/course/template/code',
    ];
    continue;
  }

  $file = $generator->generate($template, $user, $course, $code);
  if (!$file) {
    $failed[] = [
      'id' => $certificate->id(),
      'code' => $code,
      'reason' => 'generator returned empty file',
    ];
    continue;
  }

  $certificate->set('field_pdf_file', ['target_id' => $file->id()]);
  $certificate->save();
  $generated++;
}

echo json_encode([
  'checked' => $checked,
  'already_valid' => $alreadyValid,
  'generated' => $generated,
  'skipped' => $skipped,
  'failed' => $failed,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
