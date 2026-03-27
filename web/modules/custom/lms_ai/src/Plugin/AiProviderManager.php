<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\lms_ai\Annotation\AiProvider;

/**
 * Manages discovery and instantiation of AI provider plugins.
 */
class AiProviderManager extends DefaultPluginManager {

  /**
   * Constructs a new AiProviderManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/AiProvider',
      $namespaces,
      $module_handler,
      AiProviderInterface::class,
      AiProvider::class
    );

    $this->alterInfo('lms_ai_provider_info');
    $this->setCacheBackend($cache_backend, 'lms_ai_provider_plugins');
  }

}
