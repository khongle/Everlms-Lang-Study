<?php

declare(strict_types=1);

namespace Drupal\lms_lesson\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for Lesson entity.
 */
final class LessonForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type_selector = 'select[name="field_type"]';

    // Set correct weights & states for Video
    if (isset($form['field_video_url'])) {
      $form['field_video_url']['#weight'] = -6;
      $form['field_video_url']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'video']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }
    if (isset($form['field_video_file'])) {
      $form['field_video_file']['#weight'] = -6;
      $form['field_video_file']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'video']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }

    if (isset($form['field_quiz'])) {
      $form['field_quiz']['#weight'] = -6;
      $form['field_quiz']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'quiz']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }

    if (isset($form['field_h5p_embed'])) {
      $form['field_h5p_embed']['#weight'] = -6;
      $form['field_h5p_embed']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'h5p']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }

    // Capture standard configured H5P fields
    $h5p_variations = ['field_h5p', 'field_h5p_interactive_file', 'field_h5p_interactive', 'field_h5p_file', 'field_h5p_content'];
    foreach ($h5p_variations as $h5p_field) {
      if (isset($form[$h5p_field])) {
        $form[$h5p_field]['#weight'] = -6;
        $form[$h5p_field]['#states'] = [
          'visible' => [
            [$type_selector => ['value' => 'h5p']],
            [$type_selector => ['value' => 'mixed']],
          ],
        ];
      }
    }

    // Fallback: intercept ANY field with h5p in the name
    foreach (array_keys($form) as $key) {
      if (is_string($key) && strpos($key, 'h5p') !== false && !isset($form[$key]['#states'])) {
        $form[$key]['#weight'] = -6;
        $form[$key]['#states'] = [
          'visible' => [
            [$type_selector => ['value' => 'h5p']],
            [$type_selector => ['value' => 'mixed']],
          ],
        ];
      }
    }

    if (isset($form['field_scorm_package'])) {
      $form['field_scorm_package']['#weight'] = -6;
      $form['field_scorm_package']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'scorm']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }

    if (isset($form['field_pptx_attachment'])) {
      $form['field_pptx_attachment']['#weight'] = -6;
      $form['field_pptx_attachment']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'pptx']],
          [$type_selector => ['value' => 'mixed']],
        ],
      ];
    }

    if (isset($form['field_zoom_meeting_id'])) {
      $form['field_zoom_meeting_id']['#weight'] = -6;
      $form['field_zoom_meeting_id']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'zoom']],
        ],
      ];
    }

    if (isset($form['field_zoom_schedule'])) {
      $form['field_zoom_schedule']['#weight'] = -6;
      $form['field_zoom_schedule']['#states'] = [
        'visible' => [
          [$type_selector => ['value' => 'zoom']],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Lesson %label created.', ['%label' => $entity->label()])
        : $this->t('Lesson %label updated.', ['%label' => $entity->label()])
    );
    // Try to redirect to canonical or edit form instead of collection
    try {
      $form_state->setRedirectUrl($entity->toUrl('edit-form'));
    } catch (\Exception $e) {
      // Fallback
    }
    return $result;
  }

}
