# Drupal 11 Coding Standards

> **Strict Adherence Required.**

## 1. Core PHP Rules (MANDATORY)

- **Strict Types**: `declare(strict_types=1);` at the top of **EVERY** PHP file.
- **Indentation**: 2 spaces. NO tabs.
- **Constructor Promotion**: Use PHP 8.3+ constructor property promotion.
- **Attributes**: Use Attributes (`#[Block]`), NOT Annotations.
- **No `?>`**: Omit closing PHP tag.
- **Arrays**: Short syntax `[]`, trailing commas.
- **Naming**: `PascalCase` (Classes), `camelCase` (Methods/Vars), `snake_case` (Hooks/Functions).

## 2. Dependency Injection (DI)

- **Access**: **NEVER** use `\Drupal::service()` in classes. Inject everything.
- **Constructor**:
    ```php
    public function __construct(
      protected readonly EntityTypeManagerInterface $entityTypeManager,
      protected readonly AccountProxyInterface $currentUser,
    ) {}
    ```
- **⚠️ AJAX Form Warning**: Injected properties in **AJAX Forms** MUST NOT be `readonly`. Use `protected ServiceInterface $service;`.

## 3. HTML & Rendering

**❌ NO Deep Render Arrays / Raw HTML in Controller.**
- **Simple**: `html_tag` (e.g. wrapper, single element) is OK.
- **Complex**: Nested structure (> 2 levels), logic-heavy arrays, or concatenating strings → **MUST use `hook_theme` + Twig**.
- **Rule**: Controller passes data; Twig handles markup.

**Good**:
```php
return ['#theme' => 'my_template', '#items' => $items];
```

```php
// html_tag
['#type' => 'html_tag', '#tag' => 'h2', '#value' => 'Title', '#attributes' => ['class' => ['mb-4']]]

// inline_template
['#type' => 'inline_template', '#template' => '<i class="{{ icon }}"></i> {{ label }}', '#context' => [...]]
```

## 4. Prefer Drupal Services (Optimization)

| Native PHP | ❌ Avoid | ✅ **Prefer Drupal Service** |
| :--- | :--- | :--- |
| JSON | `json_encode` | `Drupal\Component\Serialization\Json` |
| Time | `date()`, `time()` | `DrupalDateTime`, `DateFormatterInterface` |
| Files | `file_put_contents` | `FileSystemInterface` |
| HTTP | `curl`, `file_get` | `GuzzleHttp\ClientInterface` |
| Logging | `error_log` | `LoggerInterface` |
| Strings | `"Hi $name"` | `$this->t('Hi @name', ['@name' => $n])` |

## 5. IDE Compatibility (PHPDoc)

**MANDATORY**: IDEs cannot infer generic entity types. You MUST add `@var` or `@property`.

| Context | Annotation |
| :--- | :--- |
| **Entity Form** | `@property \Drupal\my_mod\Entity\MyEnt $entity` (Class Docblock) |
| **`load($id)`** | `/** @var \Drupal\node\NodeInterface|null $node */` |
| **Loops** | `/** @var \Drupal\user\UserInterface $user */` (Inside foreach) |
| **Arrays** | `/** @var \Drupal\file\FileInterface $file */` (Before access) |

## 6. JavaScript (jQuery + Behaviors)

- **Wrapper**: `(function ($, Drupal, once) { ... })(jQuery, Drupal, once);`
- **Behaviors**: `Drupal.behaviors.myFeature = { attach: ... }`
- **Once**: `$(once('myFeat', '.sel', context)).each(...)`
