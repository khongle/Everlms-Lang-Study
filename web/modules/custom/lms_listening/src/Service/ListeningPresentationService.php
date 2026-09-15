<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Service;

use Drupal\lms_listening\Entity\ListeningPassage;

/**
 * Shapes a ListeningPassage's transcript/vocabulary for display — shared by
 * ListeningController::listen() (Active mode's HTML page) and the Passive
 * Listening flow (ListeningController::listenPassive() and
 * ListeningApiController::passiveNext(), see PASSIVE_LISTENING_PLAN.md),
 * which needs the exact same data shaped as plain JSON instead of a
 * server-rendered template. Factored out here rather than left inlined in
 * ListeningController so the two flows can never quietly drift apart.
 */
final class ListeningPresentationService {

  /**
   * Zips field_dialogue_speakers/field_dialogue_lines into one row per
   * turn — {speaker, line} pairs, in order, dropping any speaker entry
   * with no matching line at that position. Same logic ListeningController
   * ::listen() has always inlined; moved here verbatim.
   *
   * @return array<int, array{speaker: string, line: string}>
   */
  public function buildDialogueTurns(ListeningPassage $passage): array {
    $speakers = array_column($passage->get('field_dialogue_speakers')->getValue(), 'value');
    $lines = array_column($passage->get('field_dialogue_lines')->getValue(), 'value');

    $turns = [];
    foreach ($speakers as $index => $speaker) {
      if (isset($lines[$index])) {
        $turns[] = ['speaker' => $speaker, 'line' => $lines[$index]];
      }
    }
    return $turns;
  }

  /**
   * Builds the plain-JSON transcript payload Passive Listening speaks
   * aloud — unlike the Active Listen page (which renders full HTML and
   * reveals it client-side), Passive mode has no page to render for the
   * 2nd/3rd passage of a session (see js/listening-passive.js), so this
   * returns everything as data instead.
   *
   * @return array{format: string, body: string|null, dialogueTurns: array|null}
   *   For 'dialogue' format, `body` is NULL and `dialogueTurns` holds the
   *   zipped turns (see buildDialogueTurns()); for 'sentence'/'narrative',
   *   `dialogueTurns` is NULL and `body` holds the plain-text transcript
   *   (HTML tags stripped — field_body is a text_long field, but Passive
   *   mode only ever speaks it, never renders it as markup).
   */
  public function buildPassiveTranscript(ListeningPassage $passage): array {
    $format = (string) $passage->get('field_format')->value;
    if ($format === 'dialogue') {
      return ['format' => $format, 'body' => NULL, 'dialogueTurns' => $this->buildDialogueTurns($passage)];
    }
    return [
      'format' => $format,
      'body' => trim(strip_tags((string) $passage->get('field_body')->value)),
      'dialogueTurns' => NULL,
    ];
  }

  /**
   * The passage's optional native-language translation, plain-text (HTML
   * tags stripped, same treatment as buildPassiveTranscript()'s `body` —
   * field_translation is a text_long field, but this is only ever shown
   * as inert text, never rendered as markup) — or NULL when the passage
   * has none, so a caller can simply omit/hide the "Show translation"
   * control instead of showing an empty one.
   */
  public function buildTranslation(ListeningPassage $passage): ?string {
    $value = trim(strip_tags((string) $passage->get('field_translation')->value));
    return $value !== '' ? $value : NULL;
  }

  /**
   * The passage's linked vocabulary terms (Card::label() only — see
   * PASSIVE_LISTENING_PLAN.md's decision to read just the English term,
   * not translation/pronunciation/definition) in field_cards' stored
   * order.
   *
   * @return string[]
   */
  public function buildVocabTerms(ListeningPassage $passage): array {
    $terms = [];
    foreach ($passage->get('field_cards')->referencedEntities() as $card) {
      $terms[] = (string) $card->label();
    }
    return $terms;
  }

}
