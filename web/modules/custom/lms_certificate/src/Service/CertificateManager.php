<?php

namespace Drupal\lms_certificate\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_course\Entity\Course;
use Drupal\user\UserInterface;
use Drupal\lms_certificate\Entity\CertificateTemplate;

/**
 * Manages certificate issuance logic.
 */
class CertificateManager {

  protected CertificateGenerator $generator;
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(CertificateGenerator $generator, EntityTypeManagerInterface $entity_type_manager) {
    $this->generator = $generator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks and unconditionally issues a certificate if not already issued.
   */
  public function checkAndIssueCertificate(UserInterface $user, Course $course, $quiz_result = NULL) {
    $storage = $this->entityTypeManager->getStorage('lms_user_certificate');

    // Check if user already has a certificate for this course.
    $existing = $storage->getQuery()
      ->condition('uid', $user->id())
      ->condition('field_course', $course->id())
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($existing)) {
      return FALSE; // Already issued.
    }

    // Find the default template or a course-specific template.
    // For now, get the first active template.
    $templates = $this->entityTypeManager->getStorage('lms_certificate_template')
      ->loadByProperties(['status' => 1]);
    
    if (empty($templates)) {
      return FALSE; // No template available.
    }

    $template = reset($templates);

    // Generate unique code.
    $code = strtoupper(substr(md5($user->id() . '-' . $course->id() . '-' . time()), 0, 10));

    // Generate PDF.
    $file = $this->generator->generate($template, $user, $course, $code);

    if ($file) {
      // Save UserCertificate entity.
      $cert = $storage->create([
        'uid' => $user->id(),
        'field_template' => $template->id(),
        'field_course' => $course->id(),
        'field_quiz_result' => $quiz_result ? $quiz_result->id() : NULL,
        'field_certificate_number' => $code,
        'field_pdf_file' => $file->id(),
      ]);
      $cert->save();

      return $cert;
    }

    return FALSE;
  }

}
