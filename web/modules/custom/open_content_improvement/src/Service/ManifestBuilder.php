<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleExtensionList;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds and verifies manifest.yml — the last file written on export, and the
 * gate the importer checks first (a half-written tree has no manifest).
 */
final class ManifestBuilder {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly Connection $database,
    private readonly RequestStack $requestStack,
    private readonly ModuleExtensionList $moduleList,
    private readonly EntityTypeDiscovery $discovery,
  ) {}

  public function build(array $summary): array {
    return [
      'format' => 'oci-entity-v1',
      'algorithm' => 'ocim-canon-v1',
      'created' => date('c'),
      'run_id' => $summary['run_id'] ?? '',
      'fingerprint' => $this->fingerprint(),
      'counts' => $summary['counts'] ?? [],
      'entity_types' => $summary['entity_types'] ?? [],
    ];
  }

  public function write(string $root, array $manifest): void {
    file_put_contents(rtrim($root, '/') . '/manifest.yml', "# oci manifest — written last.\n" . Yaml::encode($manifest));
  }

  public function read(string $root): ?array {
    $path = rtrim($root, '/') . '/manifest.yml';
    if (!file_exists($path)) {
      return NULL;
    }
    return Yaml::decode(file_get_contents($path) ?: '') ?: NULL;
  }

  public function fingerprint(): array {
    $request = $this->requestStack->getCurrentRequest();
    $options = $this->database->getConnectionOptions();
    $dbSig = hash('sha256', ($options['host'] ?? '') . '|' . ($options['database'] ?? ''));
    return [
      'site_uuid' => $this->configFactory->get('system.site')->get('uuid'),
      'base_url' => $request ? $request->getSchemeAndHttpHost() : '',
      'db_sig' => $dbSig,
    ];
  }
}
