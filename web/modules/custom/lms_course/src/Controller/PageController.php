<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Static/public pages: About, Pricing, Contact.
 */
final class PageController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * About Us page — matches 19-about-us.png.
   */
  public function about(): array {
    return [
      '#theme' => 'lms_about',
      '#stats' => [
        ['value' => '10K+', 'label' => $this->t('Active Students')],
        ['value' => '200+', 'label' => $this->t('Expert Instructors')],
        ['value' => '500+', 'label' => $this->t('Online Courses')],
        ['value' => '95%', 'label' => $this->t('Satisfaction Rate')],
      ],
      '#team' => [
        ['name' => 'John Smith', 'role' => $this->t('CEO & Founder'), 'avatar' => ''],
        ['name' => 'Sarah Johnson', 'role' => $this->t('CTO'), 'avatar' => ''],
        ['name' => 'Michael Chen', 'role' => $this->t('Head of Content'), 'avatar' => ''],
        ['name' => 'Emily Davis', 'role' => $this->t('Lead Designer'), 'avatar' => ''],
      ],
    ];
  }

  /**
   * Pricing / Membership plans — matches 19-pricing.png.
   */
  public function pricing(): array {
    // Load real membership plans from entity.
    $plan_ids = $this->entityTypeManager->getStorage('lms_membership_plan')
      ->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    $plans = $plan_ids
      ? $this->entityTypeManager->getStorage('lms_membership_plan')->loadMultiple($plan_ids)
      : [];

    $items = [];
    foreach ($plans as $plan) {
      $items[] = [
        'id' => $plan->id(),
        'name' => $plan->label(),
        'price' => (float) $plan->get('field_price')->value,
        'duration' => $plan->get('field_duration_months')->value ?? 1,
        'features' => $plan->get('field_features')->value ?? '',
        'recommended' => (bool) ($plan->get('field_recommended')->value ?? FALSE),
      ];
    }

    // Fallback plans if no entities exist.
    if (empty($items)) {
      $items = [
        [
          'name' => 'Free',
          'price' => 0,
          'duration' => 0,
          'features' => "Access to free courses\nBasic quizzes\nCommunity forum",
          'recommended' => FALSE,
        ],
        [
          'name' => 'Pro',
          'price' => 19.99,
          'duration' => 1,
          'features' => "All free features\nUnlimited courses\nCertificates\nPriority support\nOffline downloads",
          'recommended' => TRUE,
        ],
        [
          'name' => 'Enterprise',
          'price' => 49.99,
          'duration' => 1,
          'features' => "All Pro features\nTeam management\nCustom branding\nAPI access\nDedicated support",
          'recommended' => FALSE,
        ],
      ];
    }

    return [
      '#theme' => 'lms_pricing',
      '#plans' => $items,
    ];
  }

  /**
   * Contact page.
   */
  public function contact(): array {
    return [
      '#theme' => 'lms_contact',
    ];
  }

}
