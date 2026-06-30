<?php

declare(strict_types=1);

namespace Drupal\lms_certificate\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_certificate\Service\CertificateManager;
use Drupal\lms_course\Entity\Course;
use Drupal\lms_quiz\Event\QuizPassedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CertificateEventListener implements EventSubscriberInterface {

  public function __construct(
    protected CertificateManager $certificateManager,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function getSubscribedEvents(): array {
    return [
      QuizPassedEvent::EVENT_NAME => ['onQuizPassed'],
    ];
  }

  public function onQuizPassed(QuizPassedEvent $event): void {
    $quiz = $event->getQuiz();
    $user = $this->entityTypeManager->getStorage('user')->load($event->getUser()->id());
    $course = NULL;

    if ($quiz->hasField('field_course') && !$quiz->get('field_course')->isEmpty()) {
      $course = $quiz->get('field_course')->entity;
    }

    if (!$course instanceof Course) {
      $course = $this->loadCourseFromLessonHierarchy((int) $quiz->id());
    }

    if ($course instanceof Course && $user) {
      $this->certificateManager->checkAndIssueCertificate($user, $course, $event->getQuizResult());
    }
  }

  protected function loadCourseFromLessonHierarchy(int $quiz_id): ?Course {
    $lesson_ids = $this->entityTypeManager->getStorage('lms_lesson')->getQuery()
      ->condition('field_quiz', $quiz_id)
      ->accessCheck(FALSE)
      ->execute();

    if (empty($lesson_ids)) {
      return NULL;
    }

    $chapter_ids = $this->entityTypeManager->getStorage('lms_chapter')->getQuery()
      ->condition('field_lessons', reset($lesson_ids))
      ->accessCheck(FALSE)
      ->execute();

    if (empty($chapter_ids)) {
      return NULL;
    }

    $course_ids = $this->entityTypeManager->getStorage('lms_course')->getQuery()
      ->condition('field_chapters', reset($chapter_ids))
      ->accessCheck(FALSE)
      ->execute();

    if (empty($course_ids)) {
      return NULL;
    }

    $course = $this->entityTypeManager->getStorage('lms_course')->load(reset($course_ids));

    return $course instanceof Course ? $course : NULL;
  }

}
