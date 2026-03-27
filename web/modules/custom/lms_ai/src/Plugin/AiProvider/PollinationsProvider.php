<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin\AiProvider;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\lms_ai\Plugin\AiProviderInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Pollinations.ai implementation (Free Image Generation).
 *
 * @AiProvider(
 *   id = "pollinations",
 *   label = @Translation("Pollinations AI (FREE - Không cần API Key)"),
 *   description = @Translation("Completely FREE image generation without API Key. Uses Flux under the hood.")
 * )
 */
class PollinationsProvider extends PluginBase implements AiProviderInterface, ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ClientInterface $httpClient,
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
      $container->get('logger.channel.lms_ai')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function generateJson(string $prompt, ?string $schema = NULL, array $options = []): ?array {
    $this->logger->warning('PollinationsProvider does not support JSON generation.');
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function generateText(string $prompt, array $options = []): ?string {
     $this->logger->warning('PollinationsProvider does not support Text generation.');
     return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function generateImage(string $prompt, array $options = []): ?string {
    // Pollinations takes a URL-encoded prompt and returns binary image data.
    $encoded_prompt = urlencode($prompt);
    $url = "https://image.pollinations.ai/prompt/{$encoded_prompt}?width=1024&height=1024&nologo=true&model=flux";

    try {
      $response = $this->httpClient->request('GET', $url, [
        'timeout' => 90, 
      ]);

      if ($response->getStatusCode() === 200) {
        $body = (string) $response->getBody();
        // Convert the raw binary data to Base64 to match our interface standard
        return base64_encode($body);
      }
      
      $this->logger->error("Pollinations.ai returned HTTP status: " . $response->getStatusCode());
      return NULL;
    } catch (\Exception $e) {
      $this->logger->error('Pollinations Image Generation failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }
  }

}
