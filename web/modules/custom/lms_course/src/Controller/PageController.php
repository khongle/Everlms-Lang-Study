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
    $page_settings = $this->getPublicPageSettings();

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
      '#site_name' => $page_settings['site_name'],
      '#site_tagline' => $page_settings['site_tagline'],
      '#catalog_url' => $page_settings['catalog_url'],
      '#contact_url' => $page_settings['contact_url'],
      '#footer_phone' => $page_settings['footer_phone'],
      '#footer_email' => $page_settings['footer_email'],
    ];
  }

  /**
   * Pricing / Membership plans — matches 19-pricing.png.
   */
  public function pricing(): array {
    $pricing_copy = $this->getPricingPageCopy();

    // Load real membership plans from entity.
    $plan_ids = $this->entityTypeManager->getStorage('lms_membership_plan')
      ->getQuery()
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->sort('field_price', 'ASC')
      ->execute();
    $plans = $plan_ids
      ? $this->entityTypeManager->getStorage('lms_membership_plan')->loadMultiple($plan_ids)
      : [];

    $items = [];
    foreach ($plans as $plan) {
      $features = [];
      foreach ($plan->get('field_features') as $feature_item) {
        $features[] = $feature_item->value;
      }

      $items[] = [
        'id' => $plan->id(),
        'name' => $plan->label(),
        'price' => (float) $plan->get('field_price')->value,
        'currency' => 'USD',
        'billing_cycle' => $plan->get('field_billing_cycle')->value ?? 'monthly',
        'features' => implode("\n", $features),
        'recommended' => (bool) ($plan->get('field_recommended')->value ?? FALSE),
      ];
    }

    return [
      '#theme' => 'lms_pricing',
      '#plans' => $items,
      '#pricing_copy' => $pricing_copy,
    ];
  }

  /**
   * Builds editable copy for the public pricing page.
   */
  private function getPricingPageCopy(): array {
    $defaults = [
      'pricing_page_title' => (string) $this->t('Choose Your Plan'),
      'pricing_page_subtitle' => (string) $this->t('Get unlimited access to courses and certifications'),
      'pricing_recommended_badge_text' => (string) $this->t('Most Popular'),
      'pricing_free_price_text' => (string) $this->t('Free'),
      'pricing_month_label' => (string) $this->t('month'),
      'pricing_year_label' => (string) $this->t('year'),
      'pricing_lifetime_access_text' => (string) $this->t('lifetime access'),
      'pricing_paid_cta_text' => (string) $this->t('Get Started'),
      'pricing_free_cta_text' => (string) $this->t('Start Free'),
      'pricing_empty_state_title' => (string) $this->t('Pricing plans are being updated.'),
      'pricing_empty_state_body' => (string) $this->t('Membership plans will appear here once they are available in the system.'),
    ];

    $settings = [];
    foreach ($defaults as $key => $default) {
      $value = theme_get_setting($key, 'lms_theme');
      $settings[$key] = $value !== NULL && $value !== '' ? $value : $default;
    }

    return $settings;
  }

  /**
   * Contact page.
   */
  public function contact(): array {
    $page_settings = $this->getPublicPageSettings();

    return [
      '#theme' => 'lms_contact',
      '#site_name' => $page_settings['site_name'],
      '#site_tagline' => $page_settings['site_tagline'],
      '#footer_address' => $page_settings['footer_address'],
      '#footer_phone' => $page_settings['footer_phone'],
      '#footer_phone_href' => $page_settings['footer_phone_href'],
      '#footer_email' => $page_settings['footer_email'],
      '#footer_email_href' => $page_settings['footer_email_href'],
      '#footer_working_hours' => $page_settings['footer_working_hours'],
      '#footer_map_embed_url' => $page_settings['footer_map_embed_url'],
      '#catalog_url' => $page_settings['catalog_url'],
      '#pricing_url' => $page_settings['pricing_url'],
      '#register_url' => $page_settings['register_url'],
    ];
  }

  /**
   * Collects shared branding and contact settings for public pages.
   */
  private function getPublicPageSettings(): array {
    $site_config = $this->config('system.site');
    $site_name = trim((string) ($site_config->get('name') ?? '')) ?: 'EverLMS';

    $site_tagline = trim((string) (theme_get_setting('site_tagline', 'lms_theme') ?? ''));
    if ($site_tagline === '') {
      $site_tagline = trim((string) ($site_config->get('slogan') ?? ''));
    }
    if ($site_tagline === '') {
      $site_tagline = (string) $this->t('Flexible learning experiences built for modern teams.');
    }

    $footer_address = (string) (theme_get_setting('footer_address', 'lms_theme') ?? 'Company Headquarters, District');
    $footer_phone = (string) (theme_get_setting('footer_phone', 'lms_theme') ?? '+01 00 200 0000');
    $footer_email = (string) (theme_get_setting('footer_email', 'lms_theme') ?? ($site_config->get('mail') ?: 'hello@example.com'));
    $footer_working_hours = trim((string) (theme_get_setting('footer_working_hours', 'lms_theme') ?? ''));
    if ($footer_working_hours === '') {
      $footer_working_hours = (string) $this->t('Mon-Fri, 8:00 AM - 5:00 PM');
    }

    $footer_phone_href = 'tel:' . preg_replace('/[^0-9+]/', '', $footer_phone);
    $footer_email_href = 'mailto:' . $footer_email;

    return [
      'site_name' => $site_name,
      'site_tagline' => $site_tagline,
      'footer_address' => $footer_address,
      'footer_phone' => $footer_phone,
      'footer_phone_href' => $footer_phone_href,
      'footer_email' => $footer_email,
      'footer_email_href' => $footer_email_href,
      'footer_working_hours' => $footer_working_hours,
      'footer_map_embed_url' => (string) (theme_get_setting('footer_map_embed_url', 'lms_theme') ?? ''),
      'catalog_url' => Url::fromRoute('lms_course.catalog')->toString(),
      'contact_url' => Url::fromRoute('lms_course.contact')->toString(),
      'pricing_url' => Url::fromRoute('lms_course.pricing')->toString(),
      'register_url' => Url::fromRoute('user.register')->toString(),
    ];
  }

}
