<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;

/**
 * Resolves, prepares and hardens the export root directory.
 */
final class ExportRootResolver {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly FileSystemInterface $fileSystem,
    private readonly StreamWrapperManagerInterface $streamWrapperManager,
    private readonly StateInterface $state,
  ) {}

  public function root(): string {
    $config = $this->configFactory->get('open_content_improvement.settings');
    $root = $config->get('export_root') ?: 'public://open_content_improvement';
    if (($config->get('export_root_hardening') ?: 'htaccess_only') === 'random_segment') {
      $seg = $config->get('random_segment');
      if (is_string($seg) && $seg !== '') {
        $root = rtrim($root, '/') . '/' . $seg;
      }
    }
    return rtrim($root, '/');
  }

  public function scheme(): string {
    return $this->streamWrapperManager->getScheme($this->root() . '/') ?: 'public';
  }

  /**
   * Resolve a (possibly stream-wrapped) URI to a real local path. Falls back to
   * the input when it is already local or cannot be resolved. Needed wherever
   * glob()/scandir() must traverse the tree — they cannot follow public://.
   */
  public function realpath(string $uri): string {
    return $this->fileSystem->realpath($uri) ?: $uri;
  }

  public function prepare(): void {
    $root = $this->root();
    $this->fileSystem->prepareDirectory($root, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    // Deny web access under public://.
    $htaccess = <<<HT
# Open Content Improvement export root — deny all direct web access.
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
  Deny from all
</IfModule>
HT;
    $this->writeFile($root . '/.htaccess', $htaccess);
    $this->writeFile($root . '/web.config', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <authorization>\n      <deny users=\"*\" />\n    </authorization>\n  </system.webServer>\n</configuration>\n");
    $this->writeFile($root . '/index.html', "");
    $this->writeFile($root . '/.gitignore', "*\n");
  }

  public function securityVerdict(): array {
    $root = $this->root();
    $scheme = $this->scheme();
    $hasHtaccess = file_exists($root . '/.htaccess');
    $privateAvailable = $this->streamWrapperManager->isValidScheme('private');
    return [
      'root' => $root,
      'scheme' => $scheme,
      'htaccess' => $hasHtaccess,
      'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
      'private_available' => $privateAvailable,
      'recommendation' => $scheme === 'public'
        ? ($privateAvailable
          ? 'Switch export_root to private://open_content_improvement — this site has a private filesystem.'
          : 'No private filesystem configured; the .htaccess deny rule is the only protection. Configure a private filesystem for real access control.')
        : 'Export root is private:// — access is controlled by Drupal.',
    ];
  }

  private function writeFile(string $uri, string $contents): void {
    try {
      if (!file_exists($uri)) {
        $this->fileSystem->saveData($contents, $uri, FileSystemInterface::EXISTS_REPLACE);
      }
    }
    catch (\Throwable $e) {
      // Best-effort hardening; never fail the run on this.
    }
  }
}
