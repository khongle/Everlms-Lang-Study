<?php

/**
 * Script to regenerate all existing LMS Certificates.
 * Usage: ddev drush php-script scripts/regenerate_all_certs.php (Local)
 *    or: drush php-script scripts/regenerate_all_certs.php (Server)
 */

$storage = \Drupal::entityTypeManager()->getStorage('lms_user_certificate');
$ids = $storage->getQuery()->accessCheck(FALSE)->execute();

if (empty($ids)) {
  echo "No certificates found to regenerate.\n";
  return;
}

$certs = $storage->loadMultiple($ids);
/** @var \Drupal\lms_certificate\Service\CertificateGenerator $generator */
$generator = \Drupal::service('lms_certificate.generator');

$success_count = 0;
$fail_count = 0;

echo "Found " . count($certs) . " certificates. Beginning regeneration...\n";

foreach ($certs as $cert) {
  $user = $cert->get('uid')->entity;
  $course = $cert->get('field_course')->entity;
  $template = $cert->get('field_template')->entity;
  $code = $cert->get('field_certificate_number')->value;

  if ($user && $course && $template && $code) {
    echo "- Generating new PDF for {$user->getDisplayName()} (Code: {$code})... ";
    $file = $generator->generate($template, $user, $course, $code);
    if ($file) {
      echo "[OK]\n";
      $success_count++;
    } else {
      echo "[FAILED]\n";
      $fail_count++;
    }
  } else {
    echo "- Skipping Cert ID {$cert->id()} due to missing referenced entities.\n";
  }
}

echo "\n--- Regeneration Complete ---\n";
echo "Success: {$success_count}\n";
echo "Failed: {$fail_count}\n";
