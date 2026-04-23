# K03 — Architecture Patterns

> Reusable patterns (and anti-patterns) discovered or enforced in EverLMS.
> Updated: 2026-04-16. Cross-reference: G01-adrs/ for formal decisions, A-Project-Information/A01-CLAUDE.md §Banned Patterns.

---

## 0. System Layer Map

```
┌─────────────────────────────────────────┐
│  Layer 9: Additional (zoom, faq, badge) │
├─────────────────────────────────────────┤
│  Layer 8: Integration (notification)    │
├─────────────────────────────────────────┤
│  Layer 7: Communication (forum, rating) │
├─────────────────────────────────────────┤
│  Layer 6: Certificate                   │
├─────────────────────────────────────────┤
│  Layer 5: Commerce (order, payment)     │
├─────────────────────────────────────────┤
│  Layer 4: Progress (progress, analytics)│
├─────────────────────────────────────────┤
│  Layer 3: Assessment (quiz, question)   │
├─────────────────────────────────────────┤
│  Layer 2: Content (course, chapter)     │
├─────────────────────────────────────────┤
│  Layer 1: Foundation (lms_base)         │
└─────────────────────────────────────────┘
```

**Rule:** Dependencies flow downward only. Layer 5 (Commerce) may use Layer 2 (Content); Layer 2 must never depend on Layer 5.

---

## Pattern 1: Strategy — Question Handler

**Category:** Behavioral
**ADR:** ADR-005
**When to use:** When a family of algorithms (grading, rendering, validation) varies per type, and new types are added without modifying existing code.
**When NOT to use:** When types are fixed and unlikely to grow; overhead of an interface + dispatch isn't worth it for 2–3 types.

**Structure:**
```
QuestionHandlerInterface
  render(Question): array
  validate(array, Question): bool
  grade(array, Question): float
  getMaxScore(Question): float

Implementations:
  SingleChoiceHandler   (#[QuestionHandler(id: 'single_choice')])
  MultipleChoiceHandler (#[QuestionHandler(id: 'multiple_choice')])
  TrueFalseHandler      (#[QuestionHandler(id: 'true_false')])
  FillBlankHandler      (#[QuestionHandler(id: 'fill_blank')])
  MatchingHandler       (#[QuestionHandler(id: 'matching')])
  OrderingHandler       (#[QuestionHandler(id: 'ordering')])
  ShortAnswerHandler    (#[QuestionHandler(id: 'short_answer')])
  EssayHandler          (#[QuestionHandler(id: 'essay')])  ← manual-grade only
```

**File:** `web/modules/custom/lms_question/src/Plugin/QuestionHandler/`
**Manager:** `lms_question.handler_manager` service (wraps Drupal Plugin Manager)

**Key rule:** Every method called from `QuizGradingService` must be declared on the interface. Adding a method only to a concrete handler class is a duck-typing violation (see K04, lesson on interface contracts).

---

## Pattern 2: Plugin Manager — Payment Gateway

**Category:** Extensibility
**ADR:** ADR-007
**When to use:** When external integrations must be swappable via configuration with no code deploy.
**When NOT to use:** When only one implementation will ever exist; plugin overhead adds complexity without benefit.

**Structure:**
```
PaymentGatewayPluginBase (abstract)
  createPayment(Order): PaymentResult
  capturePayment(Order): PaymentResult
  refundPayment(Order, amount): RefundResult
  handleWebhook(Request): void   ← FIND-001/002: stub-only as of 2026-04-16

Implementations:
  StripeGateway  (#[PaymentGateway(id: 'stripe')])
  PaypalGateway  (#[PaymentGateway(id: 'paypal')])
```

**File:** `web/modules/custom/lms_payment/src/Plugin/PaymentGateway/`
**Config key:** `lms_payment.settings → active_gateway`

**Active finding:** `handleWebhook()` is a no-op stub in both implementations. See FIND-001 (Stripe), FIND-002 (PayPal) in A10. Blocks proper order fulfillment.

---

## Pattern 3: Plugin Manager — AI Provider

**Category:** Extensibility
**ADR:** ADR-006
**When to use:** Same as Pattern 2 — provider must be swappable by config, not by code.
**When NOT to use:** Don't use if only one AI provider is needed long-term (simple service suffices).

**Structure:**
```
AiProviderPluginBase (abstract)
  generateCourseOutline(params): array
  generateQuizQuestions(topic, count): array
  generateLessonContent(outline): string
  (rate-limit contract: DEC-002 pending)

Implementations:
  OpenAiProvider  (#[AiProvider(id: 'openai')])
  GeminiProvider  (#[AiProvider(id: 'gemini')])  ← planned
```

**File:** `web/modules/custom/lms_ai/src/Plugin/AiProvider/`
**Feature flag:** `FF_FEAT_AI_COURSE_BUILDER` — must be enabled before invoking plugin.
**Finding:** No `max_tokens` cap on OpenAI calls (FIND-003). Runaway cost risk in prod.

---

## Pattern 4: Event-Driven Cross-Module Communication

**Category:** Integration / Decoupling
**ADR:** implied by ADR-008 (cascade), referenced in module design
**When to use:** When module A needs to trigger side effects in module B without a direct dependency (B must not depend on A).
**When NOT to use:** When the caller needs an immediate synchronous return value from the subscriber; events are fire-and-forget (or modify event object).

**Event bus:**
```
Event dispatched by:          Subscribed by:          Result:
CourseCompletedEvent          lms_certificate         Issue UserCertificate
CourseCompletedEvent          lms_notification        Send completion notification
EnrollmentCreatedEvent        lms_notification        Send welcome email
QuizGradedEvent               lms_notification        Send score alert
QuizGradedEvent               lms_certificate         Issue quiz-based certificate
PaymentCompletedEvent         lms_notification        Send receipt email
PaymentCompletedEvent         lms_progress            Create enrollment record
OrderStatusChangedEvent       lms_notification        Send status update
BadgeEarnedEvent              lms_notification        Send badge award notification
```

**File pattern:** Dispatchers in `{module}/src/Service/`; Subscribers in `{module}/src/EventSubscriber/`.
**Drupal class:** `Symfony\Component\EventDispatcher\EventDispatcherInterface`

---

## Pattern 5: Three-Level Progress Cascade (Service Orchestration)

**Category:** Data / Domain Logic
**ADR:** ADR-008
**When to use:** Hierarchical aggregate computation where leaf changes must roll up to root.
**When NOT to use:** Flat data; or when eventual consistency is acceptable (consider queue worker instead).

**Flow:**
```
HTTP: markLessonComplete(uid, lesson_id, course_id)
  │
  ▼
ProgressCalculator::markLessonComplete()
  ├─ 1. upsert lms_user_lesson (status = completed)
  ├─ 2. recalculate lms_user_chapter progress = completed_lessons / total_lessons
  ├─ 3. recalculate lms_user_course progress = AVG(chapter progresses)
  └─ 4. if progress = 100% → dispatch CourseCompletedEvent
```

**File:** `web/modules/custom/lms_progress/src/Service/ProgressCalculator.php`
**Service ID:** `lms_progress.calculator`

**Race condition risk (RISK-002, ADR-010):** Concurrent requests from same user can produce lost updates and duplicate `CourseCompletedEvent` (→ duplicate certificate). Mitigation: `SELECT FOR UPDATE` row lock around cascade (Option A in ADR-010; awaiting SA sign-off).

---

## Pattern 6: Entity Access Control Handler

**Category:** Security
**ADR:** ADR-001 (Drupal architecture)
**When to use:** Every custom content entity must have one. Controls view, edit, delete per role and ownership.
**When NOT to use:** Config entities use different access; plain nodes use Node access hooks.

**Structure:**
```php
class CourseAccessControlHandler extends EntityAccessControlHandler {
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    return match($operation) {
      'view'   => $entity->isPublished()
                    ? AccessResult::allowedIfHasPermission($account, 'view lms_course course')
                    : AccessResult::allowedIfHasPermission($account, 'view unpublished lms_course course'),
      'update' => $this->checkOwnerOrAdmin($entity, $account),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete lms_course course'),
      default  => AccessResult::neutral(),
    };
  }
}
```

**Rule:** Route-level access uses `_permission` or `_custom_access` in `.routing.yml`. Never rely on controller-level checks alone — defense in depth.

---

## Pattern 7: Constructor-Promoted Dependency Injection (Service Locator Anti-Pattern Avoidance)

**Category:** Architecture / Testability
**When to use:** All service classes. PHP 8.0+ constructor promotion reduces boilerplate.
**When NOT to use:** `.module` file hook callbacks (no class context; use `\Drupal::service()` only there).

```php
// DO — constructor promotion
final class CertificateService implements ContainerInjectionInterface {
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileSystem $fileSystem,
    private readonly LoggerChannelInterface $logger,
  ) {}

  public static function create(ContainerInterface $c): static {
    return new static(
      $c->get('entity_type.manager'),
      $c->get('file_system'),
      $c->get('logger.factory')->get('lms_certificate'),
    );
  }
}

// DON'T — Service Locator inside business method
public function issue(int $uid): void {
  $storage = \Drupal::entityTypeManager()->getStorage('lms_certificate'); // BANNED
}
```

---

## Pattern 8: Form API State + AJAX Rebuild

**Category:** Frontend / Drupal
**When to use:** Multi-step forms where user selection determines which fields appear next (e.g., lesson type selector, question type picker).
**When NOT to use:** Simple forms where the structure is static — AJAX adds unnecessary complexity.

```php
public function lessonTypeChange(array &$form, FormStateInterface $form_state): array {
  // Triggered by AJAX on field_type select
  $type = $form_state->getValue('field_type');
  // Conditionally show/hide field groups
  $form['video_fields']['#access'] = ($type === 'video');
  $form['scorm_fields']['#access'] = ($type === 'scorm');
  return $form['conditional_wrapper'];  // Return only the changed subtree
}
```

**Key rule:** Do NOT use `readonly` properties on form state value objects — Drupal's AJAX system mutates `$form_state` throughout the request lifecycle. Fatal error otherwise. (See K04 lesson.)

---

## Anti-Patterns

### Anti-Pattern: Webhook Stub Returning 200

**Category:** Integration / Reliability
**Source:** FIND-001 (Stripe), FIND-002 (PayPal) — A10 New Critical Findings
**Problem:** A handler that logs and immediately returns HTTP 200 without processing the payload masks real incidents. The gateway considers the webhook delivered; the LMS has silently not fulfilled the order. Monitoring shows no errors. The failure is invisible.

```php
// BAD — current state of lms_stripe
public function handleWebhook(Request $request): Response {
  \Drupal::logger('lms_stripe')->info('Webhook received.');
  return new Response('OK', 200);  // ← stub — order never fulfilled
}
```

```php
// CORRECT
public function handleWebhook(Request $request): Response {
  $payload = $request->getContent();
  $sig = $request->headers->get('Stripe-Signature');
  $this->verifySignature($payload, $sig);  // throws on invalid sig
  $event = $this->parseEvent($payload);
  $this->dispatcher->dispatch(new PaymentCompletedEvent($event));
  return new Response('OK', 200);
}
```

---

### Anti-Pattern: Order + Enrollment Without Atomic Transaction

**Category:** Data Integrity
**Source:** FIND-005 — A10 New Critical Findings
**Problem:** If order is created and payment succeeds, but the enrollment creation step fails (network error, validation), the user has paid but has no access. No rollback occurs.

```php
// BAD — non-atomic (current state)
$order = $this->orderService->create($data);       // step 1
$payment = $this->gateway->charge($order);         // step 2
$enrollment = $this->enrollmentService->enroll();  // step 3 — can fail silently
```

```php
// CORRECT — wrap all three in DB transaction
$txn = $this->database->startTransaction();
try {
  $order = $this->orderService->create($data);
  $payment = $this->gateway->charge($order);
  $this->enrollmentService->enroll($order);
  // $txn commits on scope exit
} catch (\Exception $e) {
  $txn->rollBack();
  throw $e;
}
```

---

### Anti-Pattern: `#markup` With Developer-Controlled Strings

**Category:** Security
**Source:** K04 lesson; A-Project-Information/A01-CLAUDE.md §Drupal Render Rules
**Problem:** `#markup` passes content through `Xss::filterAdmin()` — strips valid tags silently; developer-controlled strings not pre-sanitized become XSS vectors.

**Correct:** Use `#theme` + template, `#plain_text`, typed render element (`#type => 'html_tag'`), or `Markup::create()` for genuinely trusted static strings.

---

*End of K03-architecture-patterns.md*
