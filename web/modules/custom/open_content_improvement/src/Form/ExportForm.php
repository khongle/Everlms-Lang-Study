<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\open_content_improvement\ExportContext;
use Drupal\open_content_improvement\Service\EntityTypeDiscovery;
use Drupal\open_content_improvement\Service\Exporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Export form: pick entity types and run.
 */
final class ExportForm extends FormBase {

  public function __construct(
    private readonly EntityTypeDiscovery $discovery,
    private readonly Exporter $exporter,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('open_content_improvement.discovery'),
      $container->get('open_content_improvement.exporter'),
    );
  }

  public function getFormId(): string {
    return 'open_content_improvement_export';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $options = [];
    foreach ($this->discovery->exportableTypes() as $id => $d) {
      $options[$id] = $this->t('@label (@id)', ['@label' => $d['label'], '@id' => $id]);
    }
    $form['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types to export (leave empty for all)'),
      '#options' => $options,
    ];
    $form['no_binaries'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Skip binary files (metadata only)'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Export now')];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected = array_values(array_filter($form_state->getValue('entity_types') ?: []));
    $ctx = new ExportContext(
      entityTypes: $selected ?: NULL,
      includeBinaries: !$form_state->getValue('no_binaries'),
      runId: 'ui-' . date('Ymd-His'),
    );
    $result = $this->exporter->export($ctx);
    $this->messenger()->addStatus($this->t('Exported @n entities to @root.', [
      '@n' => array_sum($result['counts']),
      '@root' => $result['root'],
    ]));
  }
}
