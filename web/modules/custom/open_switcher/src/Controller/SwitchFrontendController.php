<?php

declare(strict_types=1);

namespace Drupal\open_switcher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\user\Entity\Role;
use Symfony\Component\HttpFoundation\Request;

/**
 * Frontend controller for the Switch User page.
 */
class SwitchFrontendController extends ControllerBase {

  /**
   * Renders the Switch User page for the frontend theme.
   */
  public function page(Request $request): array {
    $currentUid = (int) $this->currentUser()->id();
    $session = $request->getSession();

    // Impersonation status.
    $originalUid = $session->get('open_switcher_original_uid');
    $originalName = $session->get('open_switcher_original_name', '');
    $switchBackUrl = $originalUid
      ? Url::fromRoute('open_switcher.switch_back')->toString()
      : NULL;

    // Role options for filter (exclude system roles).
    $roles = Role::loadMultiple();
    $roleOptions = [];
    foreach ($roles as $role) {
      $rid = $role->id();
      if (in_array($rid, ['anonymous', 'authenticated', 'administrator'])) {
        continue;
      }
      $roleOptions[$rid] = $role->label();
    }

    $selectedRole = $request->query->get('role', '');

    // Build user query.
    $userStorage = $this->entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('uid', [0, 1], 'NOT IN')
      ->condition('status', 1)
      ->sort('name', 'ASC');

    if (!empty($selectedRole)) {
      $query->condition('roles', $selectedRole);
    }

    // Exclude administrator-role users.
    $adminIds = $userStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('roles', 'administrator')
      ->execute();
    if ($adminIds) {
      $query->condition('uid', $adminIds, 'NOT IN');
    }

    $uids = $query->execute();
    $users = $userStorage->loadMultiple($uids);

    // Build user rows.
    $rows = [];
    foreach ($users as $user) {
      $uid = (int) $user->id();
      $userRoles = $user->getRoles(TRUE);
      $roleLabels = [];
      foreach ($userRoles as $rid) {
        $role = Role::load($rid);
        $roleLabels[] = $role ? $role->label() : $rid;
      }

      $rows[] = [
        'uid' => $uid,
        'name' => $user->getDisplayName(),
        'email' => $user->getEmail() ?: '—',
        'roles' => $roleLabels,
        'is_current' => ($uid === $currentUid),
        'switch_url' => Url::fromRoute('open_switcher.frontend_switch', ['user' => $uid])->toString(),
      ];
    }

    return [
      '#theme' => 'open_switcher_page',
      '#users' => $rows,
      '#role_options' => $roleOptions,
      '#selected_role' => $selectedRole,
      '#is_impersonating' => !empty($originalUid),
      '#original_name' => $originalName,
      '#original_uid' => $originalUid,
      '#switch_back_url' => $switchBackUrl,
      '#filter_url' => Url::fromRoute('open_switcher.frontend')->toString(),
      '#cache' => ['max-age' => 0],
    ];
  }

}
