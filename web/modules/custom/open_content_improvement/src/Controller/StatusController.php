<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\open_content_improvement\Service\AuditLogger;
use Drupal\open_content_improvement\Service\ExportRootResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Status + run report pages.
 */
final class StatusController extends ControllerBase {

  public function __construct(
    private readonly ExportRootResolver $exportRoot,
    private readonly AuditLogger $audit,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('open_content_improvement.export_root'),
      $container->get('open_content_improvement.audit'),
    );
  }

  public function overview(): array {
    $verdict = $this->exportRoot->securityVerdict();
    return [
      '#cache' => ['max-age' => 0],
      'hardening' => [
        '#type' => 'details',
        '#title' => $this->t('Export root hardening'),
        '#open' => TRUE,
        'list' => [
          '#theme' => 'item_list',
          '#items' => [
            $this->t('Root: @root', ['@root' => $verdict['root']]),
            $this->t('Scheme: @scheme', ['@scheme' => $verdict['scheme']]),
            $this->t('.htaccess: @h', ['@h' => $verdict['htaccess'] ? $this->t('present') : $this->t('MISSING')]),
            $this->t('Recommendation: @r', ['@r' => $verdict['recommendation']]),
          ],
        ],
      ],
    ];
  }

  public function report(string $run_id): array {
    $rows = [];
    foreach ($this->audit->forRun($run_id) as $r) {
      $rows[] = [$r->entity_type, $r->entity_uuid, $r->langcode, $r->field_name, $r->action, $r->actor];
    }
    return [
      '#cache' => ['max-age' => 0],
      'table' => [
        '#type' => 'table',
        '#header' => [$this->t('Type'), $this->t('UUID'), $this->t('Lang'), $this->t('Field'), $this->t('Action'), $this->t('Actor')],
        '#rows' => $rows,
        '#empty' => $this->t('No audit rows for this run.'),
      ],
    ];
  }
}
