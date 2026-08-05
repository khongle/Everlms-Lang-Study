<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\open_content_improvement\ImportContext;
use Drupal\open_content_improvement\Service\ExportRootResolver;
use Drupal\open_content_improvement\Service\ImportWriter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import wizard: (1) scope, (2) dry-run report, (3) confirm + apply.
 * Step 3 is unreachable while refused > 0.
 */
final class ImportForm extends FormBase {

  public function __construct(
    private readonly ImportWriter $importer,
    private readonly ExportRootResolver $exportRoot,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('open_content_improvement.importer'),
      $container->get('open_content_improvement.export_root'),
    );
  }

  public function getFormId(): string {
    return 'open_content_improvement_import';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $step = $form_state->get('step') ?? 1;
    $form_state->set('step', $step);

    if ($step === 1) {
      $form['dir'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Export tree directory'),
        '#default_value' => $this->exportRoot->root(),
      ];
      $form['actions']['#type'] = 'actions';
      $form['actions']['next'] = ['#type' => 'submit', '#value' => $this->t('Dry-run')];
    }
    elseif ($step === 2) {
      $report = $form_state->get('report');
      $form['summary'] = [
        '#markup' => $this->t('Planned: @p · Would change: @c · Refused: @r · Skipped: @s', [
          '@p' => $report['planned'], '@c' => $report['changed'], '@r' => $report['refused'], '@s' => $report['skipped'],
        ]),
      ];
      $form['actions']['#type'] = 'actions';
      if ($report['refused'] > 0) {
        $form['blocked'] = ['#markup' => '<p>' . $this->t('Import is blocked: @r units were refused. Fix or re-export before applying.', ['@r' => $report['refused']]) . '</p>'];
      }
      else {
        $form['actions']['apply'] = ['#type' => 'submit', '#value' => $this->t('Apply (overwrites entities)')];
      }
      $form['actions']['back'] = ['#type' => 'submit', '#value' => $this->t('Back'), '#submit' => ['::back']];
    }
    else {
      $report = $form_state->get('apply_report');
      $form['done'] = ['#markup' => $this->t('Applied. Changed @c entities.', ['@c' => $report['changed'] ?? 0])];
    }
    return $form;
  }

  public function back(array &$form, FormStateInterface $form_state): void {
    $form_state->set('step', 1);
    $form_state->setRebuild();
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $step = $form_state->get('step');
    if ($step === 1) {
      $dir = $form_state->getValue('dir') ?: $this->exportRoot->root();
      $form_state->set('dir', $dir);
      $report = $this->importer->dryRun(new ImportContext($dir, FALSE, NULL, 'ui-' . date('Ymd-His')));
      $form_state->set('report', $report);
      $form_state->set('step', 2);
      $form_state->setRebuild();
    }
    elseif ($step === 2) {
      $dir = $form_state->get('dir');
      $report = $this->importer->apply(new ImportContext($dir, TRUE, NULL, 'ui-' . date('Ymd-His')));
      $form_state->set('apply_report', $report);
      $form_state->set('step', 3);
      $form_state->setRebuild();
      $this->messenger()->addStatus($this->t('Applied: changed @c entities.', ['@c' => $report['changed']]));
    }
  }
}
