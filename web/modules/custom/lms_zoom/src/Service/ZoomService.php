<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Zoom API service for Server-to-Server OAuth and meeting management.
 */
final class ZoomService {

  private const OAUTH_URL = 'https://zoom.us/oauth/token';
  private const API_BASE_URL = 'https://api.zoom.us/v2';

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
    private readonly StateInterface $state
  ) {}

  /**
   * Retrieves an access token, utilizing cached token if valid.
   */
  private function getAccessToken(): ?string {
    // Check cached token first.
    $cachedToken = $this->state->get('lms_zoom_access_token');
    $expiresAt = $this->state->get('lms_zoom_token_expires', 0);

    if ($cachedToken && time() < $expiresAt) {
      return $cachedToken;
    }

    $config = $this->configFactory->get('lms_zoom.settings');
    $accountId = $config->get('account_id');
    $clientId = $config->get('client_id');
    $clientSecret = $config->get('client_secret');

    if (!$accountId || !$clientId || !$clientSecret) {
      $this->logger->error('Missing Zoom OAuth credentials.');
      return NULL;
    }

    try {
      $response = $this->httpClient->request('POST', self::OAUTH_URL, [
        'query' => [
          'grant_type' => 'account_credentials',
          'account_id' => $accountId,
        ],
        'auth' => [$clientId, $clientSecret],
      ]);

      $data = json_decode((string) $response->getBody(), TRUE);
      if (isset($data['access_token'])) {
        // Cache token, subtracting 60 seconds as buffer.
        $this->state->set('lms_zoom_access_token', $data['access_token']);
        $this->state->set('lms_zoom_token_expires', time() + $data['expires_in'] - 60);
        return $data['access_token'];
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to get Zoom access token: @msg', ['@msg' => $e->getMessage()]);
    }

    return NULL;
  }

  /**
   * Send an authenticated API request to Zoom.
   */
  private function request(string $method, string $endpoint, array $options = []): ?array {
    $token = $this->getAccessToken();
    if (!$token) {
      return NULL;
    }

    $options['headers']['Authorization'] = 'Bearer ' . $token;

    try {
      $response = $this->httpClient->request($method, self::API_BASE_URL . $endpoint, $options);
      return json_decode((string) $response->getBody(), TRUE) ?? [];
    }
    catch (ClientException $e) {
      $this->logger->error('Zoom API Error: @endpoint - @msg', [
        '@endpoint' => $endpoint,
        '@msg' => $e->getResponse()->getBody()->getContents()
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error('Zoom Request Error: @msg', ['@msg' => $e->getMessage()]);
    }

    return NULL;
  }

  /**
   * Create a Zoom meeting.
   *
   * @param string $topic
   *   Meeting topic.
   * @param string $startTime
   *   ISO 8601 start time (e.g., "2024-03-24T18:00:00Z").
   * @param int $durationMinutes
   *   Duration in minutes.
   *
   * @return array|null
   *   Meeting data or NULL on failure.
   */
  public function createMeeting(string $topic, string $startTime, int $durationMinutes = 60, ?string $instructorEmail = NULL): ?array {
    $payload = [
      'topic' => $topic,
      'type' => 2, // Scheduled meeting
      'start_time' => $startTime,
      'duration' => $durationMinutes,
      'timezone' => 'UTC',
      'settings' => [
        'host_video' => TRUE,
        'participant_video' => TRUE,
        'join_before_host' => FALSE,
        'mute_upon_entry' => TRUE,
        'waiting_room' => TRUE,
        'approval_type' => 0, // Automatically approve
      ]
    ];

    $userId = $instructorEmail ?: 'me';
    return $this->request('POST', "/users/{$userId}/meetings", ['json' => $payload]);
  }

  /**
   * Get meeting details.
   */
  public function getMeeting(string $meetingId): ?array {
    return $this->request('GET', "/meetings/{$meetingId}");
  }

  /**
   * Delete a meeting.
   */
  public function deleteMeeting(string $meetingId): bool {
    $response = $this->request('DELETE', "/meetings/{$meetingId}");
    // Zoom returns 204 No Content string on success, which json_decode handles as NULL if empty,
    // so we assume true if request method didn't crash and return NULL explicitly due to error.
    // However, our request method returns [] if body is empty string.
    return is_array($response);
  }

  /**
   * Get Meeting Cloud Recordings.
   */
  public function getRecordings(string $meetingId): ?array {
    return $this->request('GET', "/meetings/{$meetingId}/recordings");
  }

  /**
   * Get Zoom Access Token (ZAK) for a user to start meetings directly via Web SDK.
   */
  public function getZakToken(string $userId): ?string {
    $response = $this->request('GET', "/users/{$userId}/token?type=zak");
    return $response['token'] ?? NULL;
  }

}
