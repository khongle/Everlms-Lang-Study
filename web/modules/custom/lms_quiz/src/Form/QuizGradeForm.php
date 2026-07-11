<?php

namespace Drupal\lms_quiz\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\lms_quiz\Entity\QuizResult;
use Drupal\lms_quiz\Event\QuizPassedEvent;
use Drupal\lms_quiz\Grading\GradingStrategyFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Instructor grading form for quiz responses pending manual review.
 *
 * Closes the WF-002 loop: essays / short answers left as "Pending Manual
 * Review" by GradingStrategyFactory can be scored here so the result finalises
 * to Passed/Failed and, on a pass, fires QuizPassedEvent for WF-003.
 */
class QuizGradeForm extends FormBase {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_quiz_grade_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?QuizResult $lms_quiz_result = NULL): array {
    $form_state->set('result_id', $lms_quiz_result->id());
    $quiz = $lms_quiz_result->get('field_quiz')->entity;
    $learner = $lms_quiz_result->getOwner();

    $form['summary'] = [
      '#type' => 'item',
      '#markup' => $this->t('Learner: <strong>@name</strong> — provisional score <strong>@score%</strong>. Score the responses below to finalise the result.', [
        '@name' => $learner?->getDisplayName() ?? $this->t('Learner'),
        '@score' => round((float) $lms_quiz_result->get('field_score')->value),
      ]),
    ];

    $has_pending = FALSE;
    foreach ($lms_quiz_result->get('field_responses') as $ref) {
      $response = $ref->entity;
      if (!$response || !(bool) $response->get('field_pending_review')->value) {
        continue;
      }
      $has_pending = TRUE;
      $rid = $response->id();
      $question = $response->get('field_question')->entity;
      $max = 0.0;
      if ($question) {
        $strategy = GradingStrategyFactory::getStrategy($question->bundle());
        if ($strategy) {
          $max = $strategy->getMaxScore($question);
        }
      }

      $form['r_' . $rid] = [
        '#type' => 'fieldset',
        '#title' => $question?->label() ?? $this->t('Question'),
      ];
      $form['r_' . $rid]['answer'] = [
        '#type' => 'item',
        '#title' => $this->t('Learner answer'),
        '#markup' => '<div class="lms-grade-answer" style="white-space:pre-wrap;padding:0.75rem;background:#f6f8fa;border-radius:0.5rem;">'
          . nl2br(htmlspecialchars((string) $response->get('field_text_answer')->value)) . '</div>',
      ];
      $form['r_' . $rid]['points_' . $rid] = [
        '#type' => 'number',
        '#title' => $this->t('Points awarded (max @max)', ['@max' => $max]),
        '#min' => 0,
        '#max' => $max,
        '#step' => 0.5,
        '#default_value' => (float) $response->get('field_score')->value,
        '#required' => TRUE,
      ];
      $form['r_' . $rid]['feedback_' . $rid] = [
        '#type' => 'textarea',
        '#title' => $this->t('Feedback to learner (optional)'),
        '#default_value' => $response->get('field_teacher_feedback')->value ?? '',
        '#rows' => 2,
      ];
      $form['r_' . $rid]['max_' . $rid] = ['#type' => 'value', '#value' => $max];
    }

    if (!$has_pending) {
      $form['none'] = [
        '#type' => 'item',
        '#markup' => $this->t('This submission has no responses pending manual review.'),
      ];
      return $form;
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Finalize grade'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $result = $this->entityTypeManager->getStorage('lms_quiz_result')->load($form_state->get('result_id'));
    if (!$result) {
      return;
    }
    $quiz = $result->get('field_quiz')->entity;
    $values = $form_state->getValues();

    // Apply instructor scores to the responses that were pending review.
    foreach ($result->get('field_responses') as $ref) {
      $response = $ref->entity;
      if (!$response || !(bool) $response->get('field_pending_review')->value) {
        continue;
      }
      $rid = $response->id();
      $max = (float) ($values['max_' . $rid] ?? 0);
      $points = max(0.0, min((float) ($values['points_' . $rid] ?? 0), $max));
      $response->set('field_score', $points);
      $response->set('field_correct', $max > 0 && $points >= $max);
      $response->set('field_teacher_feedback', $values['feedback_' . $rid] ?? '');
      $response->set('field_pending_review', 0);
      $response->save();
    }

    // Recompute the finalised percentage across every response.
    $total = 0.0;
    $max_total = 0.0;
    foreach ($result->get('field_responses') as $ref) {
      $response = $ref->entity;
      if (!$response) {
        continue;
      }
      $total += (float) $response->get('field_score')->value;
      $question = $response->get('field_question')->entity;
      if ($question) {
        $strategy = GradingStrategyFactory::getStrategy($question->bundle());
        if ($strategy) {
          $max_total += $strategy->getMaxScore($question);
        }
      }
    }
    $percentage = $max_total > 0 ? ($total / $max_total) * 100 : 0;
    $threshold = (float) $quiz->get('field_threshold')->value;
    $term_name = $percentage >= $threshold ? 'Passed' : 'Failed';

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $term_storage->loadByProperties(['vid' => 'quiz_results', 'name' => $term_name]);
    $term = reset($terms);
    if (!$term) {
      $term = $term_storage->create(['vid' => 'quiz_results', 'name' => $term_name]);
      $term->save();
    }

    $result->set('field_score', $percentage);
    $result->set('field_result', $term->id());
    $result->save();

    // Fire QuizPassedEvent for the LEARNER (result owner), not the grader.
    if ($term_name === 'Passed' && class_exists(QuizPassedEvent::class)) {
      $learner = $result->getOwner();
      $event = new QuizPassedEvent($result, $quiz, $learner);
      $this->eventDispatcher->dispatch($event, QuizPassedEvent::EVENT_NAME);
    }

    $this->messenger()->addStatus($this->t('Grading finalised: @name (@score%).', [
      '@name' => $term_name,
      '@score' => round($percentage),
    ]));
    $form_state->setRedirect('lms_quiz.result', ['lms_quiz_result' => $result->id()]);
  }

  /**
   * Access control: course admins, the course owner, or a listed instructor.
   */
  public function access(AccountInterface $account, QuizResult $lms_quiz_result) {
    if ($account->hasPermission('administer lms_course')) {
      return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($lms_quiz_result);
    }
    $quiz = $lms_quiz_result->get('field_quiz')->entity;
    $course = ($quiz && $quiz->hasField('field_course') && !$quiz->get('field_course')->isEmpty())
      ? $quiz->get('field_course')->entity
      : NULL;
    if ($course) {
      if ($course->hasField('uid') && (int) $course->get('uid')->target_id === (int) $account->id()) {
        return AccessResult::allowed()->cachePerUser()->addCacheableDependency($lms_quiz_result);
      }
      if ($course->hasField('field_instructor')) {
        foreach ($course->get('field_instructor') as $item) {
          if ((int) $item->target_id === (int) $account->id()) {
            return AccessResult::allowed()->cachePerUser()->addCacheableDependency($lms_quiz_result);
          }
        }
      }
    }
    return AccessResult::forbidden()->cachePerUser();
  }

}
