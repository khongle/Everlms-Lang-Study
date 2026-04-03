<?php

declare(strict_types=1);

namespace Drupal\lms_rating\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a lms_rating form.
 */
class RatingForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new RatingForm.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_rating_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $course_id = NULL): array {
    // If no course_id was passed directly, try to get it from route context.
    if (!$course_id) {
      $course = $this->routeMatch->getParameter('lms_course');
      if ($course) {
        $course_id = is_object($course) ? $course->id() : $course;
      }
    }

    if (!$course_id) {
       return ['#markup' => $this->t('Unable to determine the course being rated.')];
    }
    
    // Check if the user already rated this course to prevent duplicates.
    $user_id = $this->currentUser()->id();
    if ($user_id != 0) {
       $existing_rating = $this->entityTypeManager->getStorage('lms_rating')
         ->getQuery()
         ->condition('uid', $user_id)
         ->condition('field_course', $course_id)
         ->accessCheck(FALSE)
         ->execute();
       
       if (!empty($existing_rating)) {
         return [
           '#type' => 'html_tag',
           '#tag' => 'div',
           '#attributes' => ['class' => ['alert', 'alert-success']],
           '#value' => $this->t('You have already submitted a rating for this course. Thank you!'),
         ];
       }
    }

    $form['course_id'] = [
      '#type' => 'hidden',
      '#value' => $course_id,
    ];

    $form['score'] = [
      '#type' => 'select',
      '#title' => $this->t('Rating (Score)'),
      '#options' => [
        '5' => $this->t('5 - Excellent'),
        '4' => $this->t('4 - Very Good'),
        '3' => $this->t('3 - Good'),
        '2' => $this->t('2 - Fair'),
        '1' => $this->t('1 - Poor'),
      ],
      '#default_value' => '5',
      '#required' => TRUE,
      '#attributes' => [
        'style' => 'background: #0d1117; color: #fff; border: 1px solid hsla(0,0%,100%,0.1); border-radius: 0.5rem; padding: 0.75rem;',
      ],
    ];

    $form['review'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your Review'),
      '#description' => $this->t('Please share your experience with this course.'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Outstanding explanations and very practical examples...'),
        'style' => 'background: #0d1117; color: #fff; border: 1px solid hsla(0,0%,100%,0.1); border-radius: 0.5rem; padding: 0.75rem; width: 100%; min-height: 120px;',
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['style' => 'margin-top: 1.5rem;'],
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Submit Review'),
        '#button_type' => 'primary',
        '#attributes' => [
          'class' => ['btn-lms-primary'],
          'style' => 'padding: 0.75rem 2rem; font-size: 1.05rem; border-radius: 0.5rem; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.2s ease;',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $course_id = $form_state->getValue('course_id');
    $score = $form_state->getValue('score');
    $review = $form_state->getValue('review');
    $uid = $this->currentUser()->id();

    try {
      $rating = $this->entityTypeManager->getStorage('lms_rating')->create([
        'uid' => $uid,
        'field_course' => $course_id,
        'field_score' => $score,
        'field_review' => $review,
        'field_approved' => FALSE, // Needs admin approval before showing on homepage.
        'field_is_featured' => FALSE,
      ]);
      $rating->save();

      $this->messenger()->addStatus($this->t('Thank you! Your review has been submitted and is pending moderation.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('There was an error saving your review. Please try again later.'));
      \Drupal::logger('lms_rating')->error($e->getMessage());
    }
  }

}
