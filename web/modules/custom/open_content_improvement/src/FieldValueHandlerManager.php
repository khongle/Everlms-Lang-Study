<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\open_content_improvement\Attribute\FieldValueHandler;

/**
 * Plugin manager for field value handlers.
 */
final class FieldValueHandlerManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/OpenContentImprovement/FieldValue',
      $namespaces,
      $module_handler,
      FieldValueHandlerInterface::class,
      FieldValueHandler::class,
    );
    $this->alterInfo('open_content_improvement_field_value_handler_info');
    $this->setCacheBackend($cache, 'oci_field_value_handler_plugins');
  }

  /**
   * Definitions sorted by weight ascending.
   */
  private function weightedDefinitions(): array {
    $definitions = $this->getDefinitions();
    uasort($definitions, static fn(array $a, array $b) => ($a['weight'] ?? 0) <=> ($b['weight'] ?? 0));
    return $definitions;
  }

  /**
   * The first handler (by weight) whose field_types match and applies() is TRUE.
   */
  public function handlerFor(FieldDefinitionInterface $fd): FieldValueHandlerInterface {
    $type = $fd->getType();
    foreach ($this->weightedDefinitions() as $id => $def) {
      $types = $def['field_types'] ?? ['*'];
      if ($types !== ['*'] && !in_array($type, $types, TRUE)) {
        continue;
      }
      try {
        $plugin = $this->createInstance($id);
      }
      catch (PluginException $e) {
        continue;
      }
      if ($plugin->applies($fd)) {
        return $plugin;
      }
    }
    throw new \LogicException('The generic field value handler was starved.');
  }
}
