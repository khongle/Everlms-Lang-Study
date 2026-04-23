# Drupal 11 JavaScript Guide

## 1. Drupal Behaviors Pattern (MANDATORY)

All JavaScript in Drupal modules **MUST** use the Drupal Behaviors pattern with jQuery and `once()`.

### Standard Template

```javascript
/**
 * @file
 * Description of what this JS file does.
 */
(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.myModuleBehaviorName = {
    attach: function (context, settings) {
      // once() ensures this runs only once per element.
      $(once('myUniqueName', '.my-selector', context)).each(function () {
        var $element = $(this);
        // Your initialization code here.
      });
    },
    // Optional: cleanup when elements are removed from DOM.
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        // Cleanup code here.
      }
    }
  };

})(jQuery, Drupal, once);
```

### Key Rules

| Rule | ✅ Correct | ❌ Wrong |
|------|-----------|---------|
| DOM query | `$(context).find('.class')` or `$('#id')` | `document.getElementById()` |
| Event binding | `$el.on('click', fn)` | `el.addEventListener('click', fn)` |
| Init guard | `once('name', selector, context)` | `el.dataset.initialized = true` |
| Element creation | `$('<div>', { class: 'foo' })` | `document.createElement('div')` |
| IIFE wrapper | `(function ($, Drupal, once) {...})(jQuery, Drupal, once)` | `(function (Drupal) {...})(Drupal)` |
| Text translation | `Drupal.t('My text')` | Hardcoded strings |

---

## 2. Library Definition

Every JS file must be registered in `{module}.libraries.yml`:

```yaml
my_library_name:
  js:
    js/my-script.js: {}
  css:
    component:
      css/my-styles.css: {}
  dependencies:
    - core/drupal        # Always required
    - core/jquery        # Required for jQuery
    - core/once          # Required for once()
    - core/drupal.ajax   # Only if using Drupal AJAX
    - core/drupalSettings # Only if using drupalSettings
```

### Attaching Libraries

```php
// In Form or Render Array:
$form['#attached']['library'][] = 'my_module/my_library_name';

// In Twig template:
// {{ attach_library('my_module/my_library_name') }}
```

---

## 3. drupalSettings (Passing PHP Data to JS)

```php
// PHP side:
$form['#attached']['drupalSettings']['myModule'] = [
  'apiUrl' => '/api/endpoint',
  'items' => $items,
];
```

```javascript
// JS side:
Drupal.behaviors.myModule = {
  attach: function (context, settings) {
    var apiUrl = settings.myModule.apiUrl;
    var items = settings.myModule.items;
  }
};
```

---

## 4. Drupal AJAX API (Server Communication)

### AJAX Forms (PREFERRED for form interactions)

Use `#ajax` property on form elements — Drupal handles everything automatically:

```php
$form['product_type'] = [
  '#type' => 'select',
  '#title' => $this->t('Product Type'),
  '#options' => $options,
  '#ajax' => [
    'callback' => '::ajaxCallback',
    'wrapper' => 'result-wrapper',
    'event' => 'change',
    'progress' => ['type' => 'throbber'],
  ],
];

$form['result'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'result-wrapper'],
];
```

```php
public function ajaxCallback(array &$form, FormStateInterface $form_state): array {
  // Return the element to replace.
  return $form['result'];
}
```

### AjaxResponse with Commands

For more complex responses:

```php
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

public function ajaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
  $response = new AjaxResponse();
  $response->addCommand(new ReplaceCommand('#wrapper', $form['result']));
  $response->addCommand(new InvokeCommand('#field', 'val', ['new value']));
  return $response;
}
```

### When to Use What

| Scenario | Approach |
|----------|----------|
| Dynamic form elements (add/remove rows, tags) | `#ajax` + `$form_state` |
| Form element triggers server update | `#ajax` on form element |
| Complex multi-command response | `AjaxResponse` + commands |
| Keyboard shortcut triggers existing AJAX | Minimal JS → trigger AJAX button |
| Pure UI enhancement (no data) | Client-side jQuery only |
| Custom non-form AJAX endpoint | **⚠️ Ask user for approval first** |

---

## 5. When Custom JS is Acceptable (No Server Round-Trip)

Client-side-only JS is acceptable for:
- Show/hide toggle
- Client-side validation feedback
- Sorting / filtering already-loaded data
- Keyboard shortcuts that trigger an existing AJAX button

**NOT acceptable** for client-side JS (use AJAX Form API instead):
- Dynamic row add/remove → use `#ajax` + `$form_state`
- Tag-input / chip creation → use `#ajax` + server-side rendering
- Any operation that generates HTML → render server-side

**Rules for JS files:**
- No HTML templates in JS
- No inline CSS in JS (use `.css` files)
- JS should only trigger existing Drupal AJAX or handle pure UI enhancement

**BUT** must still follow jQuery + `once()` pattern.

---

## 6. Common Patterns

### Delegated Event Binding (for UX enhancement only)

```javascript
$(once('myEvents', '#container', context)).each(function () {
  var $container = $(this);

  // Delegated: trigger AJAX for dynamically added elements.
  // Example: keyboard shortcut triggers a hidden AJAX submit button.
  $container.on('keydown', '.tag-value-input', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      var attrIndex = $(this).attr('data-attr-index');
      $('input[name="add_tag_' + attrIndex + '"]').trigger('mousedown');
    }
  });

  // ⚠️ For row deletion — use AJAX Form API, NOT client-side removal:
  // ❌ WRONG: $(this).closest('.row').remove();
  // ✅ RIGHT: Use #ajax submit button that calls server-side handler.
});
```

### Drupal.t() for Translations

```javascript
var message = Drupal.t('Are you sure you want to delete @name?', { '@name': name });
```

---

## References

- [Drupal JavaScript API](https://www.drupal.org/docs/drupal-apis/javascript-api)
- [jQuery Once](https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview#s-behaviors)
- [AJAX API](https://www.drupal.org/docs/drupal-apis/ajax-api)
- [drupalSettings](https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview#s-drupalsettings)
