<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\open_content_improvement\EntityHandlerManager;
use Drupal\open_content_improvement\ExportContext;
use Drupal\open_content_improvement\FieldSurface;
use Drupal\open_content_improvement\FieldValueHandlerManager;
use Psr\Log\LoggerInterface;

/**
 * Writes the export tree: one folder per entity, each with entity.yml
 * (blocks _meta/_context/content/_system/_files) plus text/ sidecars and
 * files/ binaries. manifest.yml is written last.
 */
final class Exporter {

  public function __construct(
    private readonly EntityTypeDiscovery $discovery,
    private readonly FieldClassifier $classifier,
    private readonly EntityHandlerManager $entityHandlers,
    private readonly FieldValueHandlerManager $fieldHandlers,
    private readonly CanonicalHasher $hasher,
    private readonly AnnotatedYamlWriter $yamlWriter,
    private readonly SidecarStore $sidecarStore,
    private readonly BinaryStore $binaryStore,
    private readonly ManifestBuilder $manifest,
    private readonly DocGenerator $docGenerator,
    private readonly ExportRootResolver $exportRoot,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LanguageManagerInterface $languageManager,
    private readonly TransliterationInterface $transliteration,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Run a full export.
   *
   * @return array
   *   {root, run_id, counts, entity_types, skipped}
   */
  public function export(ExportContext $ctx): array {
    $root = $ctx->targetDir ?: $this->exportRoot->root();
    $this->exportRoot->prepare();
    if ($ctx->targetDir && !is_dir($root)) {
      @mkdir($root, 0775, TRUE);
    }

    $types = $this->discovery->exportableTypes();
    if ($ctx->entityTypes) {
      $types = array_intersect_key($types, array_flip($ctx->entityTypes));
    }

    $counts = [];
    $exportedTypeCounts = [];
    $skipped = $this->discovery->skipReasons();

    foreach ($types as $typeId => $descriptor) {
      $type = $this->entityTypeManager->getDefinition($typeId);
      // EX-G7: skip an all-plumbing type.
      $bundles = $descriptor['bundles'] ?: [$typeId];
      $allEmpty = TRUE;
      foreach ($bundles as $bundle) {
        if (!$this->classifier->isTypeEmpty($typeId, $bundle)) {
          $allEmpty = FALSE;
          break;
        }
      }
      if ($allEmpty) {
        $skipped[$typeId] = 'all-fields-plumbing';
        continue;
      }

      $handler = $this->entityHandlers->handlerFor($type);
      $typeDir = rtrim($root, '/') . '/' . $handler->directoryName($type);
      @mkdir($typeDir, 0775, TRUE);

      $storage = $this->entityTypeManager->getStorage($typeId);
      $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
      $chunkSize = (int) ($this->configFactory->get('open_content_improvement.settings')->get('chunk_size') ?: 50);
      $count = 0;
      foreach (array_chunk(array_values($ids), $chunkSize) as $chunk) {
        foreach ($storage->loadMultiple($chunk) as $entity) {
          if (!$entity instanceof ContentEntityInterface) {
            continue;
          }
          try {
            $this->writeEntity($entity, $typeDir, $ctx);
            $count++;
          }
          catch (\Throwable $e) {
            $this->logger->error('Export failed for @t:@id: @m', [
              '@t' => $typeId, '@id' => $entity->id(), '@m' => $e->getMessage(),
            ]);
          }
        }
      }
      $counts[$typeId] = $count;
      $exportedTypeCounts[$typeId] = $count;
    }

    // Docs.
    file_put_contents(rtrim($root, '/') . '/README.md', $this->docGenerator->indexMd($exportedTypeCounts));
    file_put_contents(rtrim($root, '/') . '/_skipped.yml', $this->docGenerator->skippedYml($skipped));
    file_put_contents(rtrim($root, '/') . '/GLOSSARY.md', $this->docGenerator->glossaryMd());

    // Manifest LAST.
    $manifest = $this->manifest->build([
      'run_id' => $ctx->runId,
      'counts' => $counts,
      'entity_types' => array_keys($counts),
    ]);
    $this->manifest->write($root, $manifest);

    return [
      'root' => $root,
      'run_id' => $ctx->runId,
      'counts' => $counts,
      'entity_types' => array_keys($counts),
      'skipped' => $skipped,
    ];
  }

  /**
   * Write a full pre-image of one entity into a backup/snapshot root, reusing
   * the same writer, classifier and hashing as a normal export — so the backup
   * IS a valid import tree (SC-15). Call finalizeSnapshot() once afterwards to
   * write the manifest the scanner requires.
   */
  public function snapshotEntity(ContentEntityInterface $entity, string $backupRoot): void {
    @mkdir($backupRoot, 0775, TRUE);
    $type = $entity->getEntityType();
    $handler = $this->entityHandlers->handlerFor($type);
    $typeDir = rtrim($backupRoot, '/') . '/' . $handler->directoryName($type);
    @mkdir($typeDir, 0775, TRUE);
    $this->writeEntity($entity, $typeDir, new ExportContext(targetDir: $backupRoot));
  }

  /**
   * Write the manifest that makes a snapshot root a scannable import tree.
   */
  public function finalizeSnapshot(string $backupRoot, string $runId, array $counts): void {
    $manifest = $this->manifest->build([
      'run_id' => $runId,
      'counts' => $counts,
      'entity_types' => array_keys($counts),
    ]);
    $this->manifest->write($backupRoot, $manifest);
  }

  private function writeEntity(ContentEntityInterface $entity, string $typeDir, ExportContext $ctx): void {
    $dir = $typeDir . '/' . $this->slug($entity);
    @mkdir($dir, 0775, TRUE);

    $langcodes = $ctx->langcodes;
    $default = $entity->language()->getId();
    $available = array_keys($entity->getTranslationLanguages(TRUE));
    // Robustness: always include at least the entity's own language, even when
    // the language manager reports no translations in a CLI/bootstrap context.
    if (!in_array($default, $available, TRUE)) {
      $available[] = $default;
    }
    $payloadByLang = [];
    foreach ($available as $langcode) {
      if (!empty($langcodes) && !in_array($langcode, $langcodes, TRUE)) {
        continue;
      }
      $translation = $entity->hasTranslation($langcode) ? $entity->getTranslation($langcode) : $entity;
      $payloadByLang[$langcode] = $this->buildPayload($translation, $dir);
    }

    $blocks = $this->assembleBlocks($entity, $payloadByLang, $default);
    $this->yamlWriter->write($dir . '/entity.yml', $blocks);
  }

  /**
   * @return array{content: array, system: array, files: array}
   */
  private function buildPayload(ContentEntityInterface $translation, string $dir): array {
    $type = $translation->getEntityType();
    $content = [];
    $system = [];
    $files = [];
    foreach ($translation->getFields() as $name => $items) {
      $fd = $items->getFieldDefinition();
      $result = $this->classifier->classify($fd, $type, $items);
      if ($result->surface === FieldSurface::Skip) {
        continue;
      }
      $handler = $this->fieldHandlers->handlerFor($fd);
      $exported = [];
      $delta = 0;
      foreach ($items as $item) {
        if ($result->surface === FieldSurface::File) {
          $files[$name][$delta] = $this->binaryStore->exportItem($item, $dir);
        }
        else {
          $exported[$delta] = $this->exportValue($handler->exportItem($item, $fd), $name, $delta, $translation->language()->getId(), $dir);
        }
        $delta++;
      }
      if ($result->surface === FieldSurface::File) {
        continue;
      }
      if ($result->surface === FieldSurface::Content) {
        $content[$name] = $exported;
      }
      else {
        $system[$name] = $exported;
      }
    }
    return ['content' => $content, 'system' => $system, 'files' => $files];
  }

  /**
   * Hoist long strings to sidecars.
   */
  private function exportValue(mixed $value, string $field, int $delta, string $langcode, string $dir): mixed {
    if (is_array($value)) {
      foreach ($value as $col => $v) {
        if (is_string($v) && mb_strlen($v) > $this->sidecarStore->threshold()) {
          $side = $this->sidecarStore->put($dir, $field . '.' . $col, $delta, $langcode, $v);
          $value[$col] = ['_sidecar' => $side['file'], 'sha256' => $side['sha256']];
        }
      }
      return $value;
    }
    if (is_string($value) && mb_strlen($value) > $this->sidecarStore->threshold()) {
      $side = $this->sidecarStore->put($dir, $field, $delta, $langcode, $value);
      return ['_sidecar' => $side['file'], 'sha256' => $side['sha256']];
    }
    return $value;
  }

  private function assembleBlocks(ContentEntityInterface $entity, array $payloadByLang, string $default): array {
    $meta = [
      'entity_type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
      'id' => (string) $entity->id(),
      'uuid' => $entity->uuid(),
      'default_langcode' => $default,
      'langcodes' => array_keys($payloadByLang),
      'algorithm' => $this->hasher->algorithmId(),
      'meta_key_id' => $this->hasher->metaKeyId(),
    ];
    // Per-language source hash (H1) for staleness. Computed over the RAW field
    // values via the same code path the importer uses (livePayloadHash), NOT
    // over the sidecar-hoisted display payload — otherwise a hoisted field would
    // always read as stale, since export hashes the {_sidecar, sha256} marker
    // while the importer re-hashes the live raw text.
    $hashes = [];
    foreach (array_keys($payloadByLang) as $lang) {
      $hashes[$lang] = $this->livePayloadHash($entity, $lang);
    }
    $meta['source_hash'] = $hashes;
    $meta['meta_sig'] = $this->hasher->sealMeta($meta);

    $content = [];
    $system = [];
    $files = [];
    foreach ($payloadByLang as $lang => $p) {
      if ($p['content']) {
        $content[$lang] = $p['content'];
      }
      if ($p['system']) {
        $system[$lang] = $p['system'];
      }
      if ($p['files']) {
        $files[$lang] = $p['files'];
      }
    }

    $blocks = ['_meta' => $meta];
    $blocks['_context'] = ['label' => (string) ($entity->label() ?? $entity->id())];
    if ($content) {
      $blocks['content'] = $content;
    }
    if ($system) {
      $blocks['_system'] = $system;
    }
    if ($files) {
      $blocks['_files'] = $files;
    }
    return $blocks;
  }

  /**
   * Live H1 hash for staleness at import time — same serialize path as export.
   */
  public function livePayloadHash(ContentEntityInterface $entity, string $langcode): string {
    $translation = $entity->hasTranslation($langcode) ? $entity->getTranslation($langcode) : $entity;
    // Build payload without writing sidecars/files to disk.
    $type = $translation->getEntityType();
    $content = [];
    $system = [];
    $files = [];
    foreach ($translation->getFields() as $name => $items) {
      $fd = $items->getFieldDefinition();
      $result = $this->classifier->classify($fd, $type, $items);
      if ($result->surface === FieldSurface::Skip) {
        continue;
      }
      $handler = $this->fieldHandlers->handlerFor($fd);
      $delta = 0;
      foreach ($items as $item) {
        if ($result->surface === FieldSurface::File) {
          $files[$name][$delta] = $this->binaryStore->exportItem($item, sys_get_temp_dir());
        }
        elseif ($result->surface === FieldSurface::Content) {
          $content[$name][$delta] = $handler->exportItem($item, $fd);
        }
        else {
          $system[$name][$delta] = $handler->exportItem($item, $fd);
        }
        $delta++;
      }
    }
    return $this->hasher->sourceHash(['content' => $content, 'system' => $system, 'files' => $files]);
  }

  private function slug(ContentEntityInterface $entity): string {
    $label = $entity->label();
    $base = is_string($label) && $label !== '' ? $label : $entity->getEntityTypeId();
    $slug = mb_strtolower($this->transliteration->transliterate($base, 'en', '-'));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    if ($slug === '') {
      $slug = $entity->getEntityTypeId();
    }
    return $entity->id() . '--' . mb_substr($slug, 0, 60);
  }
}
