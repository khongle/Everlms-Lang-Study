<?php

namespace Drupal\lms_certificate\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\lms_certificate\Entity\CertificateTemplate;
use Drupal\user\UserInterface;
use Drupal\lms_course\Entity\Course;

/**
 * Service to generate PDF certificates using mPDF.
 */
class CertificateGenerator {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected RendererInterface $renderer;
  protected FileSystemInterface $fileSystem;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, FileSystemInterface $file_system) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->fileSystem = $file_system;
  }

  /**
   * Generates a PDF certificate.
   *
   * @param \Drupal\lms_certificate\Entity\CertificateTemplate $template
   *   The certificate template.
   * @param \Drupal\user\UserInterface $user
   *   The user receiving the certificate.
   * @param \Drupal\lms_course\Entity\Course $course
   *   The course completed.
   * @param string $certificate_code
   *   The unique certificate code.
   *
   * @return \Drupal\file\FileInterface|null
   *   The saved PDF file entity, or NULL on failure.
   */
  public function generate(CertificateTemplate $template, UserInterface $user, Course $course, string $certificate_code) {
    if (!class_exists('\Mpdf\Mpdf')) {
      \Drupal::logger('lms_certificate')->error('mPDF library is missing. Please run composer require mpdf/mpdf.');
      return NULL;
    }

    $orientation = $template->get('field_orientation')->value === 'portrait' ? 'P' : 'L';
    
    // Prepare variables for the Twig template.
    $build = [
      '#theme' => 'certificate_pdf',
      '#template' => $template,
      '#user' => $user,
      '#course' => $course,
      '#code' => $certificate_code,
      '#date' => \Drupal::service('date.formatter')->format(\Drupal::time()->getRequestTime(), 'custom', 'F j, Y'),
    ];

    // Render HTML string.
    $html = (string) $this->renderer->renderPlain($build);

    try {
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => $orientation,
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
      ]);

      // Write HTML to PDF.
      $mpdf->WriteHTML($html);

      // Generate PDF content in memory.
      $pdf_content = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

      // Ensure directory exists.
      $directory = 'public://certificates/issued';
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

      // Build safe filename.
      $filename = 'Certificate_' . $certificate_code . '.pdf';
      $destination = $directory . '/' . $filename;

      // Save as managed file.
      /** @var \Drupal\file\FileRepositoryInterface $file_repository */
      $file_repository = \Drupal::service('file.repository');
      $file = $file_repository->writeData($pdf_content, $destination, FileSystemInterface::EXISTS_REPLACE);

      return $file;

    } catch (\Exception $e) {
      \Drupal::logger('lms_certificate')->error('Error generating PDF certificate: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

}
