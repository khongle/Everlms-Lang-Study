---
solution_id: SOL-003
name: Event-Driven Side Effects via Symfony EventDispatcher
applies_to: "any cross-module reaction where Module A produces an outcome that Module B must act on without creating a hard code dependency from A to B"
derived_from_issue: «no E02 issue yet — pattern extracted from as-built lms_quiz + lms_certificate code»
owning_story: lms_certificate
lesson: K04-event-subscriber-for-cross-module-reactions
status: ACTIVE
---

# E08 — Solution: Event-Driven Side Effects

> One-line purpose: documents how `lms_quiz` dispatches `QuizPassedEvent` and `lms_certificate` reacts via `CertificateEventListener`, achieving cross-module coordination with no direct dependency from quiz to certificate.

---

## Problem

When a learner passes a quiz, several downstream side effects must occur:

- A **certificate** must be issued (`lms_certificate`).
- (Planned) A **badge** could be awarded (`lms_badge`).
- (Planned) A **notification** could be sent (`lms_notification`).
- (Planned) **Analytics** events could be recorded (`lms_analytics`).

The naive approach — calling certificate service methods directly from `QuizController::submitQuiz()` — creates hard upward dependencies:

```
lms_quiz → lms_certificate → lms_quiz   (circular)
lms_quiz → lms_badge                    (tight coupling; lms_badge is a stub)
lms_quiz → lms_notification             (more tight coupling)
```

This violates the module dependency hierarchy and makes `lms_quiz` dependent on an ever-growing list of reaction modules.

---

## Solution

`lms_quiz` defines a Symfony event and dispatches it. Reaction modules subscribe independently.

### Event definition (`lms_quiz`)

```php
// web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php
class QuizPassedEvent {
    const EVENT_NAME = 'lms_quiz.passed';

    public function __construct(
        private QuizResult $quizResult,
        private Quiz $quiz,
        private AccountInterface $user,
    ) {}

    public function getQuizResult(): QuizResult { return $this->quizResult; }
    public function getQuiz(): Quiz             { return $this->quiz; }
    public function getUser(): AccountInterface { return $this->user; }
}
```

### Dispatch point (`lms_quiz`)

Inside `QuizController::submitQuiz()`, after the `lms_quiz_result` entity is saved and the result term is confirmed as "Passed":

```php
// web/modules/custom/lms_quiz/src/Controller/QuizController.php
\Drupal::service('event_dispatcher')->dispatch(
    new QuizPassedEvent($quiz_result, $lms_quiz, $this->currentUser),
    QuizPassedEvent::EVENT_NAME,
);
```

Identical dispatch also occurs in `MobileLearningController::submitQuiz()` in `lms_mobile_api`:

```php
// web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php
if (class_exists(QuizPassedEvent::class)) {
    \Drupal::service('event_dispatcher')->dispatch(
        new QuizPassedEvent($quiz_result_entity, $quiz, $account),
        QuizPassedEvent::EVENT_NAME,
    );
}
```

Note the `class_exists()` guard — this makes `lms_mobile_api` resilient if `lms_quiz` were ever disabled, though `lms_quiz` is not declared in `lms_mobile_api.info.yml` dependencies (as-built defect).

### Subscriber (`lms_certificate`)

```yaml
# web/modules/custom/lms_certificate/lms_certificate.services.yml
lms_certificate.event_listener:
  class: Drupal\lms_certificate\EventSubscriber\CertificateEventListener
  arguments: ['@lms_certificate.manager']
  tags:
    - { name: event_subscriber }
```

```php
// web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php
class CertificateEventListener implements EventSubscriberInterface {

    public static function getSubscribedEvents(): array {
        // Conditional registration: only if QuizPassedEvent class exists
        if (!class_exists(QuizPassedEvent::class)) {
            return [];
        }
        return [QuizPassedEvent::EVENT_NAME => 'onQuizPassed'];
    }

    public function onQuizPassed(QuizPassedEvent $event): void {
        $quiz  = $event->getQuiz();
        $user  = $event->getUser();
        $result = $event->getQuizResult();

        // Traverse Quiz → Lesson → Chapter → Course to find the owning course
        $lesson  = $quiz->get('field_quiz')->referencedEntities()[0] ?? null;
        $chapter = $lesson?->get('field_lessons')->... // reverse lookup via entity query
        $course  = $chapter?->get('field_chapters')->... // reverse lookup via entity query

        if ($course) {
            $this->certificateManager->checkAndIssueCertificate($user, $course, $result);
        }
    }
}
```

`CertificateManager::checkAndIssueCertificate()` guards against double-issuance (queries for existing `lms_user_certificate` for user + course), selects the best matching template (course-specific via `field_certificate_template`, or first active template as fallback), generates a unique 10-char uppercase code, calls `CertificateGenerator::generate()` (mPDF), and saves the `lms_user_certificate` entity.

### Dependency graph (as-built)

```
lms_quiz ──dispatch──► lms_quiz.passed (event)
                              │
              ┌───────────────┘
              │
lms_certificate.event_listener::onQuizPassed()
              │
              └─► CertificateManager::checkAndIssueCertificate()
                        └─► CertificateGenerator::generate()  (mPDF)
                        └─► lms_user_certificate entity save
```

`lms_quiz` has **zero import of any `lms_certificate` class**. The dependency arrow is one-way: `lms_certificate` depends on `lms_quiz` (declared in `lms_certificate.info.yml` dependencies).

---

## When to apply

| Condition | Use event-driven side effects |
|-----------|-------------------------------|
| Module A produces an outcome; Module B (or C, D…) must react, but A must not know about B | Yes — events decouple the producer |
| Side effects are **optional** or may be added/removed by enabling/disabling modules | Yes — subscribers register themselves |
| Side effects are **fire-and-forget** (no return value needed by the producer) | Yes — Symfony dispatch has no return |
| Multiple modules may react to the same event | Yes — multiple subscribers on one event name |
| Reaction must happen in the **same request** (synchronous) | Yes — Symfony EventDispatcher is synchronous |

---

## When NOT to apply

| Situation | Use instead |
|-----------|-------------|
| The producer **needs a return value** from the reactor | Drupal hook system (`hook_*_alter`) or service interface |
| Reaction is expensive and can be deferred | Drupal Queue API — enqueue a `lms_*` queue item; see `lms_ai` queue worker pattern |
| All reactions always come from the same module (no decoupling needed) | Direct service call — simpler, more traceable |
| Reaction must be **transactional** with the producing action | Avoid events; use a service that wraps both in a DB transaction |

---

## Trade-offs

- **Cost:** Symfony EventDispatcher adds minimal overhead (subscriber list lookup on dispatch). `CertificateEventListener` performs entity traversal (Quiz→Lesson→Chapter→Course via entity queries) on each quiz-pass event. This is a synchronous chain of at least 3 entity queries. If the traversal is slow (missing indexes), the quiz-submit request feels slow.
- **Risk:** Event subscribers can fail silently. If `CertificateGenerator::generate()` throws an uncaught exception inside `onQuizPassed()`, Drupal logs the error but the quiz submission still completes — the learner passes but gets no certificate. This is the correct UX priority but certificate issuance becomes **eventually inconsistent** unless monitored.
- **Operational impact:** There is no retry mechanism if certificate issuance fails. If mPDF is missing (`mpdf/mpdf` Composer package absent), `CertificateGenerator::generate()` returns `NULL` gracefully and logs the error — no certificate is saved. Check `/admin/reports/dblog` for `lms_certificate` log channel entries after quiz events.
- **Alternatives considered:** A Drupal hook (`hook_lms_quiz_passed()`) was considered but rejected because it requires all reactor modules to implement a custom hook defined in `lms_quiz.api.php`, which is less self-documenting than a typed event class. Tagged services were also considered but do not integrate with the Symfony dispatcher pattern already in place.

---

## Extension — adding a new reactor

To have `lms_badge` (or `lms_notification`) react to quiz passes:

1. In `lms_badge.services.yml`:
   ```yaml
   lms_badge.quiz_pass_subscriber:
     class: Drupal\lms_badge\EventSubscriber\BadgeQuizPassSubscriber
     arguments: ['@lms_badge.award_service']
     tags:
       - { name: event_subscriber }
   ```
2. Implement `getSubscribedEvents()` returning `[QuizPassedEvent::EVENT_NAME => 'onQuizPassed']`.
3. Add `lms_quiz` to `lms_badge.info.yml` dependencies.
4. No changes to `lms_quiz` required.

---

## References

- D03 Story (quiz): [`D-Development/D03-stories/lms_quiz.md`](../../D-Development/D03-stories/lms_quiz.md)
- D03 Story (certificate): [`D-Development/D03-stories/lms_certificate.md`](../../D-Development/D03-stories/lms_certificate.md)
- C05 Module spec (quiz): [`C-Design/C05-modules/lms_quiz.md`](../../C-Design/C05-modules/lms_quiz.md)
- C05 Module spec (certificate): [`C-Design/C05-modules/lms_certificate.md`](../../C-Design/C05-modules/lms_certificate.md)
- Source files:
  - `web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php`
  - `web/modules/custom/lms_quiz/src/Controller/QuizController.php` — `submitQuiz()`
  - `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php` — `submitQuiz()`
  - `web/modules/custom/lms_certificate/lms_certificate.services.yml`
  - `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php`
  - `web/modules/custom/lms_certificate/src/Service/CertificateManager.php`
  - `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php`
