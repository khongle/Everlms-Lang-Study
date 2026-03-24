<?php

namespace Drupal\lms_forum\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override the canonical route of Forum Topic to use our custom controller.
    if ($route = $collection->get('entity.lms_forum_topic.canonical')) {
      $route->setDefault('_controller', '\Drupal\lms_forum\Controller\LmsForumController::viewTopic');
    }
  }

}
