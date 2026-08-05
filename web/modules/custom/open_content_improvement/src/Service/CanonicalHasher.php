<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\State\StateInterface;

/**
 * Canonical serialization + hashing. Algorithm id: ocim-canon-v1.
 *
 * H1 = source hash of a canonical payload (staleness). H2/H3 = sha256 of file
 * bytes. meta_sig = HMAC-SHA256 of the _meta block minus meta_sig itself.
 */
final class CanonicalHasher {

  public function __construct(private readonly StateInterface $state) {}

  public function algorithmId(): string {
    return 'ocim-canon-v1';
  }

  /**
   * Recursively ksort an array to make serialization deterministic.
   */
  public function canonicalize(mixed $data): mixed {
    if (!is_array($data)) {
      return $data;
    }
    $out = [];
    // Preserve list order; sort associative maps by key.
    $isList = array_is_list($data);
    foreach ($data as $k => $v) {
      $out[$k] = $this->canonicalize($v);
    }
    if (!$isList) {
      ksort($out);
    }
    return $out;
  }

  public function sourceHash(array $canonical_payload): string {
    $canon = $this->canonicalize($canonical_payload);
    return hash('sha256', $this->algorithmId() . "\n" . Yaml::encode($canon));
  }

  public function sealMeta(array $meta): string {
    unset($meta['meta_sig']);
    $canon = $this->canonicalize($meta);
    return hash_hmac('sha256', Yaml::encode($canon), $this->metaKey());
  }

  public function verifyMeta(array $meta): bool {
    $sig = $meta['meta_sig'] ?? NULL;
    if (!is_string($sig) || $sig === '') {
      return FALSE;
    }
    return hash_equals($this->sealMeta($meta), $sig);
  }

  public function fileHash(string $uri): string {
    $bytes = @file_get_contents($uri);
    return $bytes === FALSE ? '' : hash('sha256', $bytes);
  }

  public function stringHash(string $bytes): string {
    return hash('sha256', $bytes);
  }

  private function metaKey(): string {
    $key = $this->state->get('open_content_improvement.meta_key');
    if (!is_string($key) || $key === '') {
      // Deterministic fallback (kernel tests without install hook).
      $key = 'oci-insecure-default-key';
    }
    return $key;
  }

  public function metaKeyId(): int {
    return (int) ($this->state->get('open_content_improvement.meta_key_id') ?: 1);
  }
}
