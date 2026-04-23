# Drupal 11 Block Guide

Blocks are Plugins ("Block Plugins") placed in regions via the Block Layout UI.

## 1. Creating a Block Plugin

File: `src/Plugin/Block/MyBlock.php`

```php
namespace Drupal\my_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'My Block' block.
 */
#[Block(
  id: "my_module_my_block",
  admin_label: new TranslatableMarkup("My Custom Block"),
  category: new TranslatableMarkup("Custom")
)]
class MyBlock extends BlockBase {

  // 1. Render content
  public function build() {
    $config = $this->getConfiguration();
    return [
      '#markup' => $this->t('Hello, @name!', ['@name' => $config['my_setting'] ?? 'World']),
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];
  }

  // 2. Configuration Form
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['my_setting'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $config['my_setting'] ?? '',
    ];
    return $form;
  }

  // 3. Save Configuration
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['my_setting'] = $form_state->getValue('my_setting');
  }
}
```

## 2. Block Context

You can declare contexts to get data from the environment (e.g., current Node).

Annotation:

```php
 * context_definitions = {
 *   "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 * }
```

Usage:

```php
$node = $this->getContextValue('node');
```

## 3. Dependency Injection in Blocks

Implements `ContainerFactoryPluginInterface`.

```php
public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
  return new static(
    $configuration, $plugin_id, $plugin_definition,
    $container->get('some_service')
  );
}
```

## References

- [Block API](https://www.drupal.org/docs/drupal-apis/block-api)
- [Plugin API](https://www.drupal.org/docs/drupal-apis/plugin-api)
