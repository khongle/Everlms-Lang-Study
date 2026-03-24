<?php

namespace Drupal\lms_course\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a breadcrumb builder for LMS Course routes.
 */
class LmsCourseBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();
    return $route_name && (str_starts_with($route_name, 'lms_course.') || $route_name === 'entity.lms_course.canonical');
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);

    $links = [];
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $route_name = $route_match->getRouteName();

    if ($route_name === 'lms_course.catalog') {
      $links[] = Link::createFromRoute($this->t('Courses'), '<none>');
    }
    elseif ($route_name === 'lms_course.detail' || $route_name === 'entity.lms_course.canonical') {
      $links[] = Link::createFromRoute($this->t('Courses'), 'lms_course.catalog');
      $course = $route_match->getParameter('lms_course');
      if ($course) {
        $links[] = Link::createFromRoute($course->label(), '<none>');
      }
    }
    elseif ($route_name === 'lms_course.category') {
      $links[] = Link::createFromRoute($this->t('Categories'), '<none>');
    }
    elseif ($route_name === 'lms_course.category_detail') {
      $links[] = Link::createFromRoute($this->t('Courses'), 'lms_course.catalog');
      $term = $route_match->getParameter('taxonomy_term');
      if ($term) {
        $links[] = Link::createFromRoute($term->label(), '<none>');
      }
    }

    $breadcrumb->setLinks($links);
    return $breadcrumb;
  }

}
