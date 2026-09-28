<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\lms_content_tools\Service\ContentToolsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Content Tools dashboard.
 */
final class ContentToolsController extends ControllerBase {

  public function __construct(
    protected ContentToolsManager $contentToolsManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_content_tools.manager'),
    );
  }

  /**
   * Dashboard page listing available content tools.
   */
  public function dashboard(): array {
    $build = [
      '#theme_wrappers' => ['lms_workspace_shell'],
      '#workspace_title' => $this->t('Content Tools'),
      '#workspace_subtitle' => $this->t('Manage content cleanup and training program input.'),
      '#workspace_active' => 'content_tools',
      '#attached' => ['library' => ['lms_theme/workspace']],
    ];

    // Count total entities.
    $totalEntities = 0;
    foreach (array_keys($this->contentToolsManager->getEntityGroups()) as $groupKey) {
      $totalEntities += $this->contentToolsManager->countGroup($groupKey);
    }

    // Check seed input status.
    $seedData = $this->contentToolsManager->loadSeedInput();
    $seedStatus = $seedData ? $this->t('Configured (@courses courses)', [
      '@courses' => count($seedData['courses'] ?? []),
    ]) : $this->t('Not configured');

    // Check generated content status.
    $manifest = $this->contentToolsManager->loadManifest();
    $generatedStatus = $manifest ? $this->t('Generated at @date', [
      '@date' => $manifest['generated_at'] ?? 'unknown',
    ]) : $this->t('No generated content');

    $build['tools'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-admin-tools-grid']],
    ];

    // Clean Content card.
    $build['tools']['clean'] = [
      '#type' => 'inline_template',
      '#template' => '
        <div class="lms-admin-card">
          <h3>🗑 {{ title }}</h3>
          <p>{{ description }}</p>
          <p><strong>{{ status_label }}:</strong> {{ entity_count }} entities</p>
          <a href="{{ url }}" class="btn-lms-secondary lms-button--danger">{{ action }}</a>
        </div>',
      '#context' => [
        'title' => $this->t('Clean Content'),
        'description' => $this->t('Delete content from the system with 2-step confirmation.'),
        'status_label' => $this->t('Current content'),
        'entity_count' => number_format($totalEntities),
        'url' => Url::fromRoute('lms_content_tools.clean')->toString(),
        'action' => $this->t('Open Clean Form'),
      ],
    ];

    // Seed Input card.
    $build['tools']['seed'] = [
      '#type' => 'inline_template',
      '#template' => '
        <div class="lms-admin-card">
          <h3>📝 {{ title }}</h3>
          <p>{{ description }}</p>
          <p><strong>{{ status_label }}:</strong> {{ seed_status }}</p>
          <a href="{{ url }}" class="btn-lms-primary">{{ action }}</a>
        </div>',
      '#context' => [
        'title' => $this->t('Seed Input'),
        'description' => $this->t('Enter training program information for AI content generation.'),
        'status_label' => $this->t('Status'),
        'seed_status' => $seedStatus,
        'url' => Url::fromRoute('lms_content_tools.seed_input')->toString(),
        'action' => $this->t('Open Seed Input Form'),
      ],
    ];

    // AI Generator card.
    $build['tools']['generator'] = [
      '#type' => 'inline_template',
      '#template' => '
        <div class="lms-admin-card">
          <h3>🤖 {{ title }}</h3>
          <p>{{ description }}</p>
          <p><strong>{{ status_label }}:</strong> {{ generated_status }}</p>
          <p><small>{{ instruction }}</small></p>
        </div>',
      '#context' => [
        'title' => $this->t('AI Content Generator'),
        'description' => $this->t('Use Claude Code or Gemini to generate content from seed input.'),
        'status_label' => $this->t('Generated content'),
        'generated_status' => $generatedStatus,
        'instruction' => $this->t('Open Claude Code (VS Code) or Gemini (Antigravity) and ask: "Read seed input and generate all content for the training system."'),
      ],
    ];

    return $build;
  }

}
