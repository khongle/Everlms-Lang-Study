<?php

/**
 * Report whether issued certificates have attached PDF files.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/lms_certificate/scripts/check_certificate_pdf_status.php
 */

$entityTypeManager = \Drupal::entityTypeManager();
$fileSystem = \Drupal::service('file_system');
$storage = $entityTypeManager->getStorage('lms_user_certificate');
$ids = \Drupal::entityQuery('lms_user_certificate')
  ->accessCheck(FALSE)
  ->execute();

$total = 0;
$withPdfEntity = 0;
$fileExists = 0;
$missing = [];

foreach ($storage->loadMultiple($ids) as $certificate) {
  $total++;
  $file = $certificate->get('field_pdf_file')->entity;

  if (!$file) {
    $missing[] = [
      'id' => $certificate->id(),
      'code' => $certificate->get('field_certificate_number')->value,
      'reason' => 'no field_pdf_file',
    ];
    continue;
  }

  $withPdfEntity++;
  $path = $fileSystem->realpath($file->getFileUri());
  if ($path && file_exists($path)) {
    $fileExists++;
    continue;
  }

  $missing[] = [
    'id' => $certificate->id(),
    'code' => $certificate->get('field_certificate_number')->value,
    'reason' => 'file entity exists but file missing',
    'uri' => $file->getFileUri(),
  ];
}

echo json_encode([
  'total' => $total,
  'with_pdf_entity' => $withPdfEntity,
  'file_exists' => $fileExists,
  'missing_count' => count($missing),
  'missing_sample' => array_slice($missing, 0, 20),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
