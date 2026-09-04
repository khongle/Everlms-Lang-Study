<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * List builder for Flashcard entities.
 */
final class CardListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Term');
    $header['deck'] = $this->t('Deck');
    $header['part_of_speech'] = $this->t('Part of Speech');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\lms_flashcard\Entity\Card $entity */
    $row['name'] = Link::createFromRoute(
      $entity->label() ?? '',
      'entity.lms_flashcard_card.canonical',
      ['lms_flashcard_card' => $entity->id()],
    );

    $deck = $entity->get('field_deck')->entity;
    $row['deck'] = $deck ? Link::createFromRoute(
      $deck->label() ?? '',
      'entity.lms_flashcard_deck.canonical',
      ['lms_flashcard_deck' => $deck->id()],
    ) : '—';

    $row['part_of_speech'] = $entity->get('field_part_of_speech')->value ?? '—';

    return $row + parent::buildRow($entity);
  }

}
