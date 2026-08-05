<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\open_content_improvement\EntityHandlerManager;
use Drupal\open_content_improvement\Exception\OciAbortException;
use Drupal\open_content_improvement\FieldValueHandlerManager;
use Drupal\open_content_improvement\ImportContext;
use Psr\Log\LoggerInterface;

/**
 * The import engine (Phase 9–11 of 02-import-spec).
 *
 * Dry-run by default; writes only with --apply. Never creates or deletes an
 * entity, never adds or removes a translation. Identity is resolved by
 * _meta.uuid only. Every write:
 *  - is preceded by a full pre-image snapshot (SC-15);
 *  - runs inside its own DB transaction, on a freshly reloaded entity, under the
 *    resolved actor account (so entity/field/text-format access is judged
 *    against a real editor, exactly as the UI would);
 *  - re-checks staleness (TOCTOU) and access INSIDE the transaction;
 *  - merges content columns onto the FULL live item so multi-column fields never
 *    lose their frozen _system columns;
 *  - is validate()d before save() and post-verified after, so a third module's
 *    presave hook rewriting the result surfaces as `applied-diverged`.
 */
final class ImportWriter {

  public function __construct(
    private readonly Exporter $exporter,
    private readonly ImportValidator $validator,
    private readonly Differ $differ,
    private readonly TreeScanner $scanner,
    private readonly FieldValueHandlerManager $fieldHandlers,
    private readonly EntityHandlerManager $entityHandlers,
    private readonly CanonicalHasher $hasher,
    private readonly BinaryStore $binaryStore,
    private readonly RunJournal $journal,
    private readonly AuditLogger $audit,
    private readonly ExportRootResolver $exportRoot,
    private readonly EntityRepositoryInterface $entityRepository,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly Connection $database,
    private readonly AccountProxyInterface $currentUser,
    private readonly TimeInterface $time,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
    private readonly AccountSwitcherInterface $accountSwitcher,
  ) {}

  public function dryRun(ImportContext $ctx): array {
    return $this->run($ctx->apply ? new ImportContext($ctx->rootDir, FALSE, $ctx->entityTypes, $ctx->runId) : $ctx, FALSE);
  }

  public function apply(ImportContext $ctx): array {
    $ctx->apply = TRUE;
    return $this->run($ctx, TRUE);
  }

  /**
   * @return array
   *   {apply, planned, changed, refused, skipped, units, diverged, snapshot,
   *   hard_refused}
   */
  private function run(ImportContext $ctx, bool $apply): array {
    $config = $this->configFactory->get('open_content_improvement.settings');
    $root = $ctx->rootDir ?: $this->exportRoot->root();
    $scan = $this->scanner->scan($root);

    $importable = $config->get('importable_entity_types') ?: [];
    $immutable = $config->get('immutable_entity_types') ?: [];
    // Ships EMPTY: a text-format change is a filter-permission decision, refused
    // unless the operator has explicitly allowlisted the new format id here.
    $writableFormats = $config->get('writable_text_formats') ?: [];
    $newRevisionDefault = (bool) $config->get('create_new_revision');

    $report = [
      'apply' => $apply,
      'root' => $root,
      'planned' => 0,
      'changed' => 0,
      'refused' => 0,
      'skipped' => 0,
      'units' => [],
      'diverged' => [],
    ];

    $backupRoot = rtrim($root, '/') . '/_backup/' . date('Ymd-His') . '--' . ($ctx->runId ?: 'run');

    $hardRefused = 0;
    $snapshotted = [];
    $snapshotCounts = [];
    // Units actually saved this run — post-verified after the loop.
    $appliedUnits = [];

    // A write must run as a real account so entity/field/text-format access is
    // evaluated against an editor, not the CLI's anonymous session.
    $actor = NULL;
    $switched = FALSE;
    if ($apply) {
      $actor = $this->resolveActor($ctx);
      if ($actor === NULL) {
        throw new OciAbortException(
          'No actor available to write as. Run as an authenticated user (drush --user=NAME) or pass --actor=UID.',
          'no_actor'
        );
      }
      $this->accountSwitcher->switchTo($actor);
      $switched = TRUE;
    }

    try {
      foreach ($scan['units'] as $entityDir) {
        $blocks = $this->readEntity($entityDir);
        if ($blocks === NULL) {
          continue;
        }
        $meta = $blocks['_meta'] ?? [];
        $typeId = $meta['entity_type'] ?? '';
        $uuid = $meta['uuid'] ?? '';
        $report['planned']++;

        // Scope filter.
        if ($ctx->entityTypes && !in_array($typeId, $ctx->entityTypes, TRUE)) {
          $report['skipped']++;
          continue;
        }
        // Allowlist / immutable.
        if (in_array($typeId, $immutable, TRUE)) {
          $report['skipped']++;
          $this->recordUnit($report, $typeId, $uuid, 'skipped', 'immutable', []);
          continue;
        }
        if (!in_array($typeId, $importable, TRUE) && !$this->hookAllows($typeId, NULL)) {
          $report['skipped']++;
          $this->recordUnit($report, $typeId, $uuid, 'skipped', 'not-importable', []);
          continue;
        }

        // Identity: uuid only. Never create.
        $live = $uuid ? $this->entityRepository->loadEntityByUuid($typeId, $uuid) : NULL;
        if (!$live instanceof ContentEntityInterface) {
          $report['refused']++;
          $hardRefused++;
          $this->recordUnit($report, $typeId, $uuid, 'refused', 'no-such-entity', []);
          continue;
        }

        $langcodes = $meta['langcodes'] ?? [$live->language()->getId()];
        foreach ($langcodes as $langcode) {
          $translation = $live->hasTranslation($langcode) ? $live->getTranslation($langcode) : NULL;
          if ($translation === NULL) {
            // Only write existing translations (T2b) — never add one.
            $this->recordUnit($report, $typeId, $uuid, 'skipped', 'translation-absent:' . $langcode, []);
            continue;
          }

          $liveHash = $this->exporter->livePayloadHash($live, $langcode);
          $verdict = $this->validator->validateUnit($meta, $blocks, $translation, $langcode, $liveHash);
          if (!$verdict['ok']) {
            $report['refused']++;
            if ($verdict['hard']) {
              $hardRefused++;
            }
            $this->recordUnit($report, $typeId, $uuid, 'refused', $verdict['reason'], []);
            continue;
          }

          $contentBlock = $blocks['content'][$langcode] ?? [];
          $newValues = $this->validator->editableChanges($contentBlock, $translation, $entityDir);
          $diffs = $this->differ->diffFields($newValues, $translation);

          // Staleness. A rollback judges against what the run it undoes actually
          // wrote (§11.1); a normal run against the export-time source hash.
          if ($ctx->rollbackOfRun !== '') {
            $appliedHash = $this->journal->appliedHash($ctx->rollbackOfRun, $uuid, $langcode);
            if ($appliedHash !== NULL && $liveHash !== $appliedHash && $diffs) {
              // Someone edited the entity after the run being rolled back; a blind
              // rollback would erase that edit. Refuse and name it.
              $report['refused']++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'modified-since-run', array_keys($diffs));
              continue;
            }
          }
          elseif ($verdict['reason'] === 'stale' && $diffs && !$ctx->acceptStaleTree) {
            $report['refused']++;
            $this->recordUnit($report, $typeId, $uuid, 'refused', 'stale', array_keys($diffs));
            continue;
          }

          if (!$diffs) {
            // Idempotent: the tree's edit is already live (or nothing to do).
            $already = $this->journal->appliedHash($ctx->rollbackOfRun ?: $ctx->runId, $uuid, $langcode);
            $this->recordUnit($report, $typeId, $uuid, $already === $liveHash && $already !== NULL ? 'already-applied' : 'unchanged', '', []);
            continue;
          }

          if (!$apply) {
            $report['changed']++;
            $this->recordUnit($report, $typeId, $uuid, 'would-change', '', array_keys($diffs));
            continue;
          }

          // SC-15 — full pre-image snapshot (once per entity) before any write.
          if (!isset($snapshotted[$uuid])) {
            try {
              $this->exporter->snapshotEntity($live, $backupRoot);
              $snapshotted[$uuid] = TRUE;
              $snapshotCounts[$typeId] = ($snapshotCounts[$typeId] ?? 0) + 1;
            }
            catch (\Throwable $e) {
              $this->logger->error('Snapshot failed for @t:@id: @m', [
                '@t' => $typeId, '@id' => $live->id(), '@m' => $e->getMessage(),
              ]);
              $report['refused']++;
              $hardRefused++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'snapshot-failed', []);
              continue;
            }
          }

          // One transaction per entity, on a freshly reloaded object so a refused
          // sibling translation can never leak dirty in-memory state into save().
          $txn = $this->database->startTransaction();
          try {
            $this->entityTypeManager->getStorage($typeId)->resetCache([$live->id()]);
            $writeEntity = $this->entityRepository->loadEntityByUuid($typeId, $uuid);
            if (!$writeEntity instanceof ContentEntityInterface || !$writeEntity->hasTranslation($langcode)) {
              $this->recordUnit($report, $typeId, $uuid, 'skipped', 'translation-vanished:' . $langcode, []);
              unset($txn);
              continue;
            }
            $target = $writeEntity->getTranslation($langcode);

            // TOCTOU: re-check staleness inside the transaction.
            if ($ctx->rollbackOfRun === '' && !$ctx->acceptStaleTree
              && $this->exporter->livePayloadHash($writeEntity, $langcode) !== $liveHash) {
              $report['refused']++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'stale-at-write', array_keys($diffs));
              unset($txn);
              continue;
            }
            // Access may have changed between plan and write.
            if (!$ctx->bypassEntityAccess && $actor instanceof AccountInterface
              && !$writeEntity->access('update', $actor)) {
              $report['refused']++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'access-denied', array_keys($diffs));
              unset($txn);
              continue;
            }

            // Merge content columns onto the full live item (never overwrite the
            // whole item); guard against a text-format change.
            $formatRefused = FALSE;
            foreach ($newValues as $field => $deltas) {
              $merged = [];
              foreach ($deltas as $delta => $cols) {
                $liveItem = $target->get($field)->get($delta);
                $liveCols = $liveItem !== NULL ? $liveItem->getValue() : [];
                if (!is_array($liveCols)) {
                  $liveCols = [];
                }
                if (is_array($cols) && isset($cols['format'], $liveCols['format'])
                  && $cols['format'] !== $liveCols['format']
                  && !in_array($cols['format'], $writableFormats, TRUE)) {
                  $formatRefused = TRUE;
                  break 2;
                }
                $merged[$delta] = is_array($cols) ? array_replace($liveCols, $cols) : $cols;
              }
              $target->set($field, array_values($merged));
            }
            if ($formatRefused) {
              $report['refused']++;
              $hardRefused++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'text-format-changed', array_keys($diffs));
              unset($txn);
              continue;
            }

            // Satisfy the EntityChanged constraint on the edited translation.
            if ($target instanceof EntityChangedInterface) {
              $target->setChangedTime($this->time->getRequestTime());
            }
            if ($newRevisionDefault && $writeEntity->getEntityType()->isRevisionable() && method_exists($writeEntity, 'setNewRevision')) {
              $writeEntity->setNewRevision(TRUE);
            }

            $violations = $writeEntity->validate();
            if (count($violations) > 0) {
              $report['refused']++;
              $hardRefused++;
              $this->recordUnit($report, $typeId, $uuid, 'refused', 'validate-failed', array_keys($diffs));
              unset($txn);
              continue;
            }

            $expected = $this->exporter->livePayloadHash($writeEntity, $langcode);
            $writeEntity->save();
            $observed = $this->exporter->livePayloadHash($writeEntity, $langcode);

            $this->journal->mark($ctx->runId, $uuid, $langcode, 'done', $observed);
            foreach (array_keys($diffs) as $field) {
              $this->audit->log([
                'run_id' => $ctx->runId,
                'entity_type' => $typeId,
                'entity_id' => (string) $writeEntity->id(),
                'entity_uuid' => $uuid,
                'langcode' => $langcode,
                'field_name' => $field,
                'action' => 'update',
                'actor' => $ctx->actor ?: ($actor?->getAccountName() ?? $this->currentUser->getAccountName()),
                'reason' => $ctx->reason,
              ]);
            }
            $report['changed']++;
            $this->recordUnit($report, $typeId, $uuid, 'changed', '', array_keys($diffs));
            $appliedUnits[] = [
              'typeId' => $typeId,
              'uuid' => $uuid,
              'langcode' => $langcode,
              'expected' => $expected,
              'observed' => $observed,
            ];
          }
          catch (\Throwable $e) {
            if (isset($txn)) {
              $txn->rollBack();
            }
            $this->logger->error('Write failed for @t:@id: @m', [
              '@t' => $typeId, '@id' => $live->id(), '@m' => $e->getMessage(),
            ]);
            $report['refused']++;
            $hardRefused++;
            $this->recordUnit($report, $typeId, $uuid, 'refused', 'write-failed', array_keys($diffs));
            continue;
          }
          // Commit.
          unset($txn);
        }
      }

      // Make the backup a scannable import tree (manifest LAST) so oci:rollback
      // can read it back.
      if ($snapshotCounts) {
        $this->exporter->finalizeSnapshot($backupRoot, $ctx->runId ?: 'run', $snapshotCounts);
        $report['snapshot'] = $backupRoot;
      }

      // Phase 10 — post-verify. Read back from a reset cache; a presave/postsave
      // hook that rewrote the result, or a write the DB did not actually take,
      // becomes a first-class `applied-diverged` outcome, not a silent log line.
      if ($apply && $appliedUnits) {
        foreach (array_unique(array_column($appliedUnits, 'typeId')) as $tid) {
          $this->entityTypeManager->getStorage($tid)->resetCache();
        }
        foreach ($appliedUnits as $u) {
          $fresh = $this->entityRepository->loadEntityByUuid($u['typeId'], $u['uuid']);
          $reloaded = $fresh instanceof ContentEntityInterface
            ? $this->exporter->livePayloadHash($fresh, $u['langcode'])
            : '';
          if ($u['expected'] !== $u['observed'] || $reloaded !== $u['observed']) {
            $report['diverged'][] = [
              'entity_type' => $u['typeId'],
              'uuid' => $u['uuid'],
              'langcode' => $u['langcode'],
              'expected' => $u['expected'],
              'observed' => $u['observed'],
              'reloaded' => $reloaded,
            ];
            $this->journal->mark($ctx->runId, $u['uuid'], $u['langcode'], 'applied-diverged', $reloaded);
          }
        }
      }
    }
    finally {
      if ($switched) {
        $this->accountSwitcher->switchBack();
      }
    }

    $report['hard_refused'] = $hardRefused;
    return $report;
  }

  /**
   * Resolve the account a write runs as: an explicit --actor (uid or name), else
   * the authenticated operator running the command. NULL means "no actor".
   */
  private function resolveActor(ImportContext $ctx): ?AccountInterface {
    $storage = $this->entityTypeManager->getStorage('user');
    $spec = trim($ctx->actor);
    if ($spec !== '') {
      $account = ctype_digit($spec) ? $storage->load((int) $spec) : NULL;
      if (!$account instanceof AccountInterface) {
        $matches = $storage->loadByProperties(['name' => $spec]);
        $account = $matches ? reset($matches) : NULL;
      }
      if ($account instanceof AccountInterface) {
        return $account;
      }
    }
    if ((int) $this->currentUser->id() > 0) {
      $account = $storage->load((int) $this->currentUser->id());
      return $account instanceof AccountInterface ? $account : NULL;
    }
    // A CLI operator who explicitly bypasses access still needs a real account
    // for text-format/validation context; fall back to the site root user.
    if ($ctx->bypassEntityAccess) {
      $root = $storage->load(1);
      return $root instanceof AccountInterface ? $root : NULL;
    }
    return NULL;
  }

  public function rollback(string $run_id, string $root, bool $apply, string $actor = ''): array {
    // glob() cannot traverse a stream wrapper (public://) — resolve to a real
    // path to locate the snapshot dir.
    $backupBase = $this->exportRoot->realpath(rtrim($root, '/') . '/_backup');
    $dirs = glob($backupBase . '/*--' . $run_id, GLOB_ONLYDIR) ?: [];
    if (!$dirs) {
      throw new OciAbortException('No backup snapshot for run ' . $run_id, 'no_backup');
    }
    // A rollback is import over the pre-image tree, but judged against the target
    // run's applied_hash (§11.1): re-apply where the live entity still matches
    // what that run wrote, refuse `modified-since-run` where a third party edited
    // it afterwards. It writes its own fresh snapshot (undo-of-undo).
    $ctx = new ImportContext(
      rootDir: $dirs[0],
      apply: $apply,
      entityTypes: NULL,
      runId: $run_id . '-rollback',
      actor: $actor,
      rollbackOfRun: $run_id,
    );
    return $this->run($ctx, $apply);
  }

  private function readEntity(string $entityDir): ?array {
    $yml = $entityDir . '/entity.yml';
    if (!file_exists($yml)) {
      return NULL;
    }
    return self::parseYaml($yml);
  }

  public static function parseYaml(string $uri): array {
    return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($uri) ?: '') ?: [];
  }

  private function hookAllows(string $typeId, ?ContentEntityInterface $entity): bool {
    $results = $this->moduleHandler->invokeAll('open_content_improvement_entity_importable', [$typeId, $entity]);
    foreach ($results as $result) {
      if ($result instanceof \Drupal\Core\Access\AccessResultInterface && $result->isAllowed()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function recordUnit(array &$report, string $typeId, string $uuid, string $state, string $reason, array $fields): void {
    $report['units'][] = [
      'entity_type' => $typeId,
      'uuid' => $uuid,
      'state' => $state,
      'reason' => $reason,
      'fields' => $fields,
    ];
  }

}
