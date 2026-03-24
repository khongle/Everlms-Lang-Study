<?php

namespace Drupal\lms_certificate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CertificateAdminController for the Manager UI Dashboard.
 */
class CertificateAdminController extends ControllerBase {


  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function adminDashboard() {
    $storage = $this->entityTypeManager->getStorage('lms_user_certificate');
    
    // Quick Stats
    $total_issued = $storage->getQuery()->accessCheck(FALSE)->count()->execute();
    $total_revoked = $storage->getQuery()->condition('field_revoked', 1)->accessCheck(FALSE)->count()->execute();
    
    $week_ago = \Drupal::time()->getRequestTime() - (7 * 24 * 60 * 60);
    $recent_issued = $storage->getQuery()->condition('field_issued_at', $week_ago, '>')->accessCheck(FALSE)->count()->execute();

    // Load certificates (simple pagination can be added via pager extension, but we'll load top 50 for now)
    $ids = $storage->getQuery()
      ->sort('field_issued_at', 'DESC')
      ->range(0, 50)
      ->accessCheck(FALSE)
      ->execute();
    $certificates = $storage->loadMultiple($ids);

    $items = [];
    foreach ($certificates as $cert) {
      $user = $cert->get('uid')->entity;
      $course = $cert->get('field_course')->entity;
      $is_revoked = (bool) $cert->get('field_revoked')->value;
      
      $items[] = [
        'id' => $cert->id(),
        'user_name' => $user ? $user->getDisplayName() : 'Unknown',
        'course_name' => $course ? $course->label() : 'Unknown',
        'issue_date' => \Drupal::service('date.formatter')->format($cert->get('field_issued_at')->value, 'custom', 'M j, Y'),
        'code' => $cert->get('field_certificate_number')->value,
        'status' => $is_revoked ? 'Revoked' : 'Valid',
        'pdf_url' => $cert->get('field_pdf_file')->entity ? \Drupal::service('file_url_generator')->generateAbsoluteString($cert->get('field_pdf_file')->entity->getFileUri()) : '#',
        'revoke_url' => \Drupal\Core\Url::fromRoute('lms_certificate.revoke', ['lms_user_certificate' => $cert->id()])->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_certificates',
      '#stats' => [
        'total_issued' => $total_issued,
        'revoked' => $total_revoked,
        'recent' => $recent_issued,
      ],
      '#certificates' => $items,
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

  public function adminTemplates() {
    $storage = $this->entityTypeManager->getStorage('lms_certificate_template');
    $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
    $templates = $storage->loadMultiple($ids);

    $items = [];
    foreach ($templates as $template) {
      $items[] = [
        'id' => $template->id(),
        'name' => $template->label(),
        'status' => $template->hasField('status') && $template->get('status')->value ? 'Active' : 'Disabled',
        'created' => $template->hasField('created') && $template->get('created')->value ? \Drupal::service('date.formatter')->format($template->get('created')->value, 'custom', 'M j, Y') : 'N/A',
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_certificate.admin_template.edit', ['lms_certificate_template' => $template->id()])->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_certificate_templates',
      '#templates' => $items,
      '#add_template_url' => \Drupal\Core\Url::fromRoute('lms_certificate.admin_template.add')->toString(),
    ];
  }

  public function templateAddForm(): array {
    $entity = $this->entityTypeManager->getStorage('lms_certificate_template')->create();
    return $this->buildTemplateForm($entity, 'Add Certificate Template');
  }

  public function templateEditForm(\Drupal\Core\Entity\EntityInterface $lms_certificate_template): array {
    return $this->buildTemplateForm($lms_certificate_template, 'Edit Certificate Template: ' . $lms_certificate_template->label());
  }

  private function buildTemplateForm(\Drupal\Core\Entity\EntityInterface $template, string $title): array {
    $form = \Drupal::service('entity.form_builder')->getForm($template, 'default');
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('@title', ['@title' => $title]),
      '#active_menu' => 'certificate_templates',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

}
