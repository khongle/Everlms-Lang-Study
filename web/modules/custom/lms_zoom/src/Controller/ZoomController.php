<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\lms_zoom\Service\ZoomService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Firebase\JWT\JWT;

/**
 * Handles Zoom Meeting Web SDK flow.
 */
class ZoomController extends ControllerBase {

  public function __construct(
    private readonly ZoomService $zoomService
  ) {}

  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('lms_zoom.service')
    );
  }

  /**
   * Renders the Zoom Web SDK meeting player.
   */
  public function joinMeeting(string $meeting_id): array {
    $meetingDetails = $this->zoomService->getMeeting($meeting_id);

    if (!$meetingDetails) {
      throw new \Exception('Failed to retrieve meeting details from Zoom. Are credentials correctly configured?');
    }

    $passcode = $meetingDetails['password'] ?? '';
    // If meeting has no password set empty string
    
    $user = $this->currentUser();
    $user_name = $user->getDisplayName();
    $user_email = $user->getEmail();

    return [
      '#theme' => 'lms_zoom_web_player',
      '#meeting_id' => $meeting_id,
      '#passcode' => $passcode,
      '#user_name' => $user_name,
      '#user_email' => $user_email,
      '#attached' => [
        'library' => [
          'lms_zoom/websdk',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }

  /**
   * Generates the JWT signature for Zoom Web SDK.
   */
  public function generateSignature(Request $request): JsonResponse {
    $data = json_decode($request->getContent(), TRUE);
    $meeting_num = $data['meetingNumber'] ?? null;
    $role = (int) ($data['role'] ?? 0); // 0 for attendee, 1 for host

    $zakToken = NULL;

    // Determine if the current user is the Host (Instructor)
    try {
      if ($meeting_num) {
        $lesson_storage = \Drupal::entityTypeManager()->getStorage('lms_lesson');
        $lessons = $lesson_storage->loadByProperties(['field_zoom_meeting_id' => $meeting_num]);
        if (!empty($lessons)) {
          $lesson = reset($lessons);
          $chapterStorage = \Drupal::entityTypeManager()->getStorage('lms_chapter');
          $chapters = $chapterStorage->loadByProperties(['field_lessons' => $lesson->id()]);
          if (!empty($chapters)) {
            $chapter = reset($chapters);
            $courseStorage = \Drupal::entityTypeManager()->getStorage('lms_course');
            $courses = $courseStorage->loadByProperties(['field_chapters' => $chapter->id()]);
            if (!empty($courses)) {
              $course = reset($courses);
              $instructor_refs = $course->hasField('field_instructor') ? $course->get('field_instructor')->referencedEntities() : [];
              $instructor = !empty($instructor_refs) ? reset($instructor_refs) : $course->getOwner();
              if ($instructor && $instructor->id() === \Drupal::currentUser()->id()) {
                $role = 1; // Current user is the instructor, grant host role!
                
                // Fetch ZAK token so Web SDK can physically start the meeting and share screen
                $zoomService = \Drupal::service('lms_zoom.zoom_service');
                $meeting_data = $zoomService->getMeeting($meeting_num);
                $hostEmail = $meeting_data['host_email'] ?? 'me';
                $zakToken = $zoomService->getZakToken($hostEmail);
              }
            }
          }
        }
      }
    } catch (\Exception $e) {
      \Drupal::logger('lms_zoom')->error('Failed to resolve host role: ' . $e->getMessage());
    }

    if (!$meeting_num) {
      return new JsonResponse(['error' => 'Missing meetingNumber'], 400);
    }

    $config = $this->config('lms_zoom.settings');
    $sdk_key = $config->get('sdk_key');
    $sdk_secret = $config->get('sdk_secret');

    if (empty($sdk_key) || empty($sdk_secret)) {
      return new JsonResponse(['error' => 'SDK Credentials not configured on server'], 500);
    }

    $iat = time() - 30; // 30 seconds buffer
    $exp = $iat + (60 * 60 * 2); // Valid for 2 hours

    $token_payload = [
      'sdkKey' => $sdk_key,
      'mn' => $meeting_num,
      'role' => $role,
      'iat' => $iat,
      'exp' => $exp,
      'appKey' => $sdk_key, // For backward compatibility with older Web SDK versions
      'tokenExp' => $exp
    ];

    try {
      $jwt = JWT::encode($token_payload, $sdk_secret, 'HS256');
      return new JsonResponse([
        'signature' => $jwt,
        'sdkKey' => $sdk_key,
        'zak' => $zakToken,
      ]);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => 'Signature generation failed: ' . $e->getMessage()], 500);
    }
  }

}
