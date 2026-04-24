<?php

declare(strict_types=1);

namespace Drupal\lms_mobile_api\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_mobile_api\Service\OtpService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles all mobile authentication endpoints for the EverLMS student app.
 *
 * Endpoints provided:
 *   POST /api/v1/user/register
 *   POST /api/v1/user/register/verify-otp
 *   POST /api/v1/user/register/resend-otp
 *   POST /api/v1/user/forgot-password
 *   POST /api/v1/user/forgot-password/verify-otp
 *   POST /api/v1/user/reset-password
 *   POST /api/v1/oauth/token
 *   GET  /api/v1/user/profile
 *   POST /api/v1/user/change-password
 */
class MobileAuthController extends ControllerBase {

  public function __construct(
    protected OtpService $otpService,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ConfigFactoryInterface $configFactory,
    protected LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_mobile_api.otp_service'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('logger.factory')->get('lms_mobile_api'),
    );
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/register
  // ---------------------------------------------------------------------------

  /**
   * Registers a new user (blocked) and sends an OTP verification email.
   *
   * Request body (JSON):
   *   name            string  Display name (optional; derived from email if absent)
   *   email           string  required
   *   password        string  required, min 8 chars
   *   password_confirm string required, must match password
   *
   * Response 200:
   *   status      "otp_sent"
   *   message     string
   *   otp_token   string  Session token to use with verify-otp / resend-otp
   *
   * Response 422: validation errors
   */
  public function register(Request $request): JsonResponse {
    $data = $this->decodeJson($request);

    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $password_confirm = $data['password_confirm'] ?? '';

    // --- Validate input ---
    $violations = [];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $violations[] = ['field' => 'email', 'message' => 'A valid email address is required.'];
    }
    if (mb_strlen($password) < 8) {
      $violations[] = ['field' => 'password', 'message' => 'Password must be at least 8 characters.'];
    }
    if ($password !== $password_confirm) {
      $violations[] = ['field' => 'password_confirm', 'message' => 'Passwords do not match.'];
    }
    if (!empty($violations)) {
      return $this->errorResponse('validation_error', 'Validation failed.', 422, $violations);
    }

    $user_storage = $this->entityTypeManager->getStorage('user');

    // Reject if an active account already uses this email.
    if (!empty($user_storage->loadByProperties(['mail' => $email, 'status' => 1]))) {
      return $this->errorResponse('validation_error', 'Validation failed.', 422, [
        ['field' => 'email', 'message' => 'This email address is already registered.'],
      ]);
    }

    // Remove stale pending registrations for the same email.
    foreach ($user_storage->loadByProperties(['mail' => $email, 'status' => 0]) as $stale) {
      $stale->delete();
    }

    // Derive a unique username.
    $base_name = !empty($name) ? $name : explode('@', $email)[0];
    $username = $this->ensureUniqueUsername($base_name);

    // Create user in blocked state; role 'student' is auto-assigned by
    // lms_base_user_insert() hook if present, otherwise we set it explicitly.
    /** @var \Drupal\user\UserInterface $user */
    $user = $user_storage->create([
      'name' => $username,
      'mail' => $email,
      'pass' => $password,
      'status' => 0,
      'roles' => ['student'],
    ]);
    $user->save();

    $config = $this->configFactory->get('lms_mobile_api.settings');
    $otp_expiry = (int) ($config->get('otp_expiry_seconds') ?: 900);

    $otp_code = $this->otpService->generateOtpCode();
    $otp_token = $this->otpService->generateToken();

    // Store session data including the temporary plaintext password so that
    // an OAuth2 token can be issued internally after OTP verification.
    $this->otpService->storeOtpSession($otp_token, [
      'uid' => (int) $user->id(),
      'email' => $email,
      'otp_code' => $otp_code,
      'password' => $password,
      'type' => 'register',
    ], $otp_expiry);

    $this->otpService->sendOtpEmail($email, $otp_code, 'register');

    return new JsonResponse([
      'status' => 'otp_sent',
      'message' => 'A verification code has been sent to your email.',
      'otp_token' => $otp_token,
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/register/verify-otp
  // ---------------------------------------------------------------------------

  /**
   * Verifies the registration OTP, activates the user, and issues OAuth tokens.
   *
   * Request body (JSON):
   *   otp_token  string  required  The token returned by /register
   *   otp_code   string  required  6-digit code from email
   *
   * Response 200: OAuth2 token response (access_token, refresh_token …)
   *              or {status: "verified", message: "…"} when OAuth config is missing.
   * Response 400: invalid / expired OTP
   */
  public function verifyRegisterOtp(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $otp_token = trim($data['otp_token'] ?? '');
    $otp_code = trim($data['otp_code'] ?? '');

    if (empty($otp_token) || empty($otp_code)) {
      return $this->errorResponse('invalid_otp', 'OTP token and code are required.');
    }

    $session = $this->otpService->getOtpSession($otp_token);
    if (!$session || ($session['type'] ?? '') !== 'register') {
      return $this->errorResponse('invalid_otp', 'The code is incorrect or has expired.');
    }
    if ($session['otp_code'] !== $otp_code) {
      return $this->errorResponse('invalid_otp', 'The code is incorrect or has expired.');
    }

    // Activate the user account.
    $user_storage = $this->entityTypeManager->getStorage('user');
    /** @var \Drupal\user\UserInterface|null $user */
    $user = $user_storage->load($session['uid']);
    if (!$user) {
      return $this->errorResponse('not_found', 'User account not found.', 404);
    }
    $user->activate()->save();

    $this->otpService->deleteOtpSession($otp_token);

    // Issue access token for auto-login after verification.
    $access_token = $this->issueAccessToken((int) $user->id());
    $token_expiry = $this->getTokenExpiry();

    return new JsonResponse([
      'status' => 'verified',
      'message' => 'Account verified.',
      'uid' => (int) $user->id(),
      'access_token' => $access_token,
      'token_type' => 'Bearer',
      'expires_in' => $token_expiry,
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/register/resend-otp
  // ---------------------------------------------------------------------------

  /**
   * Resends the registration OTP with a new session token.
   *
   * Request body (JSON):
   *   otp_token  string  required  Current session token
   *
   * Response 200:
   *   status      "otp_resent"
   *   message     string
   *   otp_token   string  New session token (invalidates the old one)
   */
  public function resendRegisterOtp(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $otp_token = trim($data['otp_token'] ?? '');

    if (empty($otp_token)) {
      return $this->errorResponse('invalid_request', 'OTP token is required.');
    }

    $session = $this->otpService->getOtpSession($otp_token);
    if (!$session || ($session['type'] ?? '') !== 'register') {
      return $this->errorResponse('invalid_otp', 'Session expired. Please start registration again.');
    }

    $config = $this->configFactory->get('lms_mobile_api.settings');
    $otp_expiry = (int) ($config->get('otp_expiry_seconds') ?: 900);

    $new_otp_code = $this->otpService->generateOtpCode();
    $new_otp_token = $this->otpService->generateToken();

    $this->otpService->deleteOtpSession($otp_token);
    $this->otpService->storeOtpSession($new_otp_token, [
      'uid' => $session['uid'],
      'email' => $session['email'],
      'otp_code' => $new_otp_code,
      'password' => $session['password'],
      'type' => 'register',
    ], $otp_expiry);

    $this->otpService->sendOtpEmail($session['email'], $new_otp_code, 'register');

    return new JsonResponse([
      'status' => 'otp_resent',
      'message' => 'A new code has been sent.',
      'otp_token' => $new_otp_token,
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/forgot-password
  // ---------------------------------------------------------------------------

  /**
   * Sends a password-reset OTP email.
   *
   * Always returns 200 to prevent user-enumeration attacks.
   *
   * Request body (JSON):
   *   email  string  required
   *
   * Response 200:
   *   status      "otp_sent"
   *   message     string
   *   otp_token   string  (empty string when email not found)
   */
  public function forgotPassword(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $email = trim($data['email'] ?? '');

    // Always return 200 so callers cannot enumerate registered emails.
    $generic_response = new JsonResponse([
      'status' => 'otp_sent',
      'message' => 'A password reset code has been sent to your email.',
      'otp_token' => '',
    ]);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $generic_response;
    }

    $user_storage = $this->entityTypeManager->getStorage('user');
    $users = $user_storage->loadByProperties(['mail' => $email, 'status' => 1]);
    if (empty($users)) {
      return $generic_response;
    }

    /** @var \Drupal\user\UserInterface $user */
    $user = reset($users);
    $config = $this->configFactory->get('lms_mobile_api.settings');
    $otp_expiry = (int) ($config->get('otp_expiry_seconds') ?: 900);

    $otp_code = $this->otpService->generateOtpCode();
    $otp_token = $this->otpService->generateToken();

    $this->otpService->storeOtpSession($otp_token, [
      'uid' => (int) $user->id(),
      'email' => $email,
      'otp_code' => $otp_code,
      'type' => 'forgot_password',
    ], $otp_expiry);

    $this->otpService->sendOtpEmail($email, $otp_code, 'forgot_password');

    return new JsonResponse([
      'status' => 'otp_sent',
      'message' => 'A password reset code has been sent to your email.',
      'otp_token' => $otp_token,
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/forgot-password/verify-otp
  // ---------------------------------------------------------------------------

  /**
   * Verifies the forgot-password OTP and issues a one-time reset token.
   *
   * Request body (JSON):
   *   otp_token  string  required
   *   otp_code   string  required
   *
   * Response 200:
   *   reset_token  string  Use with /api/v1/user/reset-password
   *
   * Response 400: invalid / expired OTP
   */
  public function verifyForgotPasswordOtp(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $otp_token = trim($data['otp_token'] ?? '');
    $otp_code = trim($data['otp_code'] ?? '');

    if (empty($otp_token) || empty($otp_code)) {
      return $this->errorResponse('invalid_otp', 'OTP token and code are required.');
    }

    $session = $this->otpService->getOtpSession($otp_token);
    if (!$session || ($session['type'] ?? '') !== 'forgot_password') {
      return $this->errorResponse('invalid_otp', 'The code is incorrect or has expired.');
    }
    if ($session['otp_code'] !== $otp_code) {
      return $this->errorResponse('invalid_otp', 'The code is incorrect or has expired.');
    }

    $this->otpService->deleteOtpSession($otp_token);

    $config = $this->configFactory->get('lms_mobile_api.settings');
    $reset_expiry = (int) ($config->get('reset_token_expiry_seconds') ?: 1800);
    $reset_token = $this->otpService->generateToken();

    $this->otpService->storeResetSession($reset_token, [
      'uid' => $session['uid'],
      'email' => $session['email'],
    ], $reset_expiry);

    return new JsonResponse(['reset_token' => $reset_token]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/reset-password
  // ---------------------------------------------------------------------------

  /**
   * Resets a user password using a one-time reset token.
   *
   * Request body (JSON):
   *   reset_token       string  required
   *   password          string  required, min 8 chars
   *   password_confirm  string  required, must match password
   *
   * Response 200:
   *   status   "success"
   *   message  string
   *
   * Response 400: invalid token or validation error
   */
  public function resetPassword(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $reset_token = trim($data['reset_token'] ?? '');
    $password = $data['password'] ?? '';
    $password_confirm = $data['password_confirm'] ?? '';

    if (empty($reset_token)) {
      return $this->errorResponse('invalid_reset_token', 'Reset token is required.');
    }
    if (mb_strlen($password) < 8) {
      return $this->errorResponse('validation_error', 'Password must be at least 8 characters.');
    }
    if ($password !== $password_confirm) {
      return $this->errorResponse('validation_error', 'Passwords do not match.');
    }

    $session = $this->otpService->getResetSession($reset_token);
    if (!$session) {
      return $this->errorResponse('invalid_reset_token', 'The reset token is invalid or has expired.');
    }

    $user_storage = $this->entityTypeManager->getStorage('user');
    /** @var \Drupal\user\UserInterface|null $user */
    $user = $user_storage->load($session['uid']);
    if (!$user) {
      return $this->errorResponse('not_found', 'User account not found.', 404);
    }

    $user->setPassword($password)->save();
    $this->otpService->deleteResetSession($reset_token);

    // Issue access token for auto-login after reset.
    $access_token = $this->issueAccessToken((int) $user->id());
    $token_expiry = $this->getTokenExpiry();

    return new JsonResponse([
      'status' => 'success',
      'message' => 'Your password has been updated.',
      'uid' => (int) $user->id(),
      'access_token' => $access_token,
      'token_type' => 'Bearer',
      'expires_in' => $token_expiry,
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/oauth/token
  // ---------------------------------------------------------------------------

  /**
   * Issues an access token for a user (password grant workaround).
   *
   * Request body (JSON):
   *   username  string  required  User email address
   *   password  string  required
   *
   * Response 200:
   *   uid          int
   *   email        string
   *   name         string
   *   access_token string
   *   token_type   "Bearer"
   *   expires_in   int  seconds
   *
   * Response 401: invalid credentials
   */
  public function oauthToken(Request $request): JsonResponse {
    $data = $this->decodeJson($request);
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
      return $this->errorResponse('invalid_request', 'username and password are required.');
    }

    $user_storage = $this->entityTypeManager->getStorage('user');
    $users = $user_storage->loadByProperties(['mail' => $username, 'status' => 1]);

    if (empty($users)) {
      return $this->errorResponse('invalid_grant', 'Invalid credentials.', 401);
    }

    /** @var \Drupal\user\UserInterface $user */
    $user = reset($users);

    if (!\Drupal::service('password')->check($password, $user->getPassword())) {
      return $this->errorResponse('invalid_grant', 'Invalid credentials.', 401);
    }

    $access_token = $this->issueAccessToken((int) $user->id());
    $token_expiry = $this->getTokenExpiry();

    return new JsonResponse([
      'uid' => (int) $user->id(),
      'email' => $user->getEmail(),
      'name' => $user->getDisplayName(),
      'access_token' => $access_token,
      'token_type' => 'Bearer',
      'expires_in' => $token_expiry,
    ]);
  }

  // ---------------------------------------------------------------------------
  // GET /api/v1/user/profile
  // ---------------------------------------------------------------------------

  /**
   * Returns the authenticated user's profile.
   *
   * Requires: Authorization: Bearer <access_token>
   *
   * Response 200:
   *   uid    int
   *   email  string
   *   name   string
   */
  public function getProfile(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);

    return new JsonResponse([
      'uid' => (int) $user->id(),
      'email' => $user->getEmail(),
      'name' => $user->getDisplayName(),
    ]);
  }

  // ---------------------------------------------------------------------------
  // POST /api/v1/user/change-password
  // ---------------------------------------------------------------------------

  /**
   * Changes the authenticated user's password.
   *
   * Requires: Authorization: Bearer <access_token>
   *
   * Request body (JSON):
   *   current_password      string  required
   *   new_password          string  required, min 8 chars
   *   new_password_confirm  string  required, must match new_password
   *
   * Response 200:
   *   status   "success"
   *   message  string
   *
   * Response 400/401: validation or auth error
   */
  public function changePassword(Request $request): JsonResponse {
    $user = $this->getAuthenticatedUser($request);
    $data = $this->decodeJson($request);

    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $new_password_confirm = $data['new_password_confirm'] ?? '';

    if (empty($current_password) || empty($new_password)) {
      return $this->errorResponse('invalid_request', 'current_password and new_password are required.');
    }
    if (mb_strlen($new_password) < 8) {
      return $this->errorResponse('validation_error', 'New password must be at least 8 characters.', 422, [
        ['field' => 'new_password', 'message' => 'New password must be at least 8 characters.'],
      ]);
    }
    if ($new_password !== $new_password_confirm) {
      return $this->errorResponse('validation_error', 'Passwords do not match.', 422, [
        ['field' => 'new_password_confirm', 'message' => 'Passwords do not match.'],
      ]);
    }
    if (!\Drupal::service('password')->check($current_password, $user->getPassword())) {
      return $this->errorResponse('invalid_grant', 'Current password is incorrect.', 401);
    }

    $user->setPassword($new_password)->save();

    return new JsonResponse([
      'status' => 'success',
      'message' => 'Password changed successfully.',
    ]);
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  /**
   * Issues a new access token and stores it in the key-value expirable store.
   *
   * @param int $uid  Drupal user ID.
   * @return string   32-character hex token.
   */
  protected function issueAccessToken(int $uid): string {
    $token = bin2hex(random_bytes(16));
    $expiry = $this->getTokenExpiry();
    \Drupal::keyValue('lms_mobile_api.tokens')->set($token, $uid);
    // Store expiry separately so we can honour TTL on lookup.
    \Drupal::service('keyvalue.expirable')
      ->get('lms_mobile_api.tokens')
      ->setWithExpire($token, $uid, $expiry);
    return $token;
  }

  /**
   * Returns the configured token expiry in seconds (default 7 days).
   */
  protected function getTokenExpiry(): int {
    $config = $this->configFactory->get('lms_mobile_api.settings');
    return (int) ($config->get('token_expiry_seconds') ?: 604800);
  }

  /**
   * Resolves the authenticated user from the Bearer token in the request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  protected function getAuthenticatedUser(Request $request): \Drupal\user\UserInterface {
    $auth_header = $request->headers->get('Authorization', '');

    if (!preg_match('/Bearer\s+(\S+)/i', $auth_header, $matches)) {
      throw new AccessDeniedHttpException('Missing or invalid Authorization header.');
    }

    $token = $matches[1];
    $uid = \Drupal::service('keyvalue.expirable')
      ->get('lms_mobile_api.tokens')
      ->get($token);

    if (!$uid) {
      throw new AccessDeniedHttpException('Access token is invalid or has expired.');
    }

    /** @var \Drupal\user\UserInterface|null $user */
    $user = $this->entityTypeManager->getStorage('user')->load($uid);

    if (!$user || !$user->isActive()) {
      throw new AccessDeniedHttpException('User account not found or inactive.');
    }

    return $user;
  }

  /**
   * Returns a unique Drupal username derived from the given base string.
   */
  protected function ensureUniqueUsername(string $base): string {
    $sanitised = preg_replace('/[^a-zA-Z0-9_.\-]/', '_', $base) ?? 'user';
    $username = $sanitised;
    $counter = 1;
    $user_storage = $this->entityTypeManager->getStorage('user');
    while (!empty($user_storage->loadByProperties(['name' => $username]))) {
      $username = $sanitised . '_' . $counter;
      $counter++;
    }
    return $username;
  }

  /**
   * Decodes the JSON request body into an array.
   */
  protected function decodeJson(Request $request): array {
    $data = json_decode($request->getContent(), TRUE);
    return is_array($data) ? $data : [];
  }

  /**
   * Returns a JSON error response.
   *
   * @param string $error
   *   Machine-readable error code.
   * @param string $message
   *   Human-readable message.
   * @param int $status
   *   HTTP status code (default 400).
   * @param array $violations
   *   Optional field violations for 422 responses.
   */
  protected function errorResponse(
    string $error,
    string $message,
    int $status = 400,
    array $violations = [],
  ): JsonResponse {
    $body = ['error' => $error, 'message' => $message];
    if (!empty($violations)) {
      $body['violations'] = $violations;
    }
    return new JsonResponse($body, $status);
  }

}
