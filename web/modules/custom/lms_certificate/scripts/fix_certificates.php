<?php
// Drush script to generate mock certificates for existing courses.

use Drupal\user\Entity\User;
use Drupal\lms_course\Entity\Course;

// First update the entity type definitions if needed.
try {
  \Drupal::service('entity.definition_update_manager')->applyUpdates();
  echo "Applied entity definition updates if any.\n";
} catch (\Exception $e) {
  echo "Could not apply entity updates automatically. Reason: " . $e->getMessage() . "\n";
}

$userStorage = \Drupal::entityTypeManager()->getStorage('user');
$courseStorage = \Drupal::entityTypeManager()->getStorage('lms_course');
$certManager = \Drupal::service('lms_certificate.certificate_manager');

// Get all test students (e.g. not admin)
$uids = \Drupal::entityQuery('user')->condition('status', 1)->condition('uid', 1, '>')->accessCheck(FALSE)->execute();
if (empty($uids)) {
  echo "No active users found to issue certificates.\n";
  return;
}

// Get courses
$course_ids = \Drupal::entityQuery('lms_course')->condition('status', 1)->accessCheck(FALSE)->execute();
if (empty($course_ids)) {
  echo "No active courses found.\n";
  return;
}

$count = 0;
foreach ($course_ids as $cid) {
  $course = Course::load($cid);
  
  // Issue multiple certificates per course for testing dashboard
  for ($i = 0; $i < 3; $i++) {
    $uid = $uids[array_rand($uids)];
    $user = User::load($uid);

    echo "Attempting to issue certificate to {$user->getDisplayName()} for {$course->label()}...\n";
    $cert = $certManager->checkAndIssueCertificate($user, $course);
    
    if ($cert) {
      echo " -> Success! Certificate ID: " . $cert->id() . "\n";
      $count++;
    } else {
      echo " -> Skipped (already exists or no global template).\n";
    }
  }

  if ($count >= 20) {
    break;
  }
}

echo "Done issuing $count certificates.\n";
