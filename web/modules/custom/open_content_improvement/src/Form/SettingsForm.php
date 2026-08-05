<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\open_content_improvement\Service\ExportRootResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for Open Content Improvement.
 */
final class SettingsForm extends ConfigFormBase {

  public function __construct(
    ConfigFactoryInterface $config_factory,
    private readonly ExportRootResolver $exportRoot,
  ) {
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('open_content_improvement.export_root'),
    );
  }

  public function getFormId(): string {
    return 'open_content_improvement_settings';
  }

  protected function getEditableConfigNames(): array {
    return ['open_content_improvement.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('open_content_improvement.settings');
    $verdict = $this->exportRoot->securityVerdict();

    $form['security'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Export root security'),
    ];
    $form['security']['verdict'] = [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Root: @root', ['@root' => $verdict['root']]),
        $this->t('Scheme: @scheme', ['@scheme' => $verdict['scheme']]),
        $this->t('.htaccess deny rule: @h', ['@h' => $verdict['htaccess'] ? $this->t('present') : $this->t('MISSING')]),
        $this->t('Private filesystem available: @p', ['@p' => $verdict['private_available'] ? $this->t('yes') : $this->t('no')]),
        $this->t('Recommendation: @r', ['@r' => $verdict['recommendation']]),
      ],
    ];

    $form['export_root'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Export root URI'),
      '#default_value' => $config->get('export_root'),
      '#description' => $this->t('Stream wrapper URI. Strongly prefer private:// on any site that has a private filesystem.'),
    ];
    $form['pii_guard'] = [
      '#type' => 'select',
      '#title' => $this->t('PII guard'),
      '#options' => ['strict' => $this->t('strict'), 'relaxed' => $this->t('relaxed')],
      '#default_value' => $config->get('pii_guard'),
    ];
    $form['include_binaries'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Copy binary files into the tree'),
      '#default_value' => (bool) $config->get('include_binaries'),
    ];
    $form['environment_label'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#options' => ['dev' => $this->t('dev'), 'staging' => $this->t('staging'), 'prod' => $this->t('prod')],
      '#default_value' => $config->get('environment_label'),
    ];
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('open_content_improvement.settings')
      ->set('export_root', $form_state->getValue('export_root'))
      ->set('pii_guard', $form_state->getValue('pii_guard'))
      ->set('include_binaries', (bool) $form_state->getValue('include_binaries'))
      ->set('environment_label', $form_state->getValue('environment_label'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
