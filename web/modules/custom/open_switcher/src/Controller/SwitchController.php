<?php

declare(strict_types=1);

namespace Drupal\open_switcher\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
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
      return new RedirectResponse(Url::fromRoute('open_switcher.switch_form')->toString());
    }

    // Cannot switch to user 1 (super admin) for security.
    if ((int) $targetUser->id() === 1 && (int) $this->currentUser()->id() !== 1) {
      $this->messenger()->addError($this->t('Cannot impersonate the super administrator.'));
      return new RedirectResponse(Url::fromRoute('open_switcher.switch_form')->toString());
    }

    // Cannot switch to yourself.
    if ((int) $targetUser->id() === (int) $this->currentUser()->id()) {
      $this->messenger()->addWarning($this->t('You are already logged in as this user.'));
      return new RedirectResponse(Url::fromRoute('open_switcher.switch_form')->toString());
    }

    // Store original user ID in session.
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $session->set('open_switcher_original_uid', (int) $this->currentUser()->id());
    $session->set('open_switcher_original_name', $this->currentUser()->getDisplayName());
    $session->set('open_switcher_dialog', [
      'title' => (string) $this->t('Impersonation active'),
      'message' => (string) $this->t('You are now signed in as @name.', [
        '@name' => $targetUser->getDisplayName(),
      ]),
      'details' => (string) $this->t('Use "Exit Impersonation" in the header when you want to return to your account.'),
      'confirmText' => (string) $this->t('Continue'),
      'variant' => 'success',
    ]);

    // Switch the session to the target user.
    $this->switchSession((int) $targetUser->id());

    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

  /**
   * Switch back to the original user.
   */
  public function switchBack(): RedirectResponse {
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $originalUid = $session->get('open_switcher_original_uid');
    $originalName = $session->get('open_switcher_original_name', '');

    if (!$originalUid) {
      $this->messenger()->addError($this->t('No original session found. Cannot switch back.'));
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Clear the switcher session data.
    $session->remove('open_switcher_original_uid');
    $session->remove('open_switcher_original_name');
    $session->set('open_switcher_dialog', [
      'title' => (string) $this->t('Back to your account'),
      'message' => (string) $this->t('You are signed back in as @name.', [
        '@name' => $originalName ?: (string) $this->t('your original account'),
      ]),
      'details' => '',
      'confirmText' => (string) $this->t('Continue'),
      'variant' => 'neutral',
    ]);

    // Switch session back to original user.
    $this->switchSession($originalUid);

    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

  /**
   * Custom access check for switching back.
   */
  public function canSwitchBack(): AccessResult {
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $originalUid = $session->get('open_switcher_original_uid');
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

    // Use Drupal's user.auth service  to switch identity.
    user_login_finalize($account);
  }

}
