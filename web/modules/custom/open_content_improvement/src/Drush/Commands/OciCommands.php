<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Drush\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\open_content_improvement\ExportContext;
use Drupal\open_content_improvement\ImportContext;
use Drupal\open_content_improvement\Service\AuditLogger;
use Drupal\open_content_improvement\Service\BatchPlanner;
use Drupal\open_content_improvement\Service\EntityTypeDiscovery;
use Drupal\open_content_improvement\Service\Exporter;
use Drupal\open_content_improvement\Service\ExportRootResolver;
use Drupal\open_content_improvement\Service\ImportabilitySuggester;
use Drupal\open_content_improvement\Service\ImportWriter;
use Drupal\open_content_improvement\Service\RunJournal;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for Open Content Improvement (namespace oci:).
 */
final class OciCommands extends DrushCommands {

  public function __construct(
    private readonly Exporter $exporter,
    private readonly ImportWriter $importer,
    private readonly EntityTypeDiscovery $discovery,
    private readonly ImportabilitySuggester $suggester,
    private readonly BatchPlanner $batchPlanner,
    private readonly RunJournal $journal,
    private readonly AuditLogger $audit,
    private readonly ExportRootResolver $exportRoot,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly AccountProxyInterface $currentUser,
  ) {
    parent::__construct();
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('open_content_improvement.exporter'),
      $container->get('open_content_improvement.importer'),
      $container->get('open_content_improvement.discovery'),
      $container->get('open_content_improvement.suggester'),
      $container->get('open_content_improvement.batch_planner'),
      $container->get('open_content_improvement.journal'),
      $container->get('open_content_improvement.audit'),
      $container->get('open_content_improvement.export_root'),
      $container->get('config.factory'),
      $container->get('current_user'),
    );
  }

  #[CLI\Command(name: 'oci:list-entities', aliases: ['oci-le'])]
  #[CLI\Option(name: 'format', description: 'table | json')]
  public function listEntities(array $options = ['format' => 'table']): int {
    $rows = [];
    foreach ($this->discovery->discover() as $id => $d) {
      $rows[] = [
        'entity_type' => $id,
        'exportable' => $d['skip_reason'] === NULL ? 'yes' : 'no',
        'importable' => ($d['skip_reason'] === NULL && !$d['export_only']) ? 'candidate' : 'no',
        'skip_reason' => $d['skip_reason'] ?? '',
      ];
    }
    if (($options['format'] ?? 'table') === 'json') {
      $this->io()->writeln(json_encode($rows, JSON_PRETTY_PRINT));
    }
    else {
      $this->io()->table(['Entity type', 'Exportable', 'Importable', 'Skip reason'], array_map('array_values', $rows));
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:suggest', aliases: ['oci-sg'])]
  #[CLI\Option(name: 'write', description: 'Write recommended types into importable_entity_types (interactive).')]
  public function suggest(array $options = ['write' => FALSE]): int {
    $suggestions = $this->suggester->suggest();
    $rows = [];
    $recommended = [];
    foreach ($suggestions as $id => $s) {
      if ($s['recommend']) {
        $recommended[] = $id;
      }
      $rows[] = [$id, $s['recommend'] ? 'yes' : 'no', $s['editable_fields'], json_encode($s['signals'])];
    }
    $this->io()->table(['Entity type', 'Recommend', 'Editable fields', 'Signals'], $rows);
    if (!empty($options['write'])) {
      if (!$recommended) {
        $this->io()->warning('No recommended entity types to write.');
        return self::EXIT_SUCCESS;
      }
      $this->io()->listing($recommended);
      if (!$this->io()->confirm('Add these entity types to importable_entity_types?', FALSE)) {
        return self::EXIT_SUCCESS;
      }
      $config = $this->configFactory->getEditable('open_content_improvement.settings');
      $existing = $config->get('importable_entity_types') ?: [];
      $config->set('importable_entity_types', array_values(array_unique([...$existing, ...$recommended])))->save();
      $this->io()->success(sprintf('%d entity types are now importable.', count($recommended)));
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:export', aliases: ['oci-ex'])]
  #[CLI\Option(name: 'entity-types', description: 'Comma-separated entity type IDs; all discovered types if omitted.')]
  #[CLI\Option(name: 'bundles', description: 'Comma-separated bundles.')]
  #[CLI\Option(name: 'langcodes', description: 'Comma-separated langcodes; all translations if omitted.')]
  #[CLI\Option(name: 'no-binaries', description: 'Write file metadata but copy no binaries.')]
  #[CLI\Option(name: 'dir', description: 'Override the configured export root for this run.')]
  #[CLI\Usage(name: 'oci:export --entity-types=node,taxonomy_term', description: 'Export two entity types.')]
  public function export(array $options = ['entity-types' => NULL, 'bundles' => NULL, 'langcodes' => NULL, 'no-binaries' => FALSE, 'dir' => NULL]): int {
    $ctx = new ExportContext(
      entityTypes: $this->csv($options['entity-types'] ?? NULL),
      bundles: $this->csv($options['bundles'] ?? NULL),
      langcodes: $this->csv($options['langcodes'] ?? NULL),
      includeBinaries: empty($options['no-binaries']),
      targetDir: $options['dir'] ?? NULL,
      runId: $this->runId(),
    );
    $result = $this->exporter->export($ctx);
    $total = array_sum($result['counts']);
    $this->io()->success(sprintf('Exported %d entities across %d types to %s', $total, count($result['counts']), $result['root']));
    $this->io()->table(['Entity type', 'Count'], array_map(static fn($t, $c) => [$t, $c], array_keys($result['counts']), array_values($result['counts'])));
    if ($result['skipped']) {
      $this->io()->note(sprintf('%d entity types skipped (see _skipped.yml).', count($result['skipped'])));
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:plan-batches', aliases: ['oci-pb'])]
  #[CLI\Option(name: 'max-chars', description: 'Character budget per batch.')]
  #[CLI\Option(name: 'dir', description: 'Export root to plan.')]
  public function planBatches(array $options = ['max-chars' => 400000, 'dir' => NULL]): int {
    $root = $options['dir'] ?? $this->exportRoot->root();
    $plan = $this->batchPlanner->plan($root, (int) $options['max-chars']);
    $this->io()->writeln(sprintf('%d batches planned.', count($plan['batches'])));
    if ($plan['oversize']) {
      $this->io()->error('Oversize entity directories (exceed the per-batch budget):');
      $this->io()->listing($plan['oversize']);
      return self::EXIT_FAILURE;
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:pack', aliases: ['oci-pk'])]
  #[CLI\Option(name: 'batch', description: 'Batch number.')]
  #[CLI\Option(name: 'out', description: 'Output directory.')]
  public function pack(array $options = ['batch' => 0, 'out' => NULL]): int {
    $this->io()->note('oci:pack materializes a self-contained batch copy. See _batches/.');
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:unpack', aliases: ['oci-up'])]
  #[CLI\Option(name: 'batch', description: 'Batch number.')]
  #[CLI\Option(name: 'from', description: 'Directory of the edited batch.')]
  public function unpack(array $options = ['batch' => 0, 'from' => NULL]): int {
    $this->io()->note('oci:unpack copies edited files back, refusing any path outside FILES.txt.');
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:import', aliases: ['oci-im'])]
  #[CLI\Option(name: 'dir', description: 'Export tree to import.')]
  #[CLI\Option(name: 'entity-types', description: 'Restrict to these entity type IDs.')]
  #[CLI\Option(name: 'apply', description: 'Actually write. Without this, dry-run.')]
  #[CLI\Option(name: 'reason', description: 'Reason recorded in the audit log.')]
  #[CLI\Option(name: 'actor', description: 'Actor recorded in the audit log.')]
  #[CLI\Option(name: 'bypass-entity-access', description: 'Write fields the actor could not otherwise edit.')]
  #[CLI\Usage(name: 'oci:import --entity-types=node', description: 'Dry-run import of node changes.')]
  public function import(array $options = ['dir' => NULL, 'entity-types' => NULL, 'apply' => FALSE, 'reason' => '', 'actor' => '', 'bypass-entity-access' => FALSE]): int {
    $apply = !empty($options['apply']);
    $ctx = new ImportContext(
      rootDir: $options['dir'] ?? $this->exportRoot->root(),
      apply: $apply,
      entityTypes: $this->csv($options['entity-types'] ?? NULL),
      runId: $this->runId(),
      reason: (string) ($options['reason'] ?? ''),
      actor: (string) ($options['actor'] ?? ''),
      bypassEntityAccess: !empty($options['bypass-entity-access']),
    );
    if ($apply) {
      // Destructive confirmation: -y satisfies confirm() but NOT the typed ask.
      if (!$this->io()->confirm('This overwrites live entities in place. Continue?', FALSE)) {
        return self::EXIT_SUCCESS;
      }
      $typed = $this->io()->ask('Type IMPORT to proceed', '');
      if ($typed !== 'IMPORT') {
        $this->io()->warning('Aborted — confirmation not typed.');
        return self::EXIT_SUCCESS;
      }
    }
    $report = $apply ? $this->importer->apply($ctx) : $this->importer->dryRun($ctx);
    $this->io()->writeln(sprintf(
      '%s: planned=%d changed=%d refused=%d skipped=%d',
      $apply ? 'APPLIED' : 'DRY-RUN',
      $report['planned'], $report['changed'], $report['refused'], $report['skipped'],
    ));
    $refusals = array_filter($report['units'], static fn($u) => $u['state'] === 'refused');
    if ($refusals) {
      $this->io()->section('Refused units');
      $this->io()->table(['Entity type', 'UUID', 'Reason'], array_map(static fn($u) => [$u['entity_type'], $u['uuid'], $u['reason']], $refusals));
    }
    if (!$apply && $report['changed'] > 0) {
      $this->io()->note('Dry-run only. Re-run with --apply to write.');
    }
    return ($report['refused'] > 0 && !$apply) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:rollback', aliases: ['oci-rb'])]
  #[CLI\Option(name: 'run', description: 'Run ID to roll back.')]
  #[CLI\Option(name: 'apply', description: 'Actually roll back.')]
  #[CLI\Option(name: 'dir', description: 'Export root holding _backup/.')]
  #[CLI\Option(name: 'actor', description: 'Actor to write as (uid or username); defaults to the current user.')]
  public function rollback(array $options = ['run' => '', 'apply' => FALSE, 'dir' => NULL, 'actor' => NULL]): int {
    $runId = (string) ($options['run'] ?? '');
    if ($runId === '') {
      $this->io()->error('--run is required.');
      return self::EXIT_FAILURE;
    }
    $apply = !empty($options['apply']);
    $actor = (string) ($options['actor'] ?? '');
    if ($actor === '' && (int) $this->currentUser->id() > 0) {
      $actor = (string) $this->currentUser->id();
    }
    if ($apply && !$this->io()->confirm('This rewrites live entities back to a previous snapshot. Continue?', FALSE)) {
      return self::EXIT_SUCCESS;
    }
    $report = $this->importer->rollback($runId, $options['dir'] ?? $this->exportRoot->root(), $apply, $actor);
    $this->io()->writeln(sprintf('Rollback %s: changed=%d refused=%d', $apply ? 'APPLIED' : 'DRY-RUN', $report['changed'], $report['refused'] ?? 0));
    $refusals = array_filter($report['units'], static fn($u) => $u['state'] === 'refused');
    if ($refusals) {
      $this->io()->table(['Entity type', 'UUID', 'Reason'], array_map(static fn($u) => [$u['entity_type'], $u['uuid'], $u['reason']], $refusals));
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:status', aliases: ['oci-st'])]
  #[CLI\Option(name: 'run', description: 'Show a single run.')]
  public function status(array $options = ['run' => NULL]): int {
    $verdict = $this->exportRoot->securityVerdict();
    $this->io()->definitionList(
      ['Export root' => $verdict['root']],
      ['Scheme' => $verdict['scheme']],
      ['.htaccess' => $verdict['htaccess'] ? 'present' : 'MISSING'],
      ['Private available' => $verdict['private_available'] ? 'yes' : 'no'],
      ['Recommendation' => $verdict['recommendation']],
    );
    if (!empty($options['run'])) {
      $states = array_count_values($this->journal->states((string) $options['run']));
      $this->io()->writeln('Journal: ' . json_encode($states));
    }
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'oci:audit', aliases: ['oci-au'])]
  #[CLI\Option(name: 'run', description: 'Run ID.')]
  #[CLI\Option(name: 'format', description: 'table | json')]
  public function auditCmd(array $options = ['run' => '', 'format' => 'table']): int {
    $rows = $this->audit->forRun((string) ($options['run'] ?? ''));
    if (($options['format'] ?? 'table') === 'json') {
      $this->io()->writeln(json_encode($rows, JSON_PRETTY_PRINT));
    }
    else {
      $this->io()->table(
        ['Entity type', 'UUID', 'Lang', 'Field', 'Action', 'Actor'],
        array_map(static fn($r) => [$r->entity_type, $r->entity_uuid, $r->langcode, $r->field_name, $r->action, $r->actor], $rows),
      );
    }
    return self::EXIT_SUCCESS;
  }

  private function csv(?string $value): ?array {
    if ($value === NULL || $value === '') {
      return NULL;
    }
    return array_values(array_filter(array_map('trim', explode(',', $value))));
  }

  private function runId(): string {
    return 'oci-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
  }
}
