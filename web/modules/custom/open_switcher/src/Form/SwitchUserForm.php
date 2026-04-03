<?php

declare(strict_types=1);

namespace Drupal\open_switcher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\Role;

/**
 * Form for selecting a user to switch to — renders as filterable table.
 */
class SwitchUserForm extends FormBase {

  public function getFormId(): string {
    return 'open_switcher_switch_user_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $request = $this->getRequest();

    // Show current impersonation status.
    $session = $request->getSession();
    $originalUid = $session->get('open_switcher_original_uid');
    if ($originalUid) {
      $originalName = $session->get('open_switcher_original_name', 'Unknown');
      $form['status'] = [
        '#type' => 'markup',
        '#markup' => '<div class="messages messages--warning"><strong>' .
          $this->t('⚠️ You are currently impersonating another user. Original account: @name (uid: @uid)', [
            '@name' => $originalName,
            '@uid' => $originalUid,
          ]) . '</strong> — <a href="' . Url::fromRoute('open_switcher.switch_back')->toString() . '">' .
          $this->t('Switch Back') . '</a></div>',
      ];
    }

    // Role filter.
    $roles = Role::loadMultiple();
    $roleOptions = ['' => $this->t('— All roles —')];
    foreach ($roles as $role) {
      $rid = $role->id();
      // Exclude anonymous, authenticated (base), and administrator.
      if (in_array($rid, ['anonymous', 'authenticated', 'administrator'])) {
        continue;
      }
      $roleOptions[$rid] = $role->label();
    }

    $selectedRole = $request->query->get('role', '');

    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
    ];

    $form['filters']['role'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter by role'),
      '#options' => $roleOptions,
      '#default_value' => $selectedRole,
    ];

    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];
    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    // Build user query.
    $userStorage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('uid', [0, 1], 'NOT IN')
      ->condition('status', 1)
      ->sort('name', 'ASC');

    if (!empty($selectedRole)) {
      $query->condition('roles', $selectedRole);
    }

    // Exclude admin users.
    $adminIds = $userStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('roles', 'administrator')
      ->execute();
    if ($adminIds) {
      $query->condition('uid', $adminIds, 'NOT IN');
    }

    $query->pager(25);
    $uids = $query->execute();
    $users = $userStorage->loadMultiple($uids);

    // Build table.
    $form['users'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('UID'),
        $this->t('Username'),
        $this->t('Email'),
        $this->t('Roles'),
        $this->t('Status'),
        $this->t('Action'),
      ],
      '#empty' => $this->t('No users found matching the filter.'),
    ];

    $currentUid = (int) \Drupal::currentUser()->id();
    foreach ($users as $user) {
      $uid = (int) $user->id();
      $userRoles = $user->getRoles(TRUE); // Exclude 'authenticated'.
      $roleLabels = [];
      foreach ($userRoles as $rid) {
        $role = Role::load($rid);
        $roleLabels[] = $role ? $role->label() : $rid;
      }

      $form['users'][$uid]['uid'] = [
        '#markup' => $uid,
      ];
      $form['users'][$uid]['name'] = [
        '#markup' => $user->getDisplayName(),
      ];
      $form['users'][$uid]['email'] = [
        '#markup' => $user->getEmail() ?: '—',
      ];
      $form['users'][$uid]['roles'] = [
        '#markup' => implode(', ', $roleLabels) ?: '<em>' . $this->t('(authenticated only)') . '</em>',
      ];
      $form['users'][$uid]['status'] = [
        '#markup' => $user->isActive() ? '✅ Active' : '❌ Blocked',
      ];

      if ($uid === $currentUid) {
        $form['users'][$uid]['action'] = [
          '#markup' => '<em>' . $this->t('(current)') . '</em>',
        ];
      }
      else {
        $form['users'][$uid]['action'] = [
          '#type' => 'link',
          '#title' => $this->t('🔀 Switch'),
          '#url' => Url::fromRoute('open_switcher.switch_to', ['user' => $uid]),
          '#attributes' => [
            'class' => ['button', 'button--small', 'button--primary'],
            'onclick' => "return confirm('" . $this->t('Switch to @name?', ['@name' => $user->getDisplayName()]) . "');",
          ],
        ];
      }
    }

    $form['pager'] = [
      '#type' => 'pager',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $role = $form_state->getValue('role');
    $options = [];
    if (!empty($role)) {
      $options['query'] = ['role' => $role];
    }
    $form_state->setRedirectUrl(Url::fromRoute('open_switcher.switch_form', [], $options));
  }

}
