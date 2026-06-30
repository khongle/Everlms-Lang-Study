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

    // The entity add route uses the same /forum/new path as the custom route.
    // Make sure direct visits to /forum/new use the product page wrapper rather
    // than Drupal's raw entity add form.
    if ($route = $collection->get('entity.lms_forum_topic.add_form')) {
      $defaults = $route->getDefaults();
      unset($defaults['_entity_form']);
      $defaults['_controller'] = '\Drupal\lms_forum\Controller\LmsForumController::addTopic';
      $defaults['_title'] = 'New Topic';
      $route->setDefaults($defaults);
      $route->setRequirement('_entity_create_access', 'lms_forum_topic');
      $route->setOption('_admin_route', FALSE);
    }
  }

}
