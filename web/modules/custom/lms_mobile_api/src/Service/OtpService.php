<?php

declare(strict_types=1);

namespace Drupal\lms_mobile_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Service for OTP generation, storage, and email notification.
 */
class OtpService {

  const KEY_VALUE_COLLECTION = 'lms_mobile_api.otp';

  const RESET_COLLECTION = 'lms_mobile_api.reset';

  public function __construct(
    protected KeyValueExpirableFactoryInterface $keyValueExpirable,
    protected MailManagerInterface $mailManager,
    protected LanguageManagerInterface $languageManager,
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Generates a 6-digit numeric OTP code.
   */
  public function generateOtpCode(): string {
    return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
  }

  /**
   * Generates a cryptographically secure random hex token.
   */
  public function generateToken(): string {
    return bin2hex(random_bytes(32));
  }

  /**
   * Stores OTP session data with expiry.
   *
   * @param string $otp_token
   *   The session token key.
   * @param array $data
   *   Session data to store.
   * @param int $expiry_seconds
   *   TTL in seconds.
   */
  public function storeOtpSession(string $otp_token, array $data, int $expiry_seconds): void {
    $store = $this->keyValueExpirable->get(self::KEY_VALUE_COLLECTION);
    $store->setWithExpire($otp_token, $data, $expiry_seconds);
  }

  /**
   * Retrieves OTP session data by token, or NULL if expired / not found.
   */
  public function getOtpSession(string $otp_token): ?array {
    $store = $this->keyValueExpirable->get(self::KEY_VALUE_COLLECTION);
    $data = $store->get($otp_token);
    return is_array($data) ? $data : NULL;
  }

  /**
   * Deletes an OTP session.
   */
  public function deleteOtpSession(string $otp_token): void {
    $store = $this->keyValueExpirable->get(self::KEY_VALUE_COLLECTION);
    $store->delete($otp_token);
  }

  /**
   * Stores a password-reset session with expiry.
   */
  public function storeResetSession(string $reset_token, array $data, int $expiry_seconds): void {
    $store = $this->keyValueExpirable->get(self::RESET_COLLECTION);
    $store->setWithExpire($reset_token, $data, $expiry_seconds);
  }

  /**
   * Retrieves a password-reset session, or NULL if expired / not found.
   */
  public function getResetSession(string $reset_token): ?array {
    $store = $this->keyValueExpirable->get(self::RESET_COLLECTION);
    $data = $store->get($reset_token);
    return is_array($data) ? $data : NULL;
  }

  /**
   * Deletes a password-reset session.
   */
  public function deleteResetSession(string $reset_token): void {
    $store = $this->keyValueExpirable->get(self::RESET_COLLECTION);
    $store->delete($reset_token);
  }

  /**
   * Sends an OTP email to the given address.
   *
   * @param string $to
   *   Recipient email address.
   * @param string $otp_code
   *   The OTP code to include in the email.
   * @param string $type
   *   Either 'register' or 'forgot_password'.
   *
   * @return bool
   *   TRUE on success.
   */
  public function sendOtpEmail(string $to, string $otp_code, string $type = 'register'): bool {
    $params = [
      'email' => $to,
      'otp_code' => $otp_code,
      'type' => $type,
    ];
    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $result = $this->mailManager->mail(
      'lms_mobile_api',
      'otp_email',
      $to,
      $langcode,
      $params,
    );
    return (bool) ($result['result'] ?? FALSE);
  }

}
