<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Suggests which entity types are safe to make importable — with every signal
 * behind each recommendation. Never writes anything.
 */
final class ImportabilitySuggester {

  public function __construct(
    private readonly EntityTypeDiscovery $discovery,
    private readonly FieldClassifier $classifier,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * @return array<string, array>
   *   entity_type_id => {recommend, signals, editable_fields}
   */
  public function suggest(): array {
    $out = [];
    foreach ($this->discovery->exportableTypes() as $id => $descriptor) {
      if ($descriptor['export_only']) {
        // No uuid → cannot import.
        continue;
      }
      $type = $this->entityTypeManager->getDefinition($id);
      $bundles = $descriptor['bundles'] ?: [$id];
      $editable = 0;
      foreach ($bundles as $bundle) {
        foreach ($this->classifier->classifyAll($id, $bundle) as $result) {
          if ($result->isEditable()) {
            $editable++;
          }
        }
      }
      $hasEditForm = $type->hasLinkTemplate('edit-form');
      $hasFormDefault = $type->getFormClass('default') !== NULL;
      $adminPermission = $type->getAdminPermission();
      $signals = [
        'edit_form' => $hasEditForm,
        'form_default' => $hasFormDefault,
        'admin_permission' => (bool) $adminPermission,
        'editable_fields' => $editable,
        'provider' => $type->getProvider(),
      ];
      // Recommend when it has a form UI and editable text and is core/known.
      $recommend = $hasEditForm && $hasFormDefault && $editable > 0;
      $out[$id] = [
        'label' => (string) $type->getLabel(),
        'recommend' => $recommend,
        'signals' => $signals,
        'editable_fields' => $editable,
      ];
    }
    return $out;
  }
}
