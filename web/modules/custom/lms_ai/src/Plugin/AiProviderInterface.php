<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for AI provider plugins.
 */
interface AiProviderInterface extends PluginInspectionInterface {

  /**
   * Generates a structured JSON response based on a prompt.
   *
   * @param string $prompt
   *   The system or user prompt.
   * @param string|null $schema
   *   Optional JSON schema to enforce structure.
   * @param array $options
   *   Additional options (e.g., specific model to override default).
   *
   * @return array|null
   *   The parsed associative array, or NULL on failure.
   */
  public function generateJson(string $prompt, ?string $schema = NULL, array $options = []): ?array;

  /**
   * Generates HTML or raw text content based on a prompt.
   *
   * @param string $prompt
   *   The system or user prompt.
   * @param array $options
   *   Additional options.
   *
   * @return string|null
   *   The text/HTML response, or NULL on failure.
   */
  public function generateText(string $prompt, array $options = []): ?string;

  /**
   * Generates a base64 encoded image string based on a prompt.
   *
   * @param string $prompt
   *   The prompt describing the image.
   * @param array $options
   *   Additional options.
   *
   * @return string|null
   *   The base64-encoded image string, or NULL on failure.
   */
  public function generateImage(string $prompt, array $options = []): ?string;

}
