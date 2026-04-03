<?php

namespace Drupal\lms_rating;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Course Rating entities.
 */
class RatingListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['course'] = $this->t('Course');
    $header['author'] = $this->t('Author');
    $header['score'] = $this->t('Score');
    $header['review'] = $this->t('Review');
    $header['status'] = $this->t('Approved?');
    $header['featured'] = $this->t('Featured?');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\lms_rating\Entity\Rating $entity */
    $row['id'] = $entity->id();
    
    $course = $entity->get('field_course')->entity;
    $row['course'] = $course ? $course->toLink()->toString() : '-';

    $owner = $entity->getOwner();
    $row['author'] = $owner ? $owner->toLink()->toString() : '-';

    $row['score'] = $entity->get('field_score')->value;
    
    $review_text = $entity->get('field_review')->value;
    $row['review'] = mb_strlen((string) $review_text) > 50 ? mb_substr((string) $review_text, 0, 50) . '...' : $review_text;

    $is_approved = $entity->get('field_approved')->value;
    $row['status'] = $is_approved ? $this->t('Yes') : $this->t('No');

    $is_featured = $entity->hasField('field_is_featured') ? $entity->get('field_is_featured')->value : FALSE;
    $row['featured'] = $is_featured ? $this->t('Yes') : $this->t('No');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    $is_approved = $entity->get('field_approved')->value;
    $operations['toggle_approve'] = [
      'title' => $is_approved ? $this->t('Reject') : $this->t('Approve'),
      'weight' => 20,
      'url' => Url::fromRoute('lms_rating.toggle_approve', ['lms_rating' => $entity->id()]),
    ];

    $is_featured = $entity->hasField('field_is_featured') ? $entity->get('field_is_featured')->value : FALSE;
    $operations['toggle_featured'] = [
      'title' => $is_featured ? $this->t('Un-feature') : $this->t('Feature on Home'),
      'weight' => 25,
      'url' => Url::fromRoute('lms_rating.toggle_featured', ['lms_rating' => $entity->id()]),
    ];

    return $operations;
  }

}
