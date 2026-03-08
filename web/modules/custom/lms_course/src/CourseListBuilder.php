<?php

declare(strict_types=1);

namespace Drupal\lms_course;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * List builder for Course entities.
 */
final class CourseListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Course Name');
    $header['category'] = $this->t('Category');
    $header['level'] = $this->t('Level');
    $header['price'] = $this->t('Price');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\lms_course\Entity\Course $entity */
    $row['name'] = Link::createFromRoute(
      $entity->label() ?? '',
      'entity.lms_course.canonical',
      ['lms_course' => $entity->id()],
    );

    // Category.
    $category = $entity->get('field_category')->entity;
    $row['category'] = $category ? $category->label() : '—';

    // Level.
    $level = $entity->get('field_level')->entity;
    $row['level'] = $level ? $level->label() : '—';

    // Price.
    $price = (float) $entity->get('field_price')->value;
    $row['price'] = $price > 0 ? '$' . number_format($price, 2) : $this->t('Free');

    // Status.
    $row['status'] = $entity->get('status')->value ? $this->t('Published') : $this->t('Draft');

    return $row + parent::buildRow($entity);
  }

}
