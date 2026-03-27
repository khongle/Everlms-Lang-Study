<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\lms_lesson\Entity\Lesson;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ZoomJoinBlock' block.
 *
 * @Block(
 *  id = "lms_zoom_join_block",
 *  admin_label = @Translation("Zoom Join Class Block"),
 *  context_definitions = {
 *    "lms_lesson" = @ContextDefinition("entity:lms_lesson", required = FALSE, label = @Translation("Lesson"))
 *  }
 * )
 */
class ZoomJoinBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new ZoomJoinBlock.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly RouteMatchInterface $routeMatch
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Try to get lesson from context or route.
    $lesson = $this->getContextValue('lms_lesson') ?: $this->routeMatch->getParameter('lms_lesson');

    if (!$lesson instanceof Lesson || $lesson->get('field_type')->value !== 'zoom') {
      return [];
    }

    $meetingId = $lesson->get('field_zoom_meeting_id')->value;
    $scheduleTime = (int) $lesson->get('field_zoom_schedule')->value;

    if (!$meetingId || !$scheduleTime) {
      return [];
    }

    return [
      '#theme' => 'lms_zoom_join_block',
      '#lesson' => $lesson,
      '#meeting_id' => $meetingId,
      '#schedule_time' => $scheduleTime,
      '#attached' => [
        'library' => [
          'lms_zoom/countdown',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
