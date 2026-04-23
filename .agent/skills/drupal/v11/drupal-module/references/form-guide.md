# Drupal 11 Form Guide

> **Ref:** [Drupal 11 Coding Standards](../../drupal-best-practices/references/coding-standards.md)

## 1. Form Types

### ConfigFormBase (Settings)

- Extends `ConfigFormBase`.
- Handles `getEditableConfigNames`.

### FormBase (Generic)

- Extends `FormBase`.
- Used for non-entity, custom forms.

### Entity Route Upcasting trong Form

> **⚠️ BẮT BUỘC:** Nếu form thao tác trên một Entity cụ thể từ Route URL (VD: `/orders/{commerce_order}/edit`), **KHÔNG ĐƯỢC** tự load Entity trong method `buildForm()` bằng file storage. Thay vào đó, **BẮT BUỘC** khai báo `options.parameters.{param_name}.type: entity:{entity_type}` trong `my_module.routing.yml`. Khi đó `$this->routeMatch->getParameter('param_name')` sẽ trả về trực tiếp Object Entity thay vì string ID. Điều này đặc biệt ngăn chặn lỗi Type Error do upcast sai và fix luôn lỗi 404 cho entity bị xoá.

## 2. AJAX Rules (MANDATORY)

- **Usage**: Use `#ajax` + `$form_state` for dynamic elements.
- **Client-Side**: Only for pure UI enhancement (show/hide).
- **⚠️ CRITICAL WARNING**: Properties injected into classes extending `FormBase` or `ConfigFormBase` (which use `DependencySerializationTrait`) **MUST NOT be `readonly`**.
  - Drupal's Form caching mechanism serializes and unserializes form objects during AJAX and Batch operations. PHP 8.1+ will throw a Fatal Error (`Cannot initialize readonly property`) when attempting to unserialize a `readonly` property.
    - **Wrong**: `protected readonly MyService $service;`
    - **Correct**: `protected MyService $service;`

## 3. Basic AJAX Example

```php
$form['select'] = [
  '#type' => 'select',
  '#options' => ['a' => 'A', 'b' => 'B'],
  '#ajax' => [
    'callback' => '::ajaxCallback',
    'wrapper' => 'result-wrapper',
  ],
];

$form['result'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'result-wrapper'],
  '#markup' => $this->t('Initial Content'), // #markup OK for simple text here
];

public function ajaxCallback(array &$form, FormStateInterface $form_state): array {
  return $form['result'];
}
```

## 4. Validation

- **Form-Level**: `validateForm()`.
- **Element-Level**: `#element_validate` callback.
- **Constraints**: Use field constraints for entity forms.

```php
public function validateForm(array &$form, FormStateInterface $form_state): void {
  if (empty($form_state->getValue('name'))) {
    $form_state->setErrorByName('name', $this->t('Required.'));
  }
}
```

## 5. Batch API

- Use for synchronous, long-running processes.

```php
$batch = [
  'title' => $this->t('Processing...'),
  'operations' => [
    [[static::class, 'processItem'], [$id]],
  ],
  'finished' => [static::class, 'batchFinished'],
];
batch_set($batch);
```

## 6. Queue API

- Use for asynchronous, background processing (Cron).
- Define Queue Worker Plugin (using Attributes).
- Add items: `\Drupal::queue('name')->createItem($data);`
