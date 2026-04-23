# Drupal 10 Custom Field Guide

Creating a custom field type requires 3 plugins: **FieldType**, **FieldWidget**, and **FieldFormatter**.

## 1. Field Type (`@FieldType`)

Defines the schema and data storage.
File: `src/Plugin/Field/FieldType/ColorField.php`

```php
namespace Drupal\my_module\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'color' field type.
 *
 * @FieldType(
 *   id = "my_color",
 *   label = @Translation("Color"),
 *   description = @Translation("Stores a hex color."),
 *   default_widget = "my_color_widget",
 *   default_formatter = "my_color_formatter"
 * )
 */
class ColorField extends FieldItemBase {

  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 7,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Hex Color'));
    return $properties;
  }

  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }
}
```

## 2. Field Widget (`@FieldWidget`)

Defines the input form element.
File: `src/Plugin/Field/FieldWidget/ColorWidget.php`

```php
namespace Drupal\my_module\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldWidget(
 *   id = "my_color_widget",
 *   label = @Translation("Color Picker"),
 *   field_types = {"my_color"}
 * )
 */
class ColorWidget extends WidgetBase {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#type' => 'color',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
    ];
    return $element;
  }
}
```

## 3. Field Formatter (`@FieldFormatter`)

Defines how the field is displayed.
File: `src/Plugin/Field/FieldFormatter/ColorFormatter.php`

```php
namespace Drupal\my_module\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldFormatter(
 *   id = "my_color_formatter",
 *   label = @Translation("Color Swatch"),
 *   field_types = {"my_color"}
 * )
 */
class ColorFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => '<div style="background-color:' . $item->value . ';">Box</div>',
      ];
    }
    return $elements;
  }
}
```

## References

- [Field API](https://www.drupal.org/docs/drupal-apis/field-api)
