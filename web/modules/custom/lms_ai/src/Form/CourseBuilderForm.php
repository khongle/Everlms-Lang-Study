<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_ai\Service\CourseBuilderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interactive wizard form for AI Course Building.
 */
class CourseBuilderForm extends FormBase {

  public function __construct(
    private readonly CourseBuilderService $courseBuilderService
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_ai.course_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_ai_course_builder_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['intro'] = [
      '#markup' => '<h2>✨ Build a Full Course with AI</h2><p>Describe the topic, and the AI engine will scaffold the entire course structure and queue up automated content generation.</p>',
    ];

    $form['topic'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Course Topic'),
      '#description' => $this->t('Example: Advanced Digital Marketing Strategies for 2024'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['audience'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target Audience'),
      '#description' => $this->t('Example: Beginners, Intermediate Developers, CEOs'),
      '#default_value' => 'Beginners',
      '#required' => TRUE,
    ];

    $form['tone'] = [
      '#type' => 'select',
      '#title' => $this->t('Tone of Voice'),
      '#options' => [
        'professional' => 'Professional & Academic',
        'casual' => 'Casual & Enthusiastic',
        'storytelling' => 'Storytelling & Anecdotal',
      ],
      '#default_value' => 'professional',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Build My Course! 🚀'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::generateAjax',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Architecting course structure... (up to 45s)'),
        ],
      ],
    ];

    return $form;
  }

  /**
   * AJAX submission handler.
   */
  public function generateAjax(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $topic = $form_state->getValue('topic');
    $audience = $form_state->getValue('audience');
    $tone = $form_state->getValue('tone');

    $course = $this->courseBuilderService->generateCourseStructure($topic, $audience, $tone);

    if ($course) {
      $message = $this->t('Success! Course "%title" scaffolded. Lessons will be generated in the background over the next few minutes.', ['%title' => $course->label()]);
      $this->messenger()->addStatus($message);
      
      // Redirect to the newly created course edit page so instructor can review.
      $url = $course->toUrl('edit-form')->toString();
      $response->addCommand(new RedirectCommand($url));
    } else {
      $response->addCommand(new MessageCommand('AI failed to generate the course. Please try again or check logs.', NULL, ['type' => 'error']));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Handled by AJAX entirely. Required by interface.
  }

}
