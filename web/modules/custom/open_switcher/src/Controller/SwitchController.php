<?php

declare(strict_types=1);

namespace Drupal\open_switcher\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;

/**
 * Handles user switching (impersonation) and switching back.
 */
class SwitchController extends ControllerBase {

  public function __construct(
    protected SessionManagerInterface $sessionManager,
    protected RequestStack $requestStack,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('session_manager'),
      $container->get('request_stack'),
    );
  }

  /**
   * Switch to another user.
   */
  public function switchTo(int $user): RedirectResponse {
    $targetUser = $this->entityTypeManager()->getStorage('user')->load($user);
    if (!$targetUser) {
      $this->messenger()->addError($this->t('User not found.'));
      return new RedirectResponse(Url::fromRoute('open_switcher.frontend')->toString());
    }

    // Keep the existing self-switch warning without changing session state.
    if ((int) $targetUser->id() === (int) $this->currentUser()->id()) {
      $this->messenger()->addWarning($this->t('You are already logged in as this user.'));
      return new RedirectResponse(Url::fromRoute('open_switcher.frontend')->toString());
    }

    if (!self::isSwitchableAccount($targetUser)) {
      $this->messenger()->addError($this->t('Only active, non-administrator user accounts can be impersonated.'));
      return new RedirectResponse(Url::fromRoute('open_switcher.frontend')->toString());
    }

    // Preserve the first account when switching again during impersonation.
    $session = $this->requestStack->getCurrentRequest()->getSession();
    if (!$session->get('open_switcher_original_uid')) {
      $session->set('open_switcher_original_uid', (int) $this->currentUser()->id());
      $session->set('open_switcher_original_name', $this->currentUser()->getDisplayName());
    }
    $session->set('open_switcher_dialog', [
      'title' => (string) $this->t('Impersonation active'),
      'message' => (string) $this->t('You are now signed in as @name.', [
        '@name' => $targetUser->getDisplayName(),
      ]),
      'details' => (string) $this->t('Use "Exit Impersonation" in the header when you want to return to your account.'),
      'confirmText' => (string) $this->t('Continue'),
      'variant' => 'success',
    ]);

    $this->switchSession((int) $targetUser->id());
    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

  /**
   * Whether an account may be selected as an impersonation target.
   */
  public static function isSwitchableAccount(UserInterface $account): bool {
    if ((int) $account->id() <= 1 || !$account->isAuthenticated() || !$account->isActive() || $account->hasRole('administrator')) {
      return FALSE;
    }
    foreach (Role::loadMultiple($account->getRoles(TRUE)) as $role) {
      if ($role->isAdmin()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Switch back to the original user.
   */
  public function switchBack(): RedirectResponse {
    $request = $this->requestStack->getCurrentRequest();
    $session = $request && $request->hasSession() ? $request->getSession() : NULL;
    $originalUid = $session ? (int) $session->get('open_switcher_original_uid', 0) : 0;

    if (!$originalUid) {
      $this->messenger()->addError($this->t('No original session found. Cannot switch back.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // The original account may have been deleted or blocked during the switch.
    $originalAccount = $this->entityTypeManager()->getStorage('user')->load($originalUid);
    if (!$originalAccount || !$originalAccount->isAuthenticated() || !$originalAccount->isActive()) {
      user_logout();
      $this->messenger()->addWarning($this->t('Your original account is unavailable or blocked. You have been signed out. Please sign in again.'));
      return new RedirectResponse(Url::fromRoute('user.login')->toString());
    }

    $originalName = $originalAccount->getDisplayName();
    $this->switchSession((int) $originalAccount->id());
    $session->remove('open_switcher_original_uid');
    $session->remove('open_switcher_original_name');
    $session->set('open_switcher_dialog', [
      'title' => (string) $this->t('Back to your account'),
      'message' => (string) $this->t('You are signed back in as @name.', [
        '@name' => $originalName,
      ]),
      'details' => '',
      'confirmText' => (string) $this->t('Continue'),
      'variant' => 'neutral',
    ]);

    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

  /**
   * Custom access check for switching back.
   */
  public function canSwitchBack(): AccessResult {
    $request = $this->requestStack->getCurrentRequest();
    $originalUid = $request && $request->hasSession()
      ? $request->getSession()->get('open_switcher_original_uid')
      : NULL;
    return AccessResult::allowedIf(!empty($originalUid))->setCacheMaxAge(0);
  }

  /**
   * Perform the actual session switch.
   */
  protected function switchSession(int $uid): void {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->entityTypeManager()->getStorage('user')->load($uid);
    if (!$account) {
      throw new \RuntimeException('Cannot load user ' . $uid);
    }

    // Keep Drupal's session migration and login hooks when changing identity.
    user_login_finalize($account);
  }

}
