<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_lesson\Entity\Lesson;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User portal pages: dashboard, my courses, lesson view.
 */
final class UserPortalController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Dashboard — overview with stats and recent activity.
   */
  public function dashboard(): array {
    $uid = (int) $this->currentUser->id();

    // Enrolled courses.
    $enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->execute();
    $enrollments = $enrollment_ids
      ? $this->entityTypeManager->getStorage('lms_user_course')->loadMultiple($enrollment_ids)
      : [];

    // Build enrollment data.
    $enrolled_courses = [];
    $in_progress = 0;
    $completed = 0;
    foreach ($enrollments as $enrollment) {
      $course = $enrollment->get('field_course')->entity;
      if (!$course) {
        continue;
      }
      $status = $enrollment->get('field_status')->value;
      if ($status === 'completed') {
        $completed++;
      }
      else {
        $in_progress++;
      }
      // Determine continue URL
      $continue_url = \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString();
      if (!$course->get('field_chapters')->isEmpty()) {
         $course_lesson_ids = [];
         foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {
           if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
             foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
               if ($lesson->get('status')->value) {
                 $course_lesson_ids[] = $lesson->id();
               }
             }
           }
         }

         if (!empty($course_lesson_ids)) {
           $target_lesson_id = NULL;

           $last_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
             ->getQuery()
             ->condition('uid', $uid)
             ->condition('field_lesson', $course_lesson_ids, 'IN')
             ->sort('id', 'DESC')
             ->range(0, 1)
             ->accessCheck(FALSE)
             ->execute();

           if ($last_progress) {
             $progress_record = $this->entityTypeManager->getStorage('lms_user_lesson')->load(reset($last_progress));
             if ($progress_record) {
               $last_lesson_id = $progress_record->get('field_lesson')->target_id;
               if ($progress_record->get('field_status')->value !== 'completed') {
                 $target_lesson_id = $last_lesson_id;
               } else {
                 $found = FALSE;
                 foreach ($course_lesson_ids as $lid) {
                   if ($found) {
                     $target_lesson_id = $lid;
                     break;
                   }
                   if ($lid == $last_lesson_id) {
                     $found = TRUE;
                   }
                 }
               }
             }
           }

           if (!$target_lesson_id) {
             $completed_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
               ->getQuery()
               ->condition('uid', $uid)
               ->condition('field_lesson', $course_lesson_ids, 'IN')
               ->condition('field_status', 'completed')
               ->accessCheck(FALSE)
               ->execute();
             
             $completed_lesson_ids = [];
             if ($completed_progress) {
               foreach ($this->entityTypeManager->getStorage('lms_user_lesson')->loadMultiple($completed_progress) as $record) {
                  $completed_lesson_ids[] = $record->get('field_lesson')->target_id;
               }
             }
             foreach ($course_lesson_ids as $lid) {
               if (!in_array($lid, $completed_lesson_ids)) {
                 $target_lesson_id = $lid;
                 break;
               }
             }
           }

           if (!$target_lesson_id) {
             $target_lesson_id = $course_lesson_ids[0];
           }

           if ($target_lesson_id) {
              $continue_url = \Drupal\Core\Url::fromRoute('lms_progress.lesson_view', [
                'lms_course' => $course->id(), 
                'lms_lesson' => $target_lesson_id
              ])->toString();
           }
         }
      }

      $thumbnail_url = '';
      if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
        $file = $course->get('field_thumbnail')->entity;
        if ($file) {
          $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $category_name = '';
      if ($course->hasField('field_category') && !$course->get('field_category')->isEmpty()) {
        $category_entity = $course->get('field_category')->entity;
        if ($category_entity) {
          $category_name = $category_entity->label();
        }
      }

      $accurate_progress = 0;
      if ($status === 'completed') {
        $accurate_progress = 100;
      } elseif (!empty($course_lesson_ids)) {
         $completed_progress_count = $this->entityTypeManager->getStorage('lms_user_lesson')
           ->getQuery()
           ->condition('uid', $uid)
           ->condition('field_lesson', $course_lesson_ids, 'IN')
           ->condition('field_status', 'completed')
           ->accessCheck(FALSE)
           ->count()
           ->execute();
         $accurate_progress = floor(($completed_progress_count / count($course_lesson_ids)) * 100);
      }

      $enrolled_courses[] = [
        'course' => $course,
        'progress' => (float) $accurate_progress,
        'status' => ($status === 'completed') ? 'completed' : 'in_progress',
        'enrolled_at' => $enrollment->get('field_enrolled_at')->value,
        'url' => $continue_url,
        'thumbnail_url' => $thumbnail_url,
        'category' => $category_name,
      ];
    }

    // Certificates.
    $cert_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'lms_dashboard',
      '#stats' => [
        'enrolled' => count($enrollments),
        'in_progress' => $in_progress,
        'completed' => $completed,
        'certificates' => $cert_count,
      ],
      '#recent_courses' => $enrolled_courses,
    ];
  }

  /**
   * My Courses — list of enrolled courses.
   */
  public function myCourses(): array {
    $uid = (int) $this->currentUser->id();

    $enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('field_enrolled_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $enrollments = $enrollment_ids
      ? $this->entityTypeManager->getStorage('lms_user_course')->loadMultiple($enrollment_ids)
      : [];

    $courses = [];
    foreach ($enrollments as $enrollment) {
      $course = $enrollment->get('field_course')->entity;
      $status = $enrollment->hasField('field_status') && !$enrollment->get('field_status')->isEmpty() 
        ? $enrollment->get('field_status')->value 
        : 'in_progress';
      if (!$course) {
        continue;
      }
      // Determine continue URL
      $continue_url = \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString();
      if (!$course->get('field_chapters')->isEmpty()) {
         $course_lesson_ids = [];
         foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {
           if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
             foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
               if ($lesson->get('status')->value) {
                 $course_lesson_ids[] = $lesson->id();
               }
             }
           }
         }

         if (!empty($course_lesson_ids)) {
           $target_lesson_id = NULL;

           $last_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
             ->getQuery()
             ->condition('uid', $uid)
             ->condition('field_lesson', $course_lesson_ids, 'IN')
             ->sort('id', 'DESC')
             ->range(0, 1)
             ->accessCheck(FALSE)
             ->execute();

           if ($last_progress) {
             $progress_record = $this->entityTypeManager->getStorage('lms_user_lesson')->load(reset($last_progress));
             if ($progress_record) {
               $last_lesson_id = $progress_record->get('field_lesson')->target_id;
               if ($progress_record->get('field_status')->value !== 'completed') {
                 $target_lesson_id = $last_lesson_id;
               } else {
                 $found = FALSE;
                 foreach ($course_lesson_ids as $lid) {
                   if ($found) {
                     $target_lesson_id = $lid;
                     break;
                   }
                   if ($lid == $last_lesson_id) {
                     $found = TRUE;
                   }
                 }
               }
             }
           }

           if (!$target_lesson_id) {
             $completed_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
               ->getQuery()
               ->condition('uid', $uid)
               ->condition('field_lesson', $course_lesson_ids, 'IN')
               ->condition('field_status', 'completed')
               ->accessCheck(FALSE)
               ->execute();
             
             $completed_lesson_ids = [];
             if ($completed_progress) {
               foreach ($this->entityTypeManager->getStorage('lms_user_lesson')->loadMultiple($completed_progress) as $record) {
                  $completed_lesson_ids[] = $record->get('field_lesson')->target_id;
               }
             }
             foreach ($course_lesson_ids as $lid) {
               if (!in_array($lid, $completed_lesson_ids)) {
                 $target_lesson_id = $lid;
                 break;
               }
             }
           }

           if (!$target_lesson_id) {
             $target_lesson_id = $course_lesson_ids[0];
           }

           if ($target_lesson_id) {
              $continue_url = \Drupal\Core\Url::fromRoute('lms_progress.lesson_view', [
                'lms_course' => $course->id(), 
                'lms_lesson' => $target_lesson_id
              ])->toString();
           }
         }
      }

      $thumbnail_url = '';
      if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
        $file = $course->get('field_thumbnail')->entity;
        if ($file) {
          $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $category_name = '';
      if ($course->hasField('field_category') && !$course->get('field_category')->isEmpty()) {
        $category_entity = $course->get('field_category')->entity;
        if ($category_entity) {
          $category_name = $category_entity->label();
        }
      }

      $accurate_progress = 0;
      if ($status === 'completed') {
        $accurate_progress = 100;
      } elseif (!empty($course_lesson_ids)) {
         $completed_progress_count = $this->entityTypeManager->getStorage('lms_user_lesson')
           ->getQuery()
           ->condition('uid', $uid)
           ->condition('field_lesson', $course_lesson_ids, 'IN')
           ->condition('field_status', 'completed')
           ->accessCheck(FALSE)
           ->count()
           ->execute();
         $accurate_progress = floor(($completed_progress_count / count($course_lesson_ids)) * 100);
      }

      $courses[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'progress' => (float) $accurate_progress,
        'status' => ($status === 'completed') ? 'completed' : 'in_progress',
        'url' => $continue_url,
        'thumbnail_url' => $thumbnail_url,
        'category' => $category_name,
      ];
    }

    return [
      '#theme' => 'lms_my_courses',
      '#courses' => $courses,
    ];
  }

  /**
   * Lesson view — learning page with video/content.
   */
  public function lessonView(Course $lms_course, Lesson $lms_lesson): array {
    $uid = (int) $this->currentUser->id();

    // Auto-enroll the user if they accessed this via Membership and have no record
    $course_enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_course', $lms_course->id())
      ->accessCheck(FALSE)
      ->execute();

    if (empty($course_enrollment_ids)) {
      $enrollment = $this->entityTypeManager->getStorage('lms_user_course')->create([
        'uid' => $uid,
        'field_course' => $lms_course->id(),
        'field_status' => 'in_progress',
        'field_progress' => 0,
        'field_enrolled_at' => time(),
      ]);
      $enrollment->save();
    }

    // Fetch user's completed lessons for the course context
    $completed_lesson_ids = [];
    $progress_ids = $this->entityTypeManager->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->execute();
    
    if ($progress_ids) {
      $progress_records = $this->entityTypeManager->getStorage('lms_user_lesson')->loadMultiple($progress_ids);
      foreach ($progress_records as $record) {
        if (!$record->get('field_lesson')->isEmpty()) {
           $completed_lesson_ids[] = $record->get('field_lesson')->target_id;
        }
      }
    }

    $curriculum = [];
    if ($lms_course->hasField('field_chapters') && !$lms_course->get('field_chapters')->isEmpty()) {
      $chapters = $lms_course->get('field_chapters')->referencedEntities();
      foreach ($chapters as $chapter) {
        if (!$chapter->get('status')->value) {
          continue;
        }
        $lessons = [];
        if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
          $chapter_lessons = $chapter->get('field_lessons')->referencedEntities();
          foreach ($chapter_lessons as $cl) {
            if ($cl->get('status')->value) {
              $is_completed = in_array($cl->id(), $completed_lesson_ids);
              $lessons[] = [
                'entity' => $cl,
                'is_completed' => $is_completed,
              ];
            }
          }
        }
        $curriculum[] = [
          'chapter' => $chapter,
          'lessons' => $lessons,
        ];
      }
    }

    // Get/create user lesson progress.
    $progress_ids = $this->entityTypeManager->getStorage('lms_user_lesson')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lms_lesson->id())
      ->accessCheck(FALSE)
      ->execute();

    $video_position = 0;
    if ($progress_ids) {
      $progress = $this->entityTypeManager->getStorage('lms_user_lesson')
        ->load(reset($progress_ids));
      if ($progress) {
        $video_position = (int) $progress->get('field_video_position')->value;
      }
    }

    $video_embed_url = '';
    if ($lms_lesson->hasField('field_video_url') && !$lms_lesson->get('field_video_url')->isEmpty()) {
      $url = $lms_lesson->get('field_video_url')->value;
      if (strpos($url, 'youtube.com/watch?v=') !== FALSE) {
        $video_embed_url = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $url);
        // Remove extra parameters that might break embed like &list=
        $video_embed_url = explode('&', $video_embed_url)[0];
      } elseif (strpos($url, 'youtu.be/') !== FALSE) {
        $video_embed_url = str_replace('youtu.be/', 'youtube.com/embed/', $url);
        $video_embed_url = explode('?', $video_embed_url)[0];
      } elseif (strpos($url, 'vimeo.com/') !== FALSE) {
        $video_embed_url = str_replace('vimeo.com/', 'player.vimeo.com/video/', $url);
      } else {
        $video_embed_url = $url;
      }
    }

    $quiz_id = NULL;
    if ($lms_lesson->get('field_type')->value === 'quiz' && $lms_lesson->hasField('field_quiz') && !$lms_lesson->get('field_quiz')->isEmpty()) {
      $quiz_id = $lms_lesson->get('field_quiz')->target_id;
    }

    $rendered_attachments = [];
    $lesson_type = $lms_lesson->get('field_type')->value;
    if ($lesson_type === 'pdf' && !$lms_lesson->get('field_attachments')->isEmpty()) {
      $rendered_attachments = $lms_lesson->get('field_attachments')->view('default');
    } elseif ($lesson_type === 'pptx' && $lms_lesson->hasField('field_pptx_attachment') && !$lms_lesson->get('field_pptx_attachment')->isEmpty()) {
      $rendered_attachments = $lms_lesson->get('field_pptx_attachment')->view('default');
    } elseif ($lesson_type === 'video' && $lms_lesson->hasField('field_video_file') && !$lms_lesson->get('field_video_file')->isEmpty()) {
      $rendered_attachments = $lms_lesson->get('field_video_file')->view('default');
    } elseif ($lesson_type === 'h5p' && $lms_lesson->hasField('field_h5p_upload') && !$lms_lesson->get('field_h5p_upload')->isEmpty()) {
      $rendered_attachments = $lms_lesson->get('field_h5p_upload')->view('default');
    } elseif ($lesson_type === 'zoom') {
      $meetingId = $lms_lesson->get('field_zoom_meeting_id')->value;
      $scheduleTime = (int) $lms_lesson->get('field_zoom_schedule')->value;
      if ($meetingId && $scheduleTime) {
        $rendered_attachments = [
          '#theme' => 'lms_zoom_join_block',
          '#lesson' => $lms_lesson,
          '#meeting_id' => $meetingId,
          '#schedule_time' => $scheduleTime,
          '#attached' => [
            'library' => [
              'lms_zoom/countdown',
            ],
          ],
        ];
      }
    }

    // Check if SCORM package exists.
    $scorm_url = '';
    if ($lesson_type === 'scorm') {
      $scorm_path = 'public://scorm_extracted/' . $lms_lesson->id() . '/index.html';
      if (is_string($scorm_path) && file_exists($scorm_path)) {
        $scorm_url = \Drupal::service('file_url_generator')->generateString($scorm_path);
      }
    }

    // Get existing note for this lesson.
    $existing_note_text = '';
    $note_ids = $this->entityTypeManager->getStorage('lms_note')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_lesson', $lms_lesson->id())
      ->accessCheck(FALSE)
      ->execute();
    if ($note_ids) {
      $note = $this->entityTypeManager->getStorage('lms_note')->load(reset($note_ids));
      if ($note && !$note->get('field_body')->isEmpty()) {
        $existing_note_text = $note->get('field_body')->value;
      }
    }

    $build = [
      '#theme' => 'lms_lesson_view',
      '#course' => $lms_course,
      '#lesson' => $lms_lesson,
      '#curriculum' => $curriculum,
      '#video_position' => $video_position,
      '#video_embed_url' => $video_embed_url,
      '#scorm_url' => $scorm_url,
      '#quiz_id' => $quiz_id,
      '#is_admin' => $this->currentUser->hasPermission('administer lms_course'),
      '#rendered_attachments' => $rendered_attachments,
      '#existing_note' => $existing_note_text,
    ];

    // Get comments using the formal configured display mode which now integrates ajax_comments
    $comments_build = [];
    if ($lms_lesson->hasField('field_comments')) {
      $status = $lms_lesson->get('field_comments')->status;
      if ($status != \Drupal\comment\Plugin\Field\FieldType\CommentItemInterface::OPEN) {
        $lms_lesson->get('field_comments')->status = \Drupal\comment\Plugin\Field\FieldType\CommentItemInterface::OPEN;
        $lms_lesson->save();
      }
      
      $comments_build = $lms_lesson->get('field_comments')->view('default');
      // Because we bypassed hook_entity_view_alter by rendering the field directly,
      // we MUST manually attach the Javascript core file for ajax_comments to run.
      $comments_build['#attached']['library'][] = 'ajax_comments/commands';

      // Force render bypass for field-level strict access checks.
      // The controller's routing and logic already secures this page.
      $comments_build['#access'] = TRUE;
    }
    
    $build['#comments'] = $comments_build;
    
    $build['#cache'] = [
      'tags' => array_merge(
        ['lms_note:' . $lms_lesson->id() . ':' . $uid],
        $lms_lesson->getCacheTags()
      ),
      'contexts' => ['user', 'url.query_args'],
    ];

    if ($lesson_type === 'zoom') {
      $build['#attached']['library'][] = 'lms_zoom/countdown';
    }

    return $build;
  }

  /**
   * My Certificates.
   */
  public function myCertificates(): array {
    $uid = (int) $this->currentUser->id();

    $cert_ids = $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('field_issued_at', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $certificates = $cert_ids
      ? $this->entityTypeManager->getStorage('lms_user_certificate')->loadMultiple($cert_ids)
      : [];

    $certs = [];
    foreach ($certificates as $cert) {
      $template = $cert->get('field_template')->entity;
      $course = $cert->get('field_course')->entity;
      $certs[] = [
        'id' => $cert->id(),
        'template_name' => $template?->label() ?? '',
        'course_name' => $course?->label() ?? '',
        'certificate_number' => $cert->get('field_certificate_number')->value,
        'issued_at' => $cert->get('field_issued_at')->value,
        'pdf' => $cert->get('field_pdf_file')->entity,
      ];
    }

    return [
      '#theme' => 'lms_my_certificates',
      '#certificates' => $certs,
    ];
  }

  /**
   * User profile page.
   */
  public function profile(): array {
    $uid = (int) $this->currentUser->id();
    $account = $this->entityTypeManager->getStorage('user')->load($uid);

    if ($account) {
      return $this->entityTypeManager->getViewBuilder('user')->view($account, 'full');
    }

    return [];
  }

}
