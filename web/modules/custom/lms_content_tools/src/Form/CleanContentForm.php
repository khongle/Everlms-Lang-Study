<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_content_tools\Service\ContentToolsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for cleaning content with 2-step confirmation.
 */
final class CleanContentForm extends FormBase {

  public function __construct(
    protected ContentToolsManager $contentToolsManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_content_tools.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_content_tools_clean_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $step = $form_state->get('step') ?? 1;
    $form_state->set('step', $step);

    $form['#attached']['library'][] = 'lms_content_tools/clean_confirm';

    if ($step === 1) {
      return $this->buildStep1($form, $form_state);
    }
    elseif ($step === 2) {
      return $this->buildStep2($form, $form_state);
    }
    else {
      return $this->buildStep3($form, $form_state);
    }
  }

  /**
   * Step 1: Select content groups to clean.
   */
  protected function buildStep1(array $form, FormStateInterface $form_state): array {
    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning"><h3>' . $this->t('WARNING') . '</h3><p>' . $this->t('This action will delete content from the system. This action CANNOT be undone.') . '</p></div>',
    ];

    $groups = $this->contentToolsManager->getEntityGroups();
    $form['groups'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select content types to clean'),
    ];

    foreach ($groups as $key => $group) {
      $count = $this->contentToolsManager->countGroup($key);
      $form['groups']['group_' . $key] = [
        '#type' => 'checkbox',
        '#title' => $this->t('@label (@count entities)', [
          '@label' => $group['label'],
          '@count' => number_format($count),
        ]),
        '#default_value' => TRUE,
        '#disabled' => $count === 0,
      ];
    }

    // Users and taxonomy - unchecked by default.
    $userCount = $this->contentToolsManager->countEntities('user') - 2; // Exclude uid 0,1.
    $form['groups']['group_users'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Users — except admin (@count users)', [
        '@count' => number_format(max(0, $userCount)),
      ]),
      '#default_value' => FALSE,
    ];

    $form['select_actions'] = [
      '#type' => 'container',
      '#attributes' => ['style' => 'margin: 10px 0;'],
    ];
    $form['select_actions']['select_all'] = [
      '#type' => 'button',
      '#value' => $this->t('Select All'),
      '#attributes' => ['class' => ['button--small'], 'onclick' => 'document.querySelectorAll("[id^=edit-group-]").forEach(function(c){if(!c.disabled)c.checked=true;}); return false;'],
    ];
    $form['select_actions']['deselect_all'] = [
      '#type' => 'button',
      '#value' => $this->t('Deselect All'),
      '#attributes' => ['class' => ['button--small'], 'onclick' => 'document.querySelectorAll("[id^=edit-group-]").forEach(function(c){c.checked=false;}); return false;'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('🗑 Clean Selected Content'),
      '#attributes' => ['class' => ['button--danger']],
    ];

    return $form;
  }

  /**
   * Step 2: Confirmation with summary — type CLEAN to confirm.
   */
  protected function buildStep2(array $form, FormStateInterface $form_state): array {
    $selected = $form_state->get('selected_groups') ?? [];
    $includeUsers = $form_state->get('include_users') ?? FALSE;

    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--error"><h3>' . $this->t('🔴 WARNING: This action CANNOT be undone') . '</h3></div>',
    ];

    // Build summary of what will be deleted.
    $rows = [];
    $totalCount = 0;
    $groups = $this->contentToolsManager->getEntityGroups();
    foreach ($selected as $groupKey) {
      if (isset($groups[$groupKey])) {
        $count = $this->contentToolsManager->countGroup($groupKey);
        $totalCount += $count;
        $rows[] = [$groups[$groupKey]['label'], number_format($count)];
      }
    }
    if ($includeUsers) {
      $userCount = max(0, $this->contentToolsManager->countEntities('user') - 2);
      $totalCount += $userCount;
      $rows[] = [$this->t('Users (except admin)'), number_format($userCount)];
    }

    $form['summary'] = [
      '#type' => 'table',
      '#header' => [$this->t('Content Type'), $this->t('Count')],
      '#rows' => $rows,
    ];

    $form['total'] = [
      '#type' => 'markup',
      '#markup' => '<p><strong>' . $this->t('Total: @count entities will be permanently deleted.', [
        '@count' => number_format($totalCount),
      ]) . '</strong></p>',
    ];

    $form['confirm_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type CLEAN below to confirm'),
      '#description' => $this->t('This field is case-sensitive. You must type exactly: CLEAN'),
      '#required' => TRUE,
      '#attributes' => [
        'autocomplete' => 'off',
        'data-clean-confirm' => 'true',
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('← Cancel'),
      '#submit' => ['::cancelForm'],
      '#limit_validation_errors' => [],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('🗑 Delete Permanently'),
      '#attributes' => [
        'class' => ['button--danger'],
        'data-clean-submit' => 'true',
        'disabled' => 'disabled',
      ],
    ];

    return $form;
  }

  /**
   * Step 3: Results after cleaning.
   */
  protected function buildStep3(array $form, FormStateInterface $form_state): array {
    $results = $form_state->get('clean_results') ?? [];
    $elapsed = $form_state->get('clean_elapsed') ?? 0;

    $form['success'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--status"><h3>' . $this->t('✅ Clean Content completed') . '</h3></div>',
    ];

    $rows = [];
    $totalDeleted = 0;
    foreach ($results as $label => $count) {
      $rows[] = [$label, number_format($count)];
      $totalDeleted += $count;
    }

    $form['results'] = [
      '#type' => 'table',
      '#header' => [$this->t('Content Type'), $this->t('Deleted')],
      '#rows' => $rows,
    ];

    $form['total'] = [
      '#type' => 'markup',
      '#markup' => '<p><strong>' . $this->t('Total: @count entities deleted in @time seconds.', [
        '@count' => number_format($totalDeleted),
        '@time' => number_format($elapsed, 1),
      ]) . '</strong></p>',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['back'] = [
      '#type' => 'link',
      '#title' => $this->t('← Back to Content Tools'),
      '#url' => \Drupal\Core\Url::fromRoute('lms_content_tools.dashboard'),
      '#attributes' => ['class' => ['button']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $step = $form_state->get('step');

    if ($step === 1) {
      // Check at least one group is selected.
      $groups = $this->contentToolsManager->getEntityGroups();
      $hasSelection = FALSE;
      foreach (array_keys($groups) as $key) {
        if ($form_state->getValue('group_' . $key)) {
          $hasSelection = TRUE;
          break;
        }
      }
      if (!$hasSelection && !$form_state->getValue('group_users')) {
        $form_state->setErrorByName('groups', $this->t('Please select at least one content type to clean.'));
      }
    }
    elseif ($step === 2) {
      $confirmText = $form_state->getValue('confirm_text');
      if ($confirmText !== 'CLEAN') {
        $form_state->setErrorByName('confirm_text', $this->t('You must type exactly CLEAN to confirm.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $step = $form_state->get('step');

    if ($step === 1) {
      // Collect selected groups and move to step 2.
      $groups = $this->contentToolsManager->getEntityGroups();
      $selected = [];
      foreach (array_keys($groups) as $key) {
        if ($form_state->getValue('group_' . $key)) {
          $selected[] = $key;
        }
      }
      $form_state->set('selected_groups', $selected);
      $form_state->set('include_users', (bool) $form_state->getValue('group_users'));
      $form_state->set('step', 2);
      $form_state->setRebuild();
    }
    elseif ($step === 2) {
      // Execute the clean operation.
      $selected = $form_state->get('selected_groups') ?? [];
      $includeUsers = $form_state->get('include_users') ?? FALSE;

      $startTime = microtime(TRUE);
      $results = [];
      $groups = $this->contentToolsManager->getEntityGroups();

      foreach ($selected as $groupKey) {
        if (isset($groups[$groupKey])) {
          $groupResults = $this->contentToolsManager->deleteGroup($groupKey);
          $groupTotal = array_sum($groupResults);
          $results[$groups[$groupKey]['label']] = $groupTotal;
        }
      }

      if ($includeUsers) {
        $currentUid = (int) $this->currentUser()->id();
        $userCount = $this->contentToolsManager->deleteUsers($currentUid);
        $results[$this->t('Users (except admin)')] = $userCount;
      }

      $elapsed = microtime(TRUE) - $startTime;

      // Log the operation.
      $totalDeleted = array_sum($results);
      \Drupal::logger('lms_content_tools')->warning('Content cleaned by @user: @count entities deleted. Groups: @groups', [
        '@user' => $this->currentUser()->getAccountName(),
        '@count' => $totalDeleted,
        '@groups' => implode(', ', $selected),
      ]);

      $form_state->set('clean_results', $results);
      $form_state->set('clean_elapsed', $elapsed);
      $form_state->set('step', 3);
      $form_state->setRebuild();
    }
  }

  /**
   * Cancel handler — go back to step 1.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state): void {
    $form_state->set('step', 1);
    $form_state->setRebuild();
  }

}
