<?php

namespace Drupal\lms_certificate\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\lms_certificate\Service\CertificateManager;

class CertificateEventListener implements EventSubscriberInterface {

  protected CertificateManager $certificateManager;

  public function __construct(CertificateManager $certificate_manager) {
    $this->certificateManager = $certificate_manager;
  }

  public static function getSubscribedEvents() {
    $events = [];
    if (class_exists('\Drupal\lms_quiz\Event\QuizPassedEvent')) {
      $events['lms_quiz.passed'] = ['onQuizPassed'];
    }
    return $events;
  }

  public function onQuizPassed($event) {
    // Only process if the event matches our custom class.
    if ($event instanceof \Drupal\lms_quiz\Event\QuizPassedEvent) {
      $quiz = $event->getQuiz();
      $account = $event->getUser();
      $quiz_result = $event->getQuizResult();
      
      // Load the actual user entity.
      $user = \Drupal::entityTypeManager()->getStorage('user')->load($account->id());
      
      // Attempt to load the course related to this quiz.
      // Hierarchy: Course -> Chapter -> Lesson -> Quiz
      $course = NULL;
      $entity_type_manager = \Drupal::entityTypeManager();

      // 1. Find the Lesson referencing this Quiz.
      $lesson_ids = $entity_type_manager->getStorage('lms_lesson')->getQuery()
        ->condition('field_quiz', $quiz->id())
        ->accessCheck(FALSE)
        ->execute();
      
      if (!empty($lesson_ids)) {
        $lesson_id = reset($lesson_ids);
        // 2. Find the Chapter referencing this Lesson.
        $chapter_ids = $entity_type_manager->getStorage('lms_chapter')->getQuery()
          ->condition('field_lessons', $lesson_id)
          ->accessCheck(FALSE)
          ->execute();
        
        if (!empty($chapter_ids)) {
          $chapter_id = reset($chapter_ids);
          // 3. Find the Course referencing this Chapter.
          $course_ids = $entity_type_manager->getStorage('lms_course')->getQuery()
            ->condition('field_chapters', $chapter_id)
            ->accessCheck(FALSE)
            ->execute();
          
          if (!empty($course_ids)) {
            $course_id = reset($course_ids);
            $course = $entity_type_manager->getStorage('lms_course')->load($course_id);
          }
        }
      }

      if ($course && $user) {
        $this->certificateManager->checkAndIssueCertificate($user, $course, $quiz_result);
      }
    }
  }

}
