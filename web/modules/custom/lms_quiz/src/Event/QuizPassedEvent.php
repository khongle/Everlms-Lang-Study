<?php

namespace Drupal\lms_quiz\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Drupal\lms_quiz\Entity\QuizResult;
use Drupal\lms_quiz\Entity\Quiz;
use Drupal\Core\Session\AccountInterface;

class QuizPassedEvent extends Event {

  public const EVENT_NAME = 'lms_quiz.passed';

  protected QuizResult $quizResult;
  protected Quiz $quiz;
  protected AccountInterface $user;

  public function __construct(QuizResult $quiz_result, Quiz $quiz, AccountInterface $user) {
    $this->quizResult = $quiz_result;
    $this->quiz = $quiz;
    $this->user = $user;
  }

  public function getQuizResult(): QuizResult {
    return $this->quizResult;
  }

  public function getQuiz(): Quiz {
    return $this->quiz;
  }

  public function getUser(): AccountInterface {
    return $this->user;
  }

}
