<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin\AiProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\lms_ai\Plugin\AiProviderInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an OpenAI implementation of the AI Provider.
 *
 * @AiProvider(
 *   id = "openai",
 *   label = @Translation("OpenAI (ChatGPT)"),
 *   description = @Translation("Integration with OpenAI Chat Completions API.")
 * )
 */
use Drupal\Core\Plugin\PluginBase;

class OpenAiProvider extends PluginBase implements AiProviderInterface, ContainerFactoryPluginInterface {

  private const API_URL = 'https://api.openai.com/v1/chat/completions';

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ClientInterface $httpClient,
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
      $container->get('http_client'),
      $container->get('config.factory'),
      $container->get('logger.channel.lms_ai')
    );
  }

  private function getApiKey(): ?string {
    return $this->configFactory->get('lms_ai.settings')->get('openai.api_key');
  }

  private function getModel(array $options): string {
    return $options['model'] ?? $this->configFactory->get('lms_ai.settings')->get('openai.model') ?? 'gpt-4o';
  }

  /**
   * {@inheritdoc}
   */
  public function generateJson(string $prompt, ?string $schema = NULL, array $options = []): ?array {
    $payload = $this->buildPayload($prompt, $options);

    // Enforce JSON structured output mode.
    $payload['response_format'] = ['type' => 'json_object'];
    
    // Auto-inject schema expectation into prompt if not already present.
    if ($schema) {
      $schemaPrompt = "You MUST return a raw JSON object STRICTLY conforming to this schema:\n$schema";
      $payload['messages'][0]['content'] .= "\n\n" . $schemaPrompt;
    }

    $response = $this->request($payload);
    
    if (!$response) {
      return NULL;
    }

    try {
      return json_decode($response, TRUE, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      $this->logger->error('Failed to parse OpenAI JSON response: @error. Raw: @raw', [
        '@error' => $e->getMessage(),
        '@raw' => $response,
      ]);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateText(string $prompt, array $options = []): ?string {
    $payload = $this->buildPayload($prompt, $options);
    return $this->request($payload);
  }

  private function buildPayload(string $prompt, array $options): array {
    $messages = [
      [
        'role' => 'system',
        'content' => $options['system_prompt'] ?? 'You are a helpful AI assistant powering an elite LMS platform. Always use rich markdown or HTML styling if applicable.',
      ],
      [
        'role' => 'user',
        'content' => $prompt,
      ]
    ];

    return [
      'model' => $this->getModel($options),
      'messages' => $messages,
      'temperature' => $options['temperature'] ?? 0.7,
    ];
  }

  private function request(array $payload): ?string {
    $apiKey = $this->getApiKey();
    if (!$apiKey) {
      $this->logger->error('OpenAI API Key is missing.');
      return NULL;
    }

    try {
      $response = $this->httpClient->request('POST', self::API_URL, [
        'headers' => [
          'Authorization' => "Bearer {$apiKey}",
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 60, // AI requests can take time.
      ]);

      $body = json_decode((string) $response->getBody(), TRUE);
      return $body['choices'][0]['message']['content'] ?? NULL;

    } catch (\Exception $e) {
      $this->logger->error('OpenAI API Request failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateImage(string $prompt, array $options = []): ?string {
    $apiKey = $this->getApiKey();
    if (!$apiKey) {
      $this->logger->error('OpenAI API Key is missing for Image Generation.');
      return NULL;
    }

    $url = 'https://api.openai.com/v1/images/generations';
    $payload = [
      'model' => 'dall-e-3',
      'prompt' => $prompt,
      'n' => 1,
      'size' => '1024x1024',
      'response_format' => 'b64_json',
    ];

    try {
      $response = $this->httpClient->request('POST', $url, [
        'headers' => [
          'Authorization' => "Bearer {$apiKey}",
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 90, // Image generation takes time.
      ]);

      $body = json_decode((string) $response->getBody(), TRUE);
      return $body['data'][0]['b64_json'] ?? NULL;

    } catch (\Exception $e) {
      $this->logger->error('OpenAI Image Generation failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }
  }

}
