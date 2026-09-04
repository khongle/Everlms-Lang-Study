<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * List builder for Flashcard Deck entities.
 */
final class DeckListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Deck Name');
    $header['machine_name'] = $this->t('Machine Name');
    $header['topic'] = $this->t('Topic');
    $header['language'] = $this->t('Language');
    $header['cards'] = $this->t('Cards');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\lms_flashcard\Entity\Deck $entity */
    $row['name'] = Link::createFromRoute(
      $entity->label() ?? '',
      'entity.lms_flashcard_deck.canonical',
      ['lms_flashcard_deck' => $entity->id()],
    );

    $row['machine_name'] = $entity->get('field_machine_name')->value ?? '—';
    $topic = $entity->get('field_topic')->entity;
    $row['topic'] = $topic ? $topic->label() : '—';
    $row['language'] = $entity->get('field_language')->value ?? '—';
    $row['cards'] = $entity->get('field_cards')->count();
    $row['status'] = $entity->get('status')->value ? $this->t('Published') : $this->t('Draft');

    return $row + parent::buildRow($entity);
  }

}
