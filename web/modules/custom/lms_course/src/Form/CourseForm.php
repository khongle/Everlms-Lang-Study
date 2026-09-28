<?php

declare(strict_types=1);

namespace Drupal\lms_course\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Render\Element;

/**
 * Form handler for Course entity add/edit.
 */
final class CourseForm extends ContentEntityForm {

  /**
   * Keeps curriculum widgets inline before Drupal instantiates their plugins.
   *
   * This changes the runtime display only; it does not save configuration.
   */
  public static function prepareInlineFormDisplay(EntityFormDisplayInterface $display, array $context): void {
    if (!self::isWorkspaceCourseEditor()) {
      return;
    }
    $fields = ['lms_course' => 'field_chapters', 'lms_chapter' => 'field_lessons'];
    $field_name = $fields[$context['entity_type'] ?? ''] ?? NULL;
    if (!$field_name || !($component = $display->getComponent($field_name))) {
      return;
    }
    if (($component['type'] ?? '') !== 'inline_entity_form_complex') {
      return;
    }
    unset($component['third_party_settings']['ief_popup']);
    $component['settings']['override_labels'] = TRUE;
    $component['settings']['label_singular'] = $field_name === 'field_chapters' ? 'chapter' : 'lesson';
    $component['settings']['label_plural'] = $field_name === 'field_chapters' ? 'chapters' : 'lessons';
    $display->setComponent($field_name, $component);
  }

  /**
   * Limits workspace presentation changes to the course create/edit routes.
   */
  private static function isWorkspaceCourseEditor(): bool {
    return in_array(\Drupal::routeMatch()->getRouteName(), [
      'lms_course.admin.course.edit',
      'lms_course.admin_course_create',
    ], TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    self::prepareInlineFormDisplay($this->getFormDisplay($form_state), ['entity_type' => 'lms_course']);
    $form = parent::form($form, $form_state);
    if (!self::isWorkspaceCourseEditor()) {
      return $form;
    }
    $form['#attributes']['class'][] = 'lms-course-editor';
    $form['#attached']['library'][] = 'lms_theme/course_editor';
    $form['#attached']['library'][] = 'lms_lesson/lesson_form_states';
    $form['#after_build'][] = [self::class, 'decorateCurriculum'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (self::isWorkspaceCourseEditor()) {
      $actions['submit']['#value'] = $this->t('Save course');
    }
    return $actions;
  }

  /**
   * Adds visible inline editor labels without changing IEF submit handlers.
   */
  public static function decorateCurriculum(array $form, FormStateInterface $form_state): array {
    self::decorateInlineElements($form);
    return $form;
  }

  /**
   * Labels nested forms after IEF has built its Update and Cancel actions.
   */
  private static function decorateInlineElements(array &$element): void {
    $entity_type = $element['#entity_type'] ?? '';
    if (($element['#type'] ?? '') === 'inline_entity_form' && in_array($entity_type, ['lms_chapter', 'lms_lesson'], TRUE)) {
      $is_chapter = $entity_type === 'lms_chapter';
      $operation = $element['#op'] ?? 'edit';
      $label = (string) ($element['#entity']->label() ?? '');
      $element['#attributes']['class'][] = 'lms-course-inline-editor';
      $element['#attributes']['data-course-inline-type'] = $is_chapter ? 'chapter' : 'lesson';
      $element['#attributes']['data-course-inline-operation'] = $operation;
      $element['editor_heading'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $operation === 'add'
          ? ($is_chapter ? t('New chapter') : t('New lesson'))
          : ($label === ''
            ? ($is_chapter ? t('Edit chapter') : t('Edit lesson'))
            : ($is_chapter ? t('Edit chapter: @title', ['@title' => $label]) : t('Edit lesson: @title', ['@title' => $label]))),
        '#attributes' => ['class' => ['lms-course-inline-editor__heading']],
        '#weight' => -100,
      ];
      if (isset($element['actions'])) {
        $element['actions']['#attributes']['class'][] = 'lms-course-inline-editor__actions';
        foreach (Element::children($element['actions']) as $key) {
          $action = &$element['actions'][$key];
          if (str_ends_with($key, '_cancel')) {
            $action['#attributes']['class'][] = 'lms-course-inline-cancel';
          }
          elseif (str_ends_with($key, '_save')) {
            $action['#attributes']['class'][] = 'lms-course-inline-update';
          }
        }
      }
    }
    foreach (Element::children($element) as $key) {
      self::decorateInlineElements($element[$key]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();
    $message_args = ['%label' => $entity->label()];

    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Course %label has been created.', $message_args)
        : $this->t('Course %label has been updated.', $message_args)
    );

    $route_name = \Drupal::routeMatch()->getRouteName();
    if (is_string($route_name) && str_starts_with($route_name, 'lms_course.admin')) {
      $form_state->setRedirect('lms_course.admin_courses');
    }
    else {
      $form_state->setRedirectUrl($entity->toUrl('collection'));
    }

    return $result;
  }

}
