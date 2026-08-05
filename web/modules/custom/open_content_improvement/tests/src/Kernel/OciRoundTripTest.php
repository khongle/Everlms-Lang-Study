<?php

declare(strict_types=1);

namespace Drupal\Tests\open_content_improvement\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\oci_test_entity\Entity\OciTestEntity;
use Drupal\open_content_improvement\ExportContext;
use Drupal\open_content_improvement\ImportContext;

/**
 * Export → improve → import round-trip on a bare, core-only entity type.
 *
 * Uses the oci_test_entity content type (no node/field/taxonomy) so the whole
 * pipeline — discovery, classification, sidecar hoisting, hashing, dry-run and
 * apply — is exercised entirely through metadata, exactly as it would be on any
 * install site.
 *
 * @group open_content_improvement
 */
final class OciRoundTripTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'filter',
    'text',
    'open_content_improvement',
    'oci_test_entity',
  ];

  /**
   * The export root for this test run.
   */
  private string $root;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('oci_test_entity');
    $this->installSchema('open_content_improvement', ['oci_journal', 'oci_audit']);
    $this->installConfig(['filter', 'open_content_improvement']);

    // uid 1 is the superuser and bypasses entity/field/format access, so the
    // filter_format AllowedValues constraint accepts full_html on save().
    $root_user = $this->container->get('entity_type.manager')->getStorage('user')->create([
      'uid' => 1,
      'name' => 'root',
    ]);
    $root_user->save();
    $this->container->get('current_user')->setAccount($root_user);

    // Export somewhere isolated and writable in the test environment.
    $this->root = 'public://oci-test-export';

    // Make the test entity type importable (opt-in is empty by default).
    $this->config('open_content_improvement.settings')
      ->set('importable_entity_types', ['oci_test_entity'])
      ->save();

    // Long enough to be hoisted into a sidecar file.
    $body = '<p>' . str_repeat('Nội dung dài để đẩy ra sidecar. ', 20) . '</p>';
    $entity = OciTestEntity::create([
      'name' => 'Round trip subject',
      'body' => ['value' => $body, 'format' => 'plain_text'],
      'weight' => 5,
    ]);
    $entity->save();
  }

  /**
   * Export the test corpus into $this->root.
   */
  private function export(): array {
    return $this->container->get('open_content_improvement.exporter')->export(
      new ExportContext(entityTypes: ['oci_test_entity'], targetDir: $this->root . '/run', runId: 'test')
    );
  }

  /**
   * P-C1 — determinism: two exports of the same state are byte-identical.
   */
  public function testExportIsDeterministic(): void {
    $a = 'public://oci-a';
    $b = 'public://oci-b';
    $exporter = $this->container->get('open_content_improvement.exporter');
    $exporter->export(new ExportContext(entityTypes: ['oci_test_entity'], targetDir: $a, runId: 'test'));
    $exporter->export(new ExportContext(entityTypes: ['oci_test_entity'], targetDir: $b, runId: 'test'));

    $fs = $this->container->get('file_system');
    $entityYmlA = $this->firstEntityYml($fs->realpath($a));
    $entityYmlB = $this->firstEntityYml($fs->realpath($b));
    $this->assertNotNull($entityYmlA);
    $this->assertSame(
      file_get_contents($entityYmlA),
      file_get_contents($entityYmlB),
      'Two exports of identical state must produce byte-identical entity.yml.'
    );
  }

  /**
   * P-C2 — a no-op import (nothing edited) changes zero entities.
   */
  public function testNoOpImportChangesNothing(): void {
    $this->export();
    $report = $this->container->get('open_content_improvement.importer')
      ->dryRun(new ImportContext(rootDir: $this->root . '/run', entityTypes: ['oci_test_entity'], runId: 'noop'));
    $this->assertSame(0, $report['changed'], 'An unedited tree must report zero changes.');
    $this->assertSame(0, $report['refused'], 'An unedited tree must not refuse anything.');
  }

  /**
   * P-C4 — one edited value ⇒ exactly one changed entity and one audit row.
   */
  public function testSingleEditProducesSingleChangeAndAuditRow(): void {
    $this->export();
    $fs = $this->container->get('file_system');
    $entityYml = $this->firstEntityYml($fs->realpath($this->root . '/run'));
    $this->assertNotNull($entityYml);
    $dir = dirname($entityYml);

    // Edit the body sidecar (the hoisted content), leaving weight/name alone.
    $sidecars = glob($dir . '/text/*.html') ?: [];
    $this->assertNotEmpty($sidecars, 'Body should have been hoisted to a sidecar.');
    file_put_contents($sidecars[0], file_get_contents($sidecars[0]) . '<p>edited</p>');

    $importer = $this->container->get('open_content_improvement.importer');

    // Dry-run detects exactly one would-change.
    $dry = $importer->dryRun(new ImportContext(rootDir: $this->root . '/run', entityTypes: ['oci_test_entity'], runId: 'edit-dry'));
    $this->assertSame(1, $dry['changed']);
    $this->assertSame(0, $dry['refused']);

    // Apply writes exactly one entity.
    $applied = $importer->apply(new ImportContext(rootDir: $this->root . '/run', apply: TRUE, entityTypes: ['oci_test_entity'], runId: 'edit-apply', reason: 'test'));
    $this->assertSame(1, $applied['changed']);
    $this->assertSame(0, $applied['refused']);

    // Exactly one audit row for the body field of this run.
    $count = (int) $this->container->get('database')->select('oci_audit', 'a')
      ->condition('run_id', 'edit-apply')
      ->countQuery()->execute()->fetchField();
    $this->assertSame(1, $count, 'Exactly one audit row expected for a single-field edit.');
  }

  /**
   * Locate the first entity.yml under a real export root path.
   */
  private function firstEntityYml(string $realRoot): ?string {
    foreach (glob($realRoot . '/*', GLOB_ONLYDIR) ?: [] as $typeDir) {
      if (str_starts_with(basename($typeDir), '_')) {
        continue;
      }
      foreach (glob($typeDir . '/*', GLOB_ONLYDIR) ?: [] as $entityDir) {
        if (file_exists($entityDir . '/entity.yml')) {
          return $entityDir . '/entity.yml';
        }
      }
    }
    return NULL;
  }

}
