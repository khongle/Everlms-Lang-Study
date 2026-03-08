<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Service;

/**
 * Zoom API service for meeting management.
 *
 * @todo Implement with Zoom REST API + OAuth2.
 */
final class ZoomService {

  /**
   * Create a Zoom meeting.
   *
   * @return array
   *   Meeting data with 'meeting_id', 'join_url', 'start_url'.
   */
  public function createMeeting(string $topic, string $startTime, int $durationMinutes = 60): array {
    // TODO: Implement Zoom API call.
    return [
      'meeting_id' => '',
      'join_url' => '',
      'start_url' => '',
    ];
  }

  /**
   * Get meeting details.
   */
  public function getMeeting(string $meetingId): array {
    // TODO: Implement.
    return [];
  }

  /**
   * Delete a meeting.
   */
  public function deleteMeeting(string $meetingId): bool {
    // TODO: Implement.
    return FALSE;
  }

}
