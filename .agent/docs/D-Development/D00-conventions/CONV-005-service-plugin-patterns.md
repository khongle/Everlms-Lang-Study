# CONV-005 — Service and Plugin Patterns

> **Rule**: Register all reusable cross-module logic as named Drupal services (`lms_{module}.{name}` in `services.yml`). Use Drupal's `DefaultPluginManager` + annotation pattern when third parties must extend a type at runtime. Use a static PHP factory only when the strategy set is closed and speed matters more than extensibility — and document the choice explicitly.
> **Why**: Consistent service IDs make dependency injection predictable and grep-able. Choosing the wrong extensibility mechanism (static factory when a plugin manager is needed, or a plugin manager for a fixed-set strategy) adds unnecessary complexity or removes legitimate extension points.
> **Backed by**: project convention.

---

## 1. Service ID Convention

All custom services follow the pattern:

```
lms_{module}.{name}
```

| Module | Service ID | Class |
|--------|-----------|-------|
| `lms_certificate` | `lms_certificate.generator` | `CertificateGenerator` |
| `lms_certificate` | `lms_certificate.manager` | `CertificateManager` |
| `lms_certificate` | `lms_certificate.event_listener` | `CertificateEventListener` |
| `lms_ai` | `plugin.manager.lms_ai_provider` | `AiProviderManager` |
| `lms_ai` | `lms_ai.course_builder` | `CourseBuilderService` |
| `lms_zoom` | `lms_zoom.service` | `ZoomService` |
| `lms_zoom` | `lms_zoom.ai_service` | `ZoomAiService` |
| `lms_payment` | `plugin.manager.payment_gateway` | `PaymentGatewayManager` |
| `lms_membership` | `lms_membership.manager` | `MembershipManager` |
| `lms_notification` | `lms_notification.manager` | `NotificationManager` |
| `lms_analytics` | `lms_analytics.course_report` | `CourseReportManager` |

**Exception:** Drupal plugin managers follow the Drupal convention `plugin.manager.{namespace}` instead of `lms_{module}.*`. Both `plugin.manager.lms_ai_provider` and `plugin.manager.payment_gateway` use this form.

Logger channels use `logger.channel.{module}`:

```yaml
# lms_zoom.services.yml
logger.channel.lms_zoom:
  parent: logger.channel_base
  arguments: ['lms_zoom']
```

---

## 2. Drupal Plugin Manager Pattern (as-built: `lms_payment`, `lms_ai`)

Use `DefaultPluginManager` + annotation when:
- Other modules or third parties need to register new implementations.
- Discovery must happen at runtime from any installed module.
- The type is an open extension point (payment gateways, AI providers).

### Pattern: `lms_payment` — `@PaymentGateway`

**Annotation class** (`src/Annotation/PaymentGateway.php`):

```php
/**
 * @Annotation
 */
class PaymentGateway extends Plugin {
  public string $id;
  public TranslatableMarkup $label;
  public TranslatableMarkup $description;
}
```

**Plugin manager** (`src/Plugin/PaymentGatewayManager.php`):

```php
class PaymentGatewayManager extends DefaultPluginManager {
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      'Plugin/PaymentGateway',        // sub-namespace to scan
      $namespaces,
      $module_handler,
      PaymentGatewayInterface::class, // plugin interface
      PaymentGateway::class,          // annotation class
    );
    $this->alterInfo('lms_payment_payment_gateway_info');
    $this->setCacheBackend($cache_backend, 'lms_payment_payment_gateway_plugins');
  }
}
```

**Registration** (`lms_payment.services.yml`):

```yaml
plugin.manager.payment_gateway:
  class: Drupal\lms_payment\Plugin\PaymentGatewayManager
  parent: default_plugin_manager
```

**Concrete plugin** (`lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`):

```php
/**
 * @PaymentGateway(
 *   id = "stripe",
 *   label = @Translation("Stripe"),
 *   description = @Translation("Process payments via Stripe Checkout.")
 * )
 */
class StripeGateway extends PaymentGatewayBase { ... }
```

### Pattern: `lms_ai` — `@AiProvider`

Identical structure; plugin directory `Plugin/AiProvider`, annotation `@AiProvider`, alter hook `lms_ai_provider_info`, cache bin `lms_ai_provider_plugins`. Three providers ship with the module: `openai`, `gemini`, `pollinations`.

---

## 3. Static Factory Alternative (as-built: `lms_quiz` — `GradingStrategyFactory`)

`lms_quiz` uses a **static PHP factory + strategy pattern** instead of a Drupal Plugin manager:

```php
// src/Grading/GradingStrategyFactory.php
class GradingStrategyFactory {
  public static function getStrategy(string $bundle): ?QuestionGradingInterface {
    return match ($bundle) {
      'single_choice'   => new SingleChoiceStrategy(),
      'multiple_choice' => new MultipleChoiceStrategy(),
      'true_false'      => new TrueFalseStrategy(),
      'fill_blank'      => new FillBlankStrategy(),
      'short_answer'    => new ShortAnswerStrategy(),
      'ordering'        => new OrderingStrategy(),
      'matching'        => new MatchingStrategy(),
      'essay'           => new EssayStrategy(),
      default           => NULL,
    };
  }
}
```

### Why it diverges

The grading strategy set is **closed by design**: the 8 question bundles are defined as config entities in `lms_question/config/install/`. No external module can add a ninth bundle without a schema migration. Therefore:

- Runtime plugin discovery adds overhead (DI container, annotation scanning, cache) for zero benefit.
- The `match` expression is exhaustive and enforced by PHP; adding a bundle requires editing exactly two files (the config install YML + the factory `match`), not wiring a new plugin.
- Grading strategies carry no Drupal API dependencies (no `@inject`, no container), which makes them unit-testable with plain PHPUnit.

**When NOT to use a static factory:**
- When third-party modules must register new implementations at runtime.
- When the strategy requires Drupal services (entity manager, config, logger) — inject via constructor instead.
- When the strategy set is open-ended (e.g., payment gateways, AI providers).

---

## 4. QueueWorker for Async AI (as-built: `lms_ai`)

Long-running AI generation tasks (generating lesson HTML content via OpenAI) are offloaded via Drupal's Queue API:

```php
// src/Plugin/QueueWorker/CourseContentGenerator.php
/**
 * @QueueWorker(
 *   id = "lms_ai_content_generator",
 *   title = @Translation("LMS AI Course Content Generator"),
 *   cron = {"time" = 60}
 * )
 */
class CourseContentGenerator extends QueueWorkerBase {
  public function processItem($data): void {
    // $data contains lesson_id, prompt_for_content, tone
    $lesson = $this->entityTypeManager->getStorage('lms_lesson')->load($data['lesson_id']);
    $content = $this->aiProvider->createInstance($data['provider'])->generateText($data['prompt']);
    $lesson->set('field_content', ['value' => $content, 'format' => 'full_html']);
    $lesson->set('status', 1);
    $lesson->save();
  }
}
```

`CourseBuilderService::generateCourseStructure()` creates course + lesson entities synchronously, then queues one item per lesson. Workers process up to 60 seconds per cron run.

**Known limitation (as-built):** No rollback/compensation. If `processItem()` fails, Drupal requeueues the item for retry, but already-created empty lesson entities remain in the database. There is no saga/cleanup pattern.

---

## Don't

- Do not register a service with an arbitrary ID — always use `lms_{module}.{name}`.
- Do not use `\Drupal::service()` inside injectable classes; use constructor injection via `services.yml`.
- Do not add a new grading strategy to `lms_quiz` without adding the corresponding `match` arm in `GradingStrategyFactory` and the config entity in `lms_question/config/install/`.
- Do not implement `Plugin\PaymentGatewayInterface` without the `@PaymentGateway` annotation — the plugin manager will not discover the class (real bug: `lms_paypal\PayPalGateway` uses the wrong interface and has no annotation, so it is dead code).
- Do not use `lms_payment\Service\PaymentGatewayInterface` for new payment plugins — it is a dead interface implemented only by the dead-code `lms_paypal` class. Use `lms_payment\Plugin\PaymentGatewayInterface`.
- Do not queue AI items without considering partial-failure cleanup.

---

## Checklist

- [ ] New service ID follows `lms_{module}.{name}` (or `plugin.manager.{namespace}` for plugin managers).
- [ ] Service is declared in `{module}.services.yml` with typed constructor arguments.
- [ ] If extensible type: `DefaultPluginManager` + annotation + alter hook + cache bin are all present.
- [ ] If static factory: the strategy set is closed and documented as such in a comment.
- [ ] New `@PaymentGateway` plugin extends `PaymentGatewayBase` and carries the annotation.
- [ ] New `@AiProvider` plugin implements `AiProviderInterface` and carries `@AiProvider` annotation.
- [ ] Long-running AI work is queued via `lms_ai_content_generator` queue, not executed inline.
- [ ] No `\Drupal::service()` call inside a class that can receive DI.

---

## Related

[`CONV-006-naming-conventions.md`](CONV-006-naming-conventions.md) · [`CONV-008-events-cross-module.md`](CONV-008-events-cross-module.md) · [`C05/lms_payment.md`](../../C-Design/C05-modules/lms_payment.md) · [`C05/lms_ai.md`](../../C-Design/C05-modules/lms_ai.md) · [`C05/lms_quiz.md`](../../C-Design/C05-modules/lms_quiz.md)
