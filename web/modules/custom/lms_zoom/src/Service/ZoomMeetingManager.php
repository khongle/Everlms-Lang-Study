<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\lms_lesson\Entity\Lesson;
use Psr\Log\LoggerInterface;

/**
 * Manages the lifecycle of Zoom meetings tied to LMS lessons.
 */
final class ZoomMeetingManager {

  public function __construct(
    private readonly ZoomService $zoomApi,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Synchronize a Lesson entity to a Zoom meeting.
   *
   * @param \Drupal\lms_lesson\Entity\Lesson $lesson
   *   The lesson entity.
   */
  public function syncMeeting(Lesson $lesson): void {
    if ($lesson->get('field_type')->value !== 'zoom') {
      return;
    }

    $existingMeetingId = $lesson->get('field_zoom_meeting_id')->value;
    $topic = $lesson->label();

    $scheduleTimestamp = $lesson->get('field_zoom_schedule')->value;
    $startTime = $scheduleTimestamp ? gmdate('Y-m-d\TH:i:s\Z', (int) $scheduleTimestamp) : gmdate('Y-m-d\TH:i:s\Z', time() + 3600);
    $duration = (int) $lesson->get('field_duration')->value ?: 60;

    // Resolve the instructor's email via Lesson -> Chapter -> Course
    $instructorEmail = NULL;
    try {
      $chapterStorage = \Drupal::entityTypeManager()->getStorage('lms_chapter');
      $chapters = $chapterStorage->loadByProperties(['field_lessons' => $lesson->id()]);
      if (!empty($chapters)) {
        $chapter = reset($chapters);
        $courseStorage = \Drupal::entityTypeManager()->getStorage('lms_course');
        $courses = $courseStorage->loadByProperties(['field_chapters' => $chapter->id()]);
        if (!empty($courses)) {
          $course = reset($courses);
          if ($course->hasField('field_instructor') && !$course->get('field_instructor')->isEmpty()) {
            $instructorRefs = $course->get('field_instructor')->referencedEntities();
            $instructor = !empty($instructorRefs) ? reset($instructorRefs) : $course->getOwner();
            $instructorEmail = $instructor ? $instructor->getEmail() : NULL;
          } else {
            $instructorEmail = $course->getOwner()->getEmail();
          }
        }
      }
    } catch (\Exception $e) {
      $this->logger->error('Failed to lookup instructor for zoom: ' . $e->getMessage());
    }

    // If no meeting ID exists, create one via API.
    if (!$existingMeetingId) {
      $meeting = $this->zoomApi->createMeeting($topic, $startTime, $duration, $instructorEmail);
      
      // Fallback: If creating the meeting for the specific instructor failed (e.g., they don't have a linked Zoom account)
      if (!$meeting && $instructorEmail) {
        $this->logger->warning('Failed to create Zoom meeting for instructor @email. Falling back to Admin account (me).', [
          '@email' => $instructorEmail,
        ]);
        $meeting = $this->zoomApi->createMeeting($topic, $startTime, $duration);
      }

      if ($meeting && isset($meeting['id'])) {
        $lesson->set('field_zoom_meeting_id', (string) $meeting['id']);
        $this->logger->info('Created Zoom meeting @id for lesson @lesson', [
          '@id' => $meeting['id'],
          '@lesson' => $lesson->id(),
        ]);
      }
    }
    // Update logic could be added here if Zoom API integration supports PATCH.
  }

  /**
   * Delete linked zoom meeting if lesson is deleted.
   */
  public function deleteMeeting(Lesson $lesson): void {
    if ($lesson->get('field_type')->value === 'zoom') {
      $meetingId = $lesson->get('field_zoom_meeting_id')->value;
      if ($meetingId) {
        $this->zoomApi->deleteMeeting($meetingId);
        $this->logger->info('Deleted Zoom meeting @id for deleted lesson @lesson', [
          '@id' => $meetingId,
          '@lesson' => $lesson->id(),
        ]);
      }
    }
  }

}
