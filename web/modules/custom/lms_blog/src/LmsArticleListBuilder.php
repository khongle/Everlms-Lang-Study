<?php

declare(strict_types=1);

namespace Drupal\lms_blog;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of LMS Article entities.
 */
class LmsArticleListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['status'] = $this->t('Status');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink($entity->label(), 'edit-form');
    $row['status'] = $entity->get('status')->value ? $this->t('Published') : $this->t('Draft');
    $row['created'] = \Drupal::service('date.formatter')->format($entity->get('created')->value, 'short');
    return $row + parent::buildRow($entity);
  }

}
