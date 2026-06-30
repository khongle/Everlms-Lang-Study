<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\lms_ai\Plugin\AiProviderManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates lesson content asynchronously.
 *
 * @QueueWorker(
 *   id = "lms_ai_content_generator",
 *   title = @Translation("AI Lesson Content Generator"),
 *   cron = {"time" = 60}
 * )
 */
class CourseContentGenerator extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly AiProviderManager $aiProviderManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.lms_ai_provider'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('logger.channel.lms_ai')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    if (!isset($data['lesson_id'], $data['prompt'])) {
      return;
    }

    $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');
    $lesson = $lessonStorage->load($data['lesson_id']);

    if (!$lesson) {
      return;
    }

    try {
      $provider_id = $data['provider_id']
        ?? $this->configFactory->get('lms_ai.settings')->get('default_provider')
        ?? 'openai';

      if (!isset($this->aiProviderManager->getDefinitions()[$provider_id])) {
        $provider_id = 'openai';
      }

      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $ai */
      $ai = $this->aiProviderManager->createInstance($provider_id);
      
      $fullPrompt = sprintf(
        "Write a complete, educational, and engaging textbook-style lesson about the following instruction:\n\n%s\n\nEnsure the tone is '%s'. Format the output using accessible HTML (<h1>, <h2>, <p>, <ul>, <strong>). Do NOT output Markdown code blocks.",
        $data['prompt'],
        $data['tone'] ?? 'professional'
      );

      $htmlContent = $ai->generateText($fullPrompt);

      if ($htmlContent) {
        $lesson->set('field_content', [
          'value' => $htmlContent,
          'format' => 'full_html',
        ]);
        // Also auto-publish the lesson once generation is done.
        $lesson->set('status', 1);
        $lesson->save();
        $this->logger->info('Successfully generated AI content for Lesson @id.', ['@id' => $lesson->id()]);
      } else {
        throw new \Exception('AI returned empty response for lesson ' . $lesson->id());
      }
    } catch (\Exception $e) {
      $this->logger->error('Queue AI Error: ' . $e->getMessage());
      // Throw exception to indicate failure, so Drupal Queue API leaves item in queue to retry later.
      throw $e;
    }
  }

}
