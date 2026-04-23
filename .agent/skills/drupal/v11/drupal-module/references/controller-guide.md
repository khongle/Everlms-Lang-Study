# Drupal 11 Controller Guide

> **Ref:** [Drupal 11 Coding Standards](../../drupal-best-practices/references/coding-standards.md)

## 1. Basic Controller

- Extends `ControllerBase`.
- Returns **Render Array** (HTML, JSON, File).
- **Strict Types**: Always `declare(strict_types=1);`.

```php
declare(strict_types=1);

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase {

  public function content(): array {
    return [
      '#theme' => 'my_module_page',
      '#variable' => $value,
      '#attached' => ['library' => ['my_module/lib']],
    ];
  }

}

## 2. Page-Level Template Pattern (MANDATORY)

> **Rule**: Mỗi trang custom trả về **MỘT `#theme` duy nhất**. Controller chỉ chuẩn bị **View Model** (mảng dữ liệu thuần). Twig xử lý toàn bộ HTML.

### ❌ Anti-patterns (KHÔNG ĐƯỢC LÀM)
- Ghép nhiều `#theme` children (`header`, `table`, `card`) trong Controller
- Đặt `#attributes => ['class' => [...]]` hoặc CSS classes trong Controller
- Dùng `#type => 'table'` + `#header` + `#rows` trong Controller
- Dùng `#type => 'html_tag'` cho badge/span/icon
- Dùng `#type => 'inline_template'` cho action buttons

### ✅ Pattern đúng

**1. hook_theme() — chỉ khai báo dữ liệu thuần:**
```php
function my_module_theme(): array {
  return [
    'my_module_list_page' => [
      'variables' => [
        'create_url' => NULL,
        'can_create' => FALSE,
        'rows' => [],
        'pager' => NULL,
      ],
    ],
  ];
}
```

**2. Controller — chỉ chuẩn bị View Model:**
```php
public function list(): array {
  $rows = [];
  foreach ($entities as $entity) {
    $rows[] = [
      'id' => $entity->id(),
      'title' => $entity->label(),
      'status' => $entity->isPublished(),
      'view_url' => Url::fromRoute('my.view', ['id' => $entity->id()])->toString(),
    ];
  }

  return [
    '#theme' => 'my_module_list_page',
    '#create_url' => Url::fromRoute('my.create')->toString(),
    '#can_create' => $this->currentUser()->hasPermission('create content'),
    '#rows' => $rows,
    '#pager' => ['#type' => 'pager'],
  ];
}
```

**3. Twig (templates/my-module-list-page.html.twig) — toàn bộ HTML ở đây:**
```twig
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="h4 mb-0">{{ 'Items'|t }}</h2>
  {% if can_create %}
    <a href="{{ create_url }}" class="btn btn-primary shadow-sm">
      <i class="bi bi-plus-circle me-1"></i>{{ 'Add Item'|t }}
    </a>
  {% endif %}
</div>

<table class="table table-hover">
  <thead>
    <tr><th>{{ 'ID'|t }}</th><th>{{ 'Title'|t }}</th></tr>
  </thead>
  <tbody>
    {% for row in rows %}
      <tr>
        <td>{{ row.id }}</td>
        <td><a href="{{ row.view_url }}">{{ row.title }}</a></td>
      </tr>
    {% endfor %}
  </tbody>
</table>

{{ pager }}
```

## 2. Dependency Injection (DI)

- **Use Constructor Promotion** (PHP 8.3+).
- **Inject Everything**: No `\Drupal::service()`.

```php
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MyController extends ControllerBase {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

}
```

## 3. Routing (`my_module.routing.yml` & Entity Upcasting)

> **⚠️ BẮT BUỘC:** Khi route URL chứa ID của một entity (VD: `/my-page/{node}`), **KHÔNG ĐƯỢC** dùng `$routeMatch->getParameter('node')` (trả về chuỗi ID) rồi tự gọi `$storage->load($id)` bên trong Controller. Thay vào đó, **BẮT BUỘC** phải khai báo `options.parameters` trong file routing để Drupal tự động load entity (Upcast parameter) và truyền nó thẳng vào method của Controller. Việc này giúp code type-safe và xử lý 404 tự động nếu entity không tồn tại.

```yaml
my_module.page:
  path: '/my-page/{node}'
  defaults:
    _controller: '\Drupal\my_module\Controller\MyController::content'
    _title: 'My Page'
  requirements:
    _permission: 'access content'
  options:
    parameters:
      node:
        type: entity:node
```
*(Trong Controller, param `$node` giờ đây sẽ nhận type `\Drupal\node\NodeInterface` thay vì `string`)*

## 4. Response Types

### HTML (Render Array)
Standard return.

### JSON (API)
```php
use Symfony\Component\HttpFoundation\JsonResponse;
return new JsonResponse(['data' => $items]);
```

### File Download
```php
use Symfony\Component\HttpFoundation\BinaryFileResponse;
return new BinaryFileResponse($uri);
```

### Redirect
```php
return $this->redirect('entity.node.canonical', ['node' => 1]);
```

## 5. Access Control

- **Permission**: `_permission: 'access content'`
- **Role**: `_role: 'administrator'`
- **Custom**: `_custom_access: '\Drupal\my_mod\Controller\MyController::checkAccess'`

## 6. URL Generation (MANDATORY)

> **Rule**: KHÔNG BAO GIỜ dùng URL cứng (`'/orders/create'`). LUÔN dùng routing system.

### Trong Controller / Service:
```php
use Drupal\Core\Url;

// URL string
$url = Url::fromRoute('my_module.create')->toString();

// URL with parameters
$url = Url::fromRoute('my_module.view', ['id' => $entity->id()])->toString();

// Pass to Twig as variable
return [
  '#theme' => 'my_page',
  '#create_url' => Url::fromRoute('my_module.create')->toString(),
];
```

### Trong Twig:
```twig
{# Option 1: URL đã generate từ Controller (recommended) #}
<a href="{{ create_url }}">{{ 'Create'|t }}</a>

{# Option 2: Dùng path() trực tiếp (khi cần) #}
<a href="{{ path('my_module.create') }}">{{ 'Create'|t }}</a>
```
