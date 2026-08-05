<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Settings form for instructor workspace content overrides.
 */
final class InstructorWorkspaceSettingsForm extends ConfigFormBase {

  /**
   * Scenario labels used in the config form.
   *
   * @var array<string, string>
   */
  private const SCENARIOS = [
    'no_courses_assigned' => 'No courses assigned',
    'courses_no_enrollments' => 'Courses assigned but no enrollments',
    'attention_needed' => 'Attention needed',
    'multi_course_operator' => 'Multi-course operator',
    'healthy_teaching' => 'Healthy teaching',
  ];

  /**
   * Restricts access to managers and administrators only.
   */
  public static function access(AccountInterface $account): AccessResult {
    $roles = $account->getRoles();
    $allowed = in_array('manager', $roles, TRUE) || in_array('administrator', $roles, TRUE);

    return AccessResult::allowedIf($allowed)->addCacheContexts(['user.roles']);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_progress_instructor_workspace_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lms_progress.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('lms_progress.settings');

    $form['intro'] = [
      '#type' => 'item',
      '#markup' => '<p>' . $this->t('Override scenario-based dashboard copy for the instructor workspace. Leave a field blank to keep the default system copy. Supported placeholders: @name, @courses, @students, @active_students, @average_progress, @completion_rate, @enrollments, @count.', [
        '@name' => '@name',
        '@courses' => '@courses',
        '@students' => '@students',
        '@active_students' => '@active_students',
        '@average_progress' => '@average_progress',
        '@completion_rate' => '@completion_rate',
        '@enrollments' => '@enrollments',
        '@count' => '@count',
      ]) . '</p>',
    ];

    foreach (self::SCENARIOS as $scenario => $label) {
      $scenario_config = $config->get("scenario_overrides.$scenario") ?? [];

      $form[$scenario] = [
        '#type' => 'details',
        '#title' => $this->t($label),
        '#open' => $scenario === 'attention_needed',
      ];

      $form[$scenario]['hero'] = [
        '#type' => 'details',
        '#title' => $this->t('Hero copy'),
        '#open' => FALSE,
      ];
      $form[$scenario]['hero']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Hero title'),
        '#default_value' => $scenario_config['hero']['title'] ?? '',
      ];
      $form[$scenario]['hero']['subtitle'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Hero subtitle'),
        '#default_value' => $scenario_config['hero']['subtitle'] ?? '',
        '#rows' => 3,
      ];

      $form[$scenario]['spotlight'] = [
        '#type' => 'details',
        '#title' => $this->t('Spotlight copy'),
        '#open' => FALSE,
      ];
      $form[$scenario]['spotlight']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Spotlight title'),
        '#default_value' => $scenario_config['spotlight']['title'] ?? '',
      ];
      $form[$scenario]['spotlight']['copy'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Spotlight description'),
        '#default_value' => $scenario_config['spotlight']['copy'] ?? '',
        '#rows' => 4,
      ];

      $form[$scenario]['queue'] = [
        '#type' => 'details',
        '#title' => $this->t('Course queue copy'),
        '#open' => FALSE,
      ];
      $form[$scenario]['queue']['subtitle'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Queue subtitle'),
        '#default_value' => $scenario_config['queue']['subtitle'] ?? '',
        '#rows' => 3,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('lms_progress.settings');
    $scenario_overrides = [];

    foreach (array_keys(self::SCENARIOS) as $scenario) {
      $values = (array) $form_state->getValue($scenario);
      $scenario_overrides[$scenario] = [
        'hero' => [
          'title' => trim((string) (($values['hero']['title'] ?? ''))),
          'subtitle' => trim((string) (($values['hero']['subtitle'] ?? ''))),
        ],
        'spotlight' => [
          'title' => trim((string) (($values['spotlight']['title'] ?? ''))),
          'copy' => trim((string) (($values['spotlight']['copy'] ?? ''))),
        ],
        'queue' => [
          'subtitle' => trim((string) (($values['queue']['subtitle'] ?? ''))),
        ],
      ];
    }

    $config->set('scenario_overrides', $scenario_overrides)->save();
    parent::submitForm($form, $form_state);
  }

}
