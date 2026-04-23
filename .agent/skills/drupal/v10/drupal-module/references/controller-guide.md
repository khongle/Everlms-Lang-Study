# Drupal 10 Controller Guide

> **Ref:** [Drupal 10 Coding Standards](../../drupal-best-practices/references/coding-standards.md)

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
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $this->t('Hello World'),
      '#attached' => ['library' => ['my_module/lib']],
      '#cache' => ['contexts' => ['user.permissions']],
    ];
  }

}
```

## 2. Dependency Injection (DI)

- **Use Constructor Promotion** (PHP 8.1+).
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

```php
public function checkAccess(AccountInterface $account): AccessResult {
  return AccessResult::allowedIf($account->hasPermission('view my page'));
}
```
