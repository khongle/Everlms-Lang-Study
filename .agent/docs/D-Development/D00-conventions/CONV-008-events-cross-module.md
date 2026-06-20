# CONV-008 — Events and Cross-Module Communication

> **Rule**: Cross-module communication uses three sanctioned patterns: (a) Symfony EventDispatcher for reactive decoupling, (b) Drupal entity/hook API for cross-module entity access, and (c) `RouteSubscriber` for runtime route overrides. Never call another module's internals directly; always go through a registered service, hook, or event.
> **Why**: Drupal's module system allows any module to be disabled. Hard dependencies on another module's classes create brittle coupling. Using events, hooks, and service calls with existence guards (`class_exists`, `\Drupal::moduleHandler()->moduleExists()`) keeps modules independently deployable.
> **Backed by**: project convention; Drupal 11 architecture.

---

## 1. Symfony EventDispatcher Pattern

### Dispatching an Event

Events are dispatched by the module that owns the outcome. `lms_quiz` owns quiz submission; it dispatches `QuizPassedEvent` when a quiz is passed:

```php
// src/Event/QuizPassedEvent.php (lms_quiz)
class QuizPassedEvent {
  const EVENT_NAME = 'lms_quiz.passed';

  public function __construct(
    private readonly QuizResult $quizResult,
    private readonly Quiz $quiz,
    private readonly AccountInterface $user,
  ) {}

  public function getQuizResult(): QuizResult { return $this->quizResult; }
  public function getQuiz(): Quiz { return $this->quiz; }
  public function getUser(): AccountInterface { return $this->user; }
}
```

```php
// In QuizController::submitQuiz() (lms_quiz)
if ($result_term->getName() === 'Passed') {
  $event = new QuizPassedEvent($quiz_result, $lms_quiz, $this->currentUser);
  $this->eventDispatcher->dispatch($event, QuizPassedEvent::EVENT_NAME);
}
```

No service entry is needed for the event class itself — it is a plain PHP value object.

### Subscribing to an Event

Subscribers live in the **consuming** module, not the dispatching module:

```php
// src/EventSubscriber/CertificateEventListener.php (lms_certificate)
class CertificateEventListener implements EventSubscriberInterface {
  public static function getSubscribedEvents(): array {
    // Guard: only subscribe if the event class exists (lms_quiz may be absent)
    if (!class_exists(QuizPassedEvent::class)) {
      return [];
    }
    return [QuizPassedEvent::EVENT_NAME => 'onQuizPassed'];
  }

  public function onQuizPassed(QuizPassedEvent $event): void {
    // Traverse Quiz → Lesson → Chapter → Course to find the owning course
    $quiz = $event->getQuiz();
    // ... entity traversal ...
    $this->certificateManager->checkAndIssueCertificate(
      $event->getUser(),
      $course,
      $event->getQuizResult(),
    );
  }
}
```

**Registration** (`lms_certificate.services.yml`):

```yaml
lms_certificate.event_listener:
  class: Drupal\lms_certificate\EventSubscriber\CertificateEventListener
  arguments: ['@lms_certificate.manager']
  tags:
    - { name: event_subscriber }
```

### Event Flow Map (as-built)

```
QuizController::submitQuiz()
  └─ dispatches lms_quiz.passed (QuizPassedEvent)
        ├─ CertificateEventListener::onQuizPassed()   [lms_certificate]
        │    └─ CertificateManager::checkAndIssueCertificate()
        │         └─ CertificateGenerator::generate() → mPDF → save UserCertificate
        └─ NotificationEventSubscriber::onQuizPassed() [lms_notification]
             └─ NotificationManager::dispatch('quiz_result', ...)
```

Both subscribers are conditional on `class_exists(QuizPassedEvent::class)`. If `lms_quiz` is uninstalled, neither subscriber registers and no error occurs.

---

## 2. Cross-Module Entity Access via Hooks

When one module needs to react to another module's entity lifecycle, use Drupal hooks in the **consuming module**'s `.module` file, with an existence guard:

```php
// lms_order.module

/**
 * Implements hook_entity_insert().
 */
function lms_order_entity_insert(EntityInterface $entity): void {
  if ($entity->getEntityTypeId() === 'lms_order'
    && $entity->get('field_status')->value === 'completed') {
    _lms_order_process_completed_order($entity);
  }
}

function _lms_order_process_completed_order(Order $order): void {
  // Creates lms_user_course enrollment records for each course in the order
  // Creates lms_user_membership records for membership plan items
  // Sends enrollment confirmation email
  // Creates lms_notification entity (in-app)
}
```

**Cross-module access rules:**
- Load entities via `\Drupal::entityTypeManager()->getStorage('{entity_type_id}')->load($id)` — never raw SQL unless raw SQL is the only option (analytics reports are the justified exception).
- Do not access another module's internal service directly unless it is declared public in that module's `services.yml`.
- Wrap optional cross-module calls with `\Drupal::moduleHandler()->moduleExists('lms_notification')` guards:

```php
// In ForumReplyForm::save() (lms_forum)
if (\Drupal::moduleHandler()->moduleExists('lms_notification')) {
  \Drupal::service('lms_notification.manager')
    ->dispatch('forum_reply', $topic->getOwner(), ...);
}
```

---

## 3. RouteSubscriber for Route Overrides (as-built: `lms_forum`)

When a module must replace Drupal's auto-generated route (e.g., the canonical entity route) with a custom controller, use a `RouteSubscriber`:

```php
// src/Routing/RouteSubscriber.php (lms_forum)
class RouteSubscriber extends RouteSubscriberBase {
  protected function alterRoutes(RouteCollection $collection): void {
    // Override the auto-generated canonical topic route to use our controller
    if ($route = $collection->get('entity.lms_forum_topic.canonical')) {
      $route->setDefault(
        '_controller',
        'Drupal\lms_forum\Controller\LmsForumController::viewTopic'
      );
      // Remove the default _entity_view default if present
      $route->setDefault('_entity_view', NULL);
    }
  }
}
```

**Registration** (`lms_forum.services.yml`):

```yaml
lms_forum.route_subscriber:
  class: Drupal\lms_forum\Routing\RouteSubscriber
  tags:
    - { name: event_subscriber }
```

**Why this pattern is used in `lms_forum`:** The auto-generated `AdminHtmlRouteProvider` creates a canonical route for `lms_forum_topic` at `/forum/{lms_forum_topic}` that renders the entity in a view mode. The custom controller `LmsForumController::viewTopic` performs additional work (increment view count, load replies with access checks, build the reply form) that cannot be expressed as a view mode. Overriding at the route level keeps the entity links (`links.canonical`) pointing to the correct URL while delegating rendering to the custom controller.

**Important:** The legacy `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — it is dead code. The `RouteSubscriber` ensures it is never reached. It must be removed as part of bug cleanup (tracked as an E02 issue).

---

## 4. Conditional Subscriber Registration Pattern

When a subscriber depends on a class from another module, guard the subscription at `getSubscribedEvents()` time, not at runtime:

```php
// CORRECT
public static function getSubscribedEvents(): array {
  if (!class_exists(QuizPassedEvent::class)) {
    return [];   // subscriber silently does nothing if lms_quiz is absent
  }
  return [QuizPassedEvent::EVENT_NAME => 'onQuizPassed'];
}

// WRONG — the class reference at annotation/method level causes a fatal error
// if lms_quiz is not installed
use Drupal\lms_quiz\Event\QuizPassedEvent; // ← fatal if module absent
```

This pattern is used by both `CertificateEventListener` and `NotificationEventSubscriber`.

---

## 5. Cross-Module Entity Traversal Pattern

Several modules navigate entity relationships to find a parent entity (e.g., Quiz → Lesson → Chapter → Course for certificate issuance). The canonical traversal in `CertificateEventListener::onQuizPassed()`:

```php
// Find the Course that owns this Quiz (traverses: Quiz → Lesson → Chapter → Course)
$quiz = $event->getQuiz();
$lessons = $entity_type_manager
  ->getStorage('lms_lesson')
  ->loadByProperties(['field_quiz' => $quiz->id()]);

foreach ($lessons as $lesson) {
  $chapters = $entity_type_manager
    ->getStorage('lms_chapter')
    ->loadByProperties(['field_lessons' => $lesson->id()]);
  foreach ($chapters as $chapter) {
    $courses = $entity_type_manager
      ->getStorage('lms_course')
      ->loadByProperties(['field_chapters' => $chapter->id()]);
    if (!empty($courses)) {
      $course = reset($courses);
      break 2;
    }
  }
}
```

This traversal is O(depth) entity queries. It is acceptable for certificate issuance (rare event). For performance-sensitive paths, denormalize the parent reference (e.g., store `field_course` directly on the lesson entity) instead of traversing.

---

## Don't

- Do not call another module's class statically (`SomeOtherModule\SomeClass::method()`) — always go through a registered service or hook.
- Do not subscribe to events without a `class_exists()` guard when the event class lives in an optional module.
- Do not override routes by editing the route definition in YAML — use `RouteSubscriber` so the override is declared explicitly.
- Do not implement cross-module entity access via raw SQL when entity API queries work — raw SQL bypasses access control.
- Do not dispatch an event from within another event subscriber in a synchronous chain — use the queue for async continuation.
- Do not leave debug `throw new \Exception("HARD DEBUG!")` statements or `messenger()->addMessage("FRESH CONTROLLER")` calls in controllers (`ForumController` and `LmsForumController` both have these — tracked as bugs).

---

## Checklist

- [ ] New event class defines `EVENT_NAME` constant; constructor stores immutable state; getters return typed values.
- [ ] New event subscriber tagged `event_subscriber` in `services.yml`.
- [ ] Subscriber's `getSubscribedEvents()` guards with `class_exists()` when event class is in an optional module.
- [ ] Cross-module hook calls (`_lms_order_process_completed_order`, `lms_notification.manager::dispatch`) wrapped in module-existence guards.
- [ ] Route overrides implemented as `RouteSubscriber extends RouteSubscriberBase`, not as YAML edits.
- [ ] Entity traversal uses entity API (`loadByProperties`), not raw SQL.
- [ ] No debug `throw new \Exception` or `addMessage("DEBUG")` in any controller.

---

## Related

[`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) · [`C05/lms_quiz.md`](../../C-Design/C05-modules/lms_quiz.md) · [`C05/lms_certificate.md`](../../C-Design/C05-modules/lms_certificate.md) · [`C05/lms_forum.md`](../../C-Design/C05-modules/lms_forum.md) · [`C05/lms_notification.md`](../../C-Design/C05-modules/lms_notification.md) · [`C05/lms_order.md`](../../C-Design/C05-modules/lms_order.md)
