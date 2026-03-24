<?php

declare(strict_types=1);

namespace Drupal\lms_membership\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Redirects 403 Access Denied requests back to pricing for membership courses.
 */
class MembershipAccessSubscriber implements EventSubscriberInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new MembershipAccessSubscriber.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Handles 403 exceptions.
   */
  public function onException(ExceptionEvent $event) {
    if ($event->getThrowable() instanceof AccessDeniedHttpException) {
      $request = $event->getRequest();
      $path = $request->getPathInfo();

      // If the user is trying to access a course or lesson (e.g., /learn/X/Y)
      if (strpos($path, '/learn/') === 0 || strpos($path, '/course/') === 0) {
        $this->messenger->addWarning(t('You must have an active Membership Plan to access this exclusive content.'));
        $url = Url::fromRoute('lms_course.pricing')->toString();
        $response = new RedirectResponse($url);
        $event->setResponse($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['onException', 50];
    return $events;
  }

}
