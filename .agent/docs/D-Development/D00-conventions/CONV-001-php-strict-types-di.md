# CONV-001 — PHP Strict Types & Constructor-Promoted Dependency Injection

> **Rule:** Every PHP file in `web/modules/custom/` starts with `declare(strict_types=1);`. Services, controllers, and form classes receive all dependencies through constructor injection. `\Drupal::` static service locator calls are forbidden inside any injectable class.
>
> **Why:** `strict_types=1` prevents PHP's silent type coercion from hiding data bugs. Constructor injection makes every dependency explicit and testable — no hidden global state, no coupling to the container at call time.
>
> **Backed by:** Project convention (no ADR filed). Enforced by Drupal Coding Standards + `phpstan` level TBD.

---

## Pattern

Canonical service class from `lms_base` (`web/modules/custom/lms_base/src/Service/LmsHelper.php`):

```php
<?php

declare(strict_types=1);

namespace Drupal\lms_base\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Shared helper service for EverLMS modules.
 */
final class LmsHelper {

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountProxyInterface $currentUser,
  ) {}

  public function getCurrentUserId(): int {
    return (int) $this->currentUser->id();
  }

  public function getTermsByVocabulary(string $vid): array {
    /** @var \Drupal\taxonomy\TermStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    return $storage->loadTree($vid, 0, NULL, TRUE);
  }
}
```

Key points:
- `declare(strict_types=1)` on line 3, before the `namespace` statement.
- All `use` statements listed alphabetically after `namespace`.
- Class is `final` — prevents unintended subclassing.
- Constructor uses **property promotion** (`private EntityTypeManagerInterface $entityTypeManager`).
- No `\Drupal::` call anywhere in the class body.

### Controllers (ControllerBase subclass)

`ControllerBase` provides untyped `$entityTypeManager` / `$configFactory` / `$currentUser` properties. When injecting these, assign them in the constructor body to avoid PHP property-type redeclaration conflicts. Custom dependencies use promotion:

```php
<?php

declare(strict_types=1);

namespace Drupal\lms_mobile_api\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_mobile_api\Service\OtpService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MobileAuthController extends ControllerBase {

  public function __construct(
    protected OtpService $otpService,         // custom dep — use promotion
    EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface $configFactory,
    protected LoggerInterface $logger,
  ) {
    // ControllerBase properties are untyped — assign directly.
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_mobile_api.otp_service'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('logger.factory')->get('lms_mobile_api'),
    );
  }
}
```

### services.yml registration

```yaml
# web/modules/custom/lms_base/lms_base.services.yml
services:
  lms_base.helper:
    class: Drupal\lms_base\Service\LmsHelper
    arguments:
      - '@entity_type.manager'
      - '@current_user'
```

---

## Don't

- Do not omit `declare(strict_types=1)` — even in `.module` and `.install` files (check: both use it in EverLMS).
- Do not call `\Drupal::service(...)`, `\Drupal::entityTypeManager()`, or any other `\Drupal::*` static inside a class that is or can be a service (this includes controllers, form classes, event subscribers, queue workers, and block plugins). The `.module` file procedural functions are the only legitimate place.
- Do not inject the entire container (`ContainerInterface`) into a service — inject only the specific services needed.
- Do not use untyped `$container->get()` return values without a `@var` docblock when the type cannot be inferred.
- Do not duplicate `use` imports — one `use` per fully-qualified class name per file.

---

## Checklist

- [ ] File begins with `<?php`, then a blank line (optional docblock), then `declare(strict_types=1);`.
- [ ] All dependencies are listed as constructor parameters (not set via setters or fetched with `\Drupal::`).
- [ ] `use` statements cover every class referenced without full namespace in the file body.
- [ ] Custom (non-ControllerBase) service classes are declared `final`.
- [ ] `static::create()` / `ContainerInterface` wiring is present in any class that needs it (controllers, forms, plugins, subscribers).
- [ ] No `\Drupal::service()`, `\Drupal::entityTypeManager()`, or similar statics inside any method of an injectable class.
- [ ] Return types are declared on all methods (PHP 8 union/nullable types allowed).

---

## Related

[`CONV-002-entity-conventions.md`](CONV-002-entity-conventions.md) — entity classes also follow these patterns.
[`CONV-004-routing-controllers.md`](CONV-004-routing-controllers.md) — controller DI pattern in context of routing.
