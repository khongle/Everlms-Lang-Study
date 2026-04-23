# K02 — Coding Conventions

> Source of truth for style decisions. Mirrors and extends A-Project-Information/A01-CLAUDE.md.
> Updated: 2026-04-16. Cross-references: A-Project-Information/A01-CLAUDE.md §Banned Patterns, A08-layer-templates.

---

## PHP / Drupal Standards

| Rule | Detail |
|------|--------|
| Drupal Coding Standards | Enforced via PHPCS (`phpcs.xml`) |
| DrupalPractice sniffs | Enforced in CI |
| PHPStan | Level 5+ (CI enforced) |
| Strict types | `declare(strict_types=1);` in every PHP file |
| PHP version | 8.3 minimum |

---

## File & Namespace Naming

| Context | Convention | Example |
|---------|-----------|---------|
| Module prefix | `lms_` for all custom modules | `lms_course`, `lms_quiz`, `lms_payment` |
| Module directory | `web/modules/custom/lms_{name}/` | `web/modules/custom/lms_progress/` |
| Namespace root | `Drupal\lms_{name}\` | `Drupal\lms_progress\Service\ProgressCalculator` |
| Controller class | `Drupal\lms_{name}\Controller\{Name}Controller` | `Drupal\lms_quiz\Controller\QuizController` |
| Service class | `Drupal\lms_{name}\Service\{Name}` or `{Name}Service` | `Drupal\lms_progress\Service\ProgressCalculator` |
| Plugin class | `Drupal\lms_{name}\Plugin\{PluginType}\{PluginId}` | `Drupal\lms_payment\Plugin\PaymentGateway\StripeGateway` |
| Form class | `Drupal\lms_{name}\Form\{Name}Form` | `Drupal\lms_course\Form\CourseEnrollForm` |
| Entity class | `Drupal\lms_{name}\Entity\{EntityName}` (PascalCase) | `Drupal\lms_quiz\Entity\QuizResult` |
| Event class | `Drupal\lms_{name}\Event\{Name}Event` | `Drupal\lms_progress\Event\CourseCompletedEvent` |
| Test class | `Drupal\Tests\lms_{name}\{Level}\{Name}Test` | `Drupal\Tests\lms_progress\Unit\ProgressCalculatorTest` |

---

## Service & Route ID Conventions

| Context | Convention | Example |
|---------|-----------|---------|
| Service ID | `lms_{module}.{service_name}` (snake_case) | `lms_progress.calculator`, `lms_ai.provider_manager` |
| Route ID | `lms_{module}.{route_name}` | `lms_course.catalog`, `lms_quiz.submit` |
| Permission | `{action} lms_{module} {entity}` | `create lms_course course`, `view lms_quiz quiz` |
| Plugin ID | `snake_case` | `single_choice`, `stripe_gateway`, `openai_provider` |
| Hook | `lms_{module}_{hook_name}()` | `lms_course_entity_presave()` |
| Config object | `lms_{module}.settings` | `lms_payment.settings` |
| Feature flag | `FF_FEAT_{UPPER}` or `FF_EXP_{UPPER}` | `FF_FEAT_AI_COURSE_BUILDER` |

---

## Template Naming

| Context | Convention | Example |
|---------|-----------|---------|
| Twig templates | `lms-{module}--{variant}.html.twig` | `lms-course--card.html.twig`, `lms-quiz--results.html.twig` |
| Template location | `web/modules/custom/lms_{name}/templates/` | — |
| Theme override | Same name in `lms_theme/templates/` overrides module template | — |
| Preprocess function | `lms_{module}_preprocess_{template_hook}()` | `lms_course_preprocess_lms_course__card()` |

---

## CSS Class Prefix

| Context | Convention | Example |
|---------|-----------|---------|
| Custom component root | `.lms-{component}` | `.lms-card`, `.lms-quiz`, `.lms-progress-bar` |
| BEM element | `.lms-{component}__{element}` | `.lms-card__header`, `.lms-quiz__timer` |
| BEM modifier | `.lms-{component}--{modifier}` | `.lms-card--featured`, `.lms-progress-bar--complete` |
| State class | `.is-{state}` or `.has-{feature}` | `.is-enrolled`, `.has-certificate` |
| SCSS location | `lms_theme/scss/` (16 files) | `_course-card.scss`, `_quiz.scss` |
| Bootstrap | Bootstrap 5.3.3 — use utility classes; add BEM only for custom behavior | — |

---

## Dependency Injection Rules

```php
// CORRECT — constructor injection via ContainerInjectionInterface
final class EnrollmentService implements ContainerInjectionInterface {
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly LoggerInterface $logger,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('logger.factory')->get('lms_progress'),
    );
  }
}

// WRONG — static service call in business code
class BadService {
  public function doThing(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('lms_course'); // BANNED
  }
}
```

**Rules:**
- Always constructor injection in services, controllers, forms.
- `\Drupal::service()` / `\Drupal::entityTypeManager()` allowed **only** in `.module` hook callbacks and procedural callbacks (no class available).
- Use `ContainerInjectionInterface` for forms and controllers.

---

## Entity API Conventions

```php
// Entity definition via PHP 8 Attributes (Drupal 11 style)
#[ContentEntityType(
  id: 'lms_course',
  label: new TranslatableMarkup('Course'),
  handlers: [
    'storage' => 'Drupal\lms_course\CourseStorage',
    'access' => 'Drupal\lms_course\CourseAccessControlHandler',
    'views_data' => 'Drupal\lms_course\CourseViewsData',
    'form' => [
      'default' => 'Drupal\lms_course\Form\CourseForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'list_builder' => 'Drupal\lms_course\CourseListBuilder',
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
  ],
  base_table: 'lms_course',
  admin_permission: 'administer lms_course course',
  entity_keys: ['id' => 'id', 'label' => 'name', 'uuid' => 'uuid', 'uid' => 'uid', 'status' => 'status'],
  links: ['canonical' => '/course/{lms_course}', ...],
)]
class Course extends ContentEntityBase { ... }
```

**Required handlers for every custom content entity:**

| Handler | Purpose | Omit only if... |
|---------|---------|-----------------|
| `access` | View/edit/delete access control | Never omit |
| `views_data` | Drupal Views integration | Explicitly excluded in D03 spec |
| `form` | Admin add/edit forms | Config entity or no UI needed |
| `list_builder` | Admin listing page | No admin UI needed |

---

## Plugin Rules

```php
// CORRECT — interface covers all externally-called methods
interface QuestionHandlerInterface {
  public function render(Question $question): array;
  public function validate(array $response, Question $question): bool;
  public function grade(array $response, Question $question): float;
  public function getMaxScore(Question $question): float;
}

// WRONG — method added to concrete class only (duck-typing violation, see K04)
class SingleChoiceHandler implements QuestionHandlerInterface {
  public function render(...): array { ... }
  public function validate(...): bool { ... }
  public function grade(...): float { ... }
  public function getMaxScore(...): float { ... }
  public function getRenderHint(): string { ... }  // NOT on interface — BANNED
}
```

**Rules:**
- Every public method called from outside the class must appear in the interface.
- Plugin ID must be `snake_case` matching the `#[PluginType(id: '...')]` attribute.
- Plugin selection must be config-driven; never hardcode class names in factories.

---

## Service Pattern

```php
// Constructor promotion (PHP 8.1+, preferred in Drupal 11)
final class ProgressCalculator {
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly LoggerChannelInterface $logger,
  ) {}
}
```

---

## Access Control

| Scope | Mechanism |
|-------|-----------|
| Entity view/edit/delete | `EntityAccessControlHandler::checkAccess()` |
| Entity create | `EntityAccessControlHandler::checkCreateAccess()` |
| Route-level | `_permission` or `_custom_access` in `*.routing.yml` |
| Data scope (own-only) | `hook_query_alter` or entity query condition |
| Never | Trust client-supplied entity ownership; always re-verify server-side |

---

## Frontend / Twig

```twig
{# CORRECT — logic-free template #}
<div class="lms-card{% if is_enrolled %} lms-card--enrolled{% endif %}">
  <h2 class="lms-card__title">{{ course_title }}</h2>
  <p class="lms-card__price">{{ price_display }}</p>
</div>

{# WRONG — logic in template #}
{% set discounted_price = course.field_price * 0.9 %}  {# business logic — BANNED #}
```

**Rules:**
- Business logic belongs in services, not in Twig.
- Twig receives pre-computed variables from preprocess functions.
- No inline PHP (`{{ constant('SOME_CONST') }}` allowed but prefer preprocess).
- Use `{{ variable|t }}` for user-facing strings (translatability).

---

## JavaScript

```js
// Drupal behaviors pattern — required for all lms_* JS
(function ($, Drupal) {
  Drupal.behaviors.lmsCourseEnroll = {
    attach: function (context, settings) {
      once('lms-enroll', '.lms-enroll-btn', context).forEach(function (el) {
        el.addEventListener('click', function () { /* ... */ });
      });
    }
  };
})(jQuery, Drupal);
```

Use `once()` (Drupal 10+) to prevent double-attachment on AJAX-rebuilt elements.

---

## Testing Conventions

| Level | Location | Superclass |
|-------|----------|-----------|
| Unit | `modules/lms_{m}/tests/src/Unit/` | `UnitTestCase` |
| Kernel | `modules/lms_{m}/tests/src/Kernel/` | `KernelTestBase` |
| Functional | `modules/lms_{m}/tests/src/Functional/` | `BrowserTestBase` |
| JavaScript | `modules/lms_{m}/tests/src/FunctionalJavascript/` | `JavascriptTestBase` |

**Naming rules:**
- Test class: `{ClassName}Test` — e.g., `ProgressCalculatorTest`
- Test method: `test{Behavior}` — e.g., `testLessonCompletionUpdatesChapterProgress`
- Data provider: `provide{Behavior}Cases()` — static, returns array of arrays

**Minimum coverage for P0 units (G-HARDENING):** Kernel test covering happy path + boundary + error path.

---

## Git

| Branch | Purpose |
|--------|---------|
| `main` | Production-ready |
| `feature/*` | New features |
| `bugfix/*` | Bug fixes |
| `release/*` | Release preparation |

**Commit message format:**
```
type(scope): short description

Types: feat, fix, refactor, test, docs, chore, perf, security
Scope: module name without lms_ prefix

feat(course): add prerequisite gate check
fix(progress): handle concurrent cascade race condition (RISK-002)
test(quiz): kernel test for essay auto-grade bypass
security(payment): add Stripe webhook signature verification (FIND-001)
```

---

## Banned Patterns (summary — full list in A-Project-Information/A01-CLAUDE.md)

| Pattern | Reason |
|---------|--------|
| `\Drupal::service()` in class methods | Bypasses DI, untestable |
| `#markup` with developer-controlled strings | XSS vector |
| `readonly` on form state value objects | Fatal on AJAX rebuild |
| Hardcoded service class names in factory methods | Breaks plugin swapping |
| Public methods on plugin class not declared on interface | Duck-typing violation |
| Raw `$_POST` reads or `#markup` with `$_POST` data | Injection / XSS |
| Missing `declare(strict_types=1)` | Type coercion bugs |

---

*End of K02-coding-conventions.md*
