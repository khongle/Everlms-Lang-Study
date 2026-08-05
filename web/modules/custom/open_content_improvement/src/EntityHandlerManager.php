<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\open_content_improvement\Attribute\EntityHandler;

/**
 * Plugin manager for entity handlers. Ships exactly one plugin (GenericEntity).
 */
final class EntityHandlerManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/OpenContentImprovement/Entity',
      $namespaces,
      $module_handler,
      EntityHandlerInterface::class,
      EntityHandler::class,
    );
    $this->alterInfo('open_content_improvement_entity_handler_info');
    $this->setCacheBackend($cache, 'oci_entity_handler_plugins');
  }

  private function weightedDefinitions(): array {
    $definitions = $this->getDefinitions();
    uasort($definitions, static fn(array $a, array $b) => ($a['weight'] ?? 0) <=> ($b['weight'] ?? 0));
    return $definitions;
  }

  public function handlerFor(EntityTypeInterface $type): EntityHandlerInterface {
    foreach ($this->weightedDefinitions() as $id => $def) {
      $types = $def['entity_types'] ?? ['*'];
      if ($types !== ['*'] && !in_array($type->id(), $types, TRUE)) {
        continue;
      }
      $plugin = $this->createInstance($id);
      if ($plugin->applies($type)) {
        return $plugin;
      }
    }
    throw new \LogicException('The generic entity handler was starved.');
  }
}
