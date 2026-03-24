<?php

declare(strict_types=1);

namespace Drupal\lms_notification\EventSubscriber;

use Drupal\lms_notification\NotificationManager;
use Drupal\lms_quiz\Event\QuizPassedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to core LMS events to dispatch notifications.
 */
class NotificationEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs the NotificationEventSubscriber.
   */
  public function __construct(
    protected NotificationManager $notificationManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    // Only subscribe if the QuizPassedEvent class is available.
    if (class_exists('Drupal\lms_quiz\Event\QuizPassedEvent')) {
      $events[QuizPassedEvent::EVENT_NAME] = ['onQuizPassed'];
    }
    return $events;
  }

  /**
   * Handles the quiz passed event.
   */
  public function onQuizPassed(QuizPassedEvent $event): void {
    $user = $event->getUser();
    $quiz = $event->getQuiz();
    $result = $event->getQuizResult();

    $title = 'Congratulations on passing!';
    
    // Attempt to get score safely.
    $score = method_exists($result, 'getScore') ? $result->getScore() : 'passing';
    $message = sprintf('You have successfully passed the quiz: %s with a %s score.', 
      $quiz->label(), 
      $score
    );

    $link = method_exists($quiz, 'toUrl') ? $quiz->toUrl()->toString() : '';

    $this->notificationManager->dispatch(
      'quiz_result',
      $user,
      $title,
      $message,
      $link,
      ['in_app', 'email'],
      $quiz
    );
  }

}
