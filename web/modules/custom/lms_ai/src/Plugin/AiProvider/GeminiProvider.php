<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin\AiProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\lms_ai\Plugin\AiProviderInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Google Gemini implementation of the AI Provider.
 *
 * @AiProvider(
 *   id = "gemini",
 *   label = @Translation("Google Gemini"),
 *   description = @Translation("Integration with Google Generative AI API.")
 * )
 */
class GeminiProvider extends PluginBase implements AiProviderInterface, ContainerFactoryPluginInterface {

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
    return $this->configFactory->get('lms_ai.settings')->get('gemini.api_key');
  }

  private function getModel(array $options): string {
    return $options['model'] ?? $this->configFactory->get('lms_ai.settings')->get('gemini.model') ?? 'gemini-2.5-flash';
  }

  /**
   * {@inheritdoc}
   */
  public function generateJson(string $prompt, ?string $schema = NULL, array $options = []): ?array {
    $payload = $this->buildPayload($prompt, $options);

    // Enforce JSON structured output mode.
    $payload['generationConfig']['responseMimeType'] = 'application/json';
    
    if ($schema) {
      $schemaPrompt = "You MUST return a raw JSON object STRICTLY conforming to this schema:\n$schema";
      $payload['contents'][0]['parts'][0]['text'] .= "\n\n" . $schemaPrompt;
    }

    $response = $this->request($payload, $options);
    
    if (!$response) {
      return NULL;
    }
    
    // Clean markdown blocks if Gemini stubbornly includes them
    $response = trim($response);
    if (str_starts_with($response, '```json')) {
      $response = preg_replace('/^```json|```$/m', '', $response);
    }
    $response = trim($response);

    try {
      return json_decode($response, TRUE, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      $this->logger->error('Failed to parse Gemini JSON response: @error. Raw: @raw', [
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
    return $this->request($payload, $options);
  }

  private function buildPayload(string $prompt, array $options): array {
    $systemPrompt = $options['system_prompt'] ?? 'You are a helpful AI assistant powering an elite LMS platform. Always use rich markdown or HTML styling if applicable.';
    
    return [
      'systemInstruction' => [
        'parts' => [
          ['text' => $systemPrompt]
        ]
      ],
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt]
          ]
        ]
      ],
      'generationConfig' => [
        'temperature' => $options['temperature'] ?? 0.7,
      ]
    ];
  }

  private function request(array $payload, array $options): ?string {
    $apiKey = $this->getApiKey();
    if (!$apiKey) {
      $this->logger->error('Gemini API Key is missing.');
      return NULL;
    }

    $model = $this->getModel($options);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    try {
      $response = $this->httpClient->request('POST', $url, [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 60,
      ]);

      $body = json_decode((string) $response->getBody(), TRUE);
      return $body['candidates'][0]['content']['parts'][0]['text'] ?? NULL;

    } catch (\Exception $e) {
      $this->logger->error('Gemini API Request failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateImage(string $prompt, array $options = []): ?string {
    $apiKey = $this->getApiKey();
    if (!$apiKey) {
      $this->logger->error('Gemini API Key is missing for Image Generation.');
      return NULL;
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict?key={$apiKey}";
    $payload = [
      'instances' => [
        ['prompt' => $prompt]
      ],
      'parameters' => [
        'sampleCount' => 1,
        'aspectRatio' => '1:1'
      ],
    ];

    try {
      $response = $this->httpClient->request('POST', $url, [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => $payload,
        'timeout' => 90,
      ]);

      $body = json_decode((string) $response->getBody(), TRUE);
      return $body['predictions'][0]['bytesBase64Encoded'] ?? NULL;

    } catch (\Exception $e) {
      $this->logger->error('Gemini Image Generation failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }
  }

}
