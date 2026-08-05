<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\open_content_improvement\Service\ImportabilitySuggester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The only UI path to make an entity type importable.
 */
final class ImportabilityForm extends FormBase {

  public function __construct(
    private readonly ImportabilitySuggester $suggester,
    private readonly ConfigFactoryInterface $configFactorySvc,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('open_content_improvement.suggester'),
      $container->get('config.factory'),
    );
  }

  public function getFormId(): string {
    return 'open_content_improvement_importability';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $current = $this->configFactorySvc->get('open_content_improvement.settings')->get('importable_entity_types') ?: [];
    $options = [];
    $defaults = [];
    foreach ($this->suggester->suggest() as $id => $s) {
      $options[$id] = $this->t('@label (@id) — @n editable fields@rec', [
        '@label' => $s['label'],
        '@id' => $id,
        '@n' => $s['editable_fields'],
        '@rec' => $s['recommend'] ? $this->t(' — recommended') : '',
      ]);
      if (in_array($id, $current, TRUE)) {
        $defaults[$id] = $id;
      }
    }
    $form['importable'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types the importer may write'),
      '#options' => $options,
      '#default_value' => $defaults,
      '#description' => $this->t('Only ticked types can be overwritten on import. Transactional types (orders, payments) should never be ticked.'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Save importable types')];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected = array_values(array_filter($form_state->getValue('importable') ?: []));
    $this->configFactorySvc->getEditable('open_content_improvement.settings')
      ->set('importable_entity_types', $selected)
      ->save();
    $this->messenger()->addStatus($this->t('Saved @n importable entity types.', ['@n' => count($selected)]));
  }
}
