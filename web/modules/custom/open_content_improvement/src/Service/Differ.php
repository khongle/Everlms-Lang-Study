<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

/**
 * Field-level diff between the edited tree payload and the live entity.
 */
final class Differ {

  public function __construct(private readonly SidecarStore $sidecarStore) {}

  /**
   * @return array<string, array{old: mixed, new: mixed}>
   */
  public function diffFields(array $newValues, \Drupal\Core\Entity\ContentEntityInterface $live): array {
    $diffs = [];
    foreach ($newValues as $field => $deltas) {
      if (!$live->hasField($field)) {
        continue;
      }
      $old = $live->get($field)->getValue();
      $new = array_values($deltas);
      if ($this->normalize($old) !== $this->normalize($new)) {
        $diffs[$field] = ['old' => $old, 'new' => $new];
      }
    }
    return $diffs;
  }

  public function renderMarkdown(array $diffs): string {
    if (!$diffs) {
      return '_No changes._';
    }
    $out = [];
    foreach ($diffs as $field => $d) {
      $out[] = "### `$field`";
      $out[] = '```diff';
      $out[] = '- ' . $this->short($d['old']);
      $out[] = '+ ' . $this->short($d['new']);
      $out[] = '```';
    }
    return implode("\n", $out) . "\n";
  }

  private function normalize(mixed $v): string {
    return json_encode($this->intersectStrings($v)) ?: '';
  }

  private function intersectStrings(mixed $v): mixed {
    if (is_array($v)) {
      $out = [];
      foreach ($v as $k => $item) {
        if (is_array($item)) {
          // Compare only the columns present in the new value.
          $out[$k] = $item;
        }
        else {
          $out[$k] = $item;
        }
      }
      return $out;
    }
    return $v;
  }

  private function short(mixed $v): string {
    $s = is_string($v) ? $v : (json_encode($v) ?: '');
    return mb_substr($s, 0, 200);
  }
}
