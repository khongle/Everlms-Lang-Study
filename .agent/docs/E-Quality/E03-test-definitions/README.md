# E03 — Test Definitions

> One file per golden-flow test. These are **specifications** (what to test and how), not run results — see [`../E04-test-results/`](../E04-test-results/) for execution logs.
>
> Status of all 10 tests: **NOT_YET_AUTOMATED** — no PHPUnit test classes exist in the repository as of 2026-06-20.

---

## Index

| ID | File | Type | Module(s) | AC ID(s) | D03 Story | Priority | Status |
|----|------|------|-----------|----------|-----------|----------|--------|
| TEST-001 | [TEST-001-course-enrollment-flow.md](TEST-001-course-enrollment-flow.md) | Kernel + Functional | lms_course, lms_progress, lms_order | AC-004, AC-026 | [lms_course](../../D-Development/D03-stories/lms_course.md), [lms_progress](../../D-Development/D03-stories/lms_progress.md) | P0 | NOT_YET_AUTOMATED |
| TEST-002 | [TEST-002-quiz-grading-auto.md](TEST-002-quiz-grading-auto.md) | Kernel | lms_quiz, lms_question | AC-013, AC-016, AC-017 | [lms_quiz](../../D-Development/D03-stories/lms_quiz.md) | P0 | NOT_YET_AUTOMATED |
| TEST-003 | [TEST-003-progress-cascade.md](TEST-003-progress-cascade.md) | Kernel | lms_progress | AC-027, AC-028, AC-029 | [lms_progress](../../D-Development/D03-stories/lms_progress.md) | P0 | NOT_YET_AUTOMATED |
| TEST-004 | [TEST-004-payment-checkout-bank-transfer.md](TEST-004-payment-checkout-bank-transfer.md) | Kernel + Functional | lms_order, lms_payment | AC-039, AC-040 | [lms_order](../../D-Development/D03-stories/lms_order.md) | P0 | NOT_YET_AUTOMATED |
| TEST-005 | [TEST-005-certificate-auto-issuance.md](TEST-005-certificate-auto-issuance.md) | Kernel | lms_certificate, lms_quiz | AC-059, AC-060 | [lms_certificate](../../D-Development/D03-stories/lms_certificate.md) | P0 | NOT_YET_AUTOMATED |
| TEST-006 | [TEST-006-membership-access-gate.md](TEST-006-membership-access-gate.md) | Kernel + Functional | lms_membership, lms_course | AC-044, AC-045 | [lms_membership](../../D-Development/D03-stories/lms_membership.md) | P0 | NOT_YET_AUTOMATED |
| TEST-007 | [TEST-007-forum-topic-reply.md](TEST-007-forum-topic-reply.md) | Functional | lms_forum | AC-080, AC-082 | [lms_forum](../../D-Development/D03-stories/lms_forum.md) | P1 | NOT_YET_AUTOMATED |
| TEST-008 | [TEST-008-mobile-api-auth-otp.md](TEST-008-mobile-api-auth-otp.md) | Functional | lms_mobile_api | AC-112, AC-113, AC-115 | [lms_mobile_api](../../D-Development/D03-stories/lms_mobile_api.md) | P0 | NOT_YET_AUTOMATED |
| TEST-009 | [TEST-009-wishlist-toggle.md](TEST-009-wishlist-toggle.md) | Kernel | lms_wishlist | AC-050, AC-051 | [lms_wishlist](../../D-Development/D03-stories/lms_wishlist.md) | P1 | NOT_YET_AUTOMATED |
| TEST-010 | [TEST-010-ai-course-builder-queue.md](TEST-010-ai-course-builder-queue.md) | Kernel | lms_ai | AC-140, AC-141 | [lms_ai](../../D-Development/D03-stories/lms_ai.md) | P1 | NOT_YET_AUTOMATED |

---

## Platform Test Layer Recipes

EverLMS is a Drupal 11.3.5 application. Use the lightest layer that proves the behaviour.

| Layer | Boots Drupal | DB | Browser | Use for |
|---|---:|---:|---:|---|
| Unit | No | No | No | Pure PHP services/value objects with mocked deps (e.g. GradingStrategyFactory strategies) |
| Kernel | Minimal | Yes | No | Entity API, hooks, services, event subscribers, config install, API controllers |
| Functional | Full | Yes | BrowserKit | Routes, form submission, access checks, full page renders |
| Browser / Nightwatch | Full | Yes | Real browser | AJAX interactions, Stripe Elements, modal dialogs |

**Rule:** pick the lightest layer that proves the behaviour. Functional/Browser tests are not the default when Kernel can cover the risk.

### Run Commands (DDEV)

```bash
# All tests (once test classes exist)
ddev exec vendor/bin/phpunit web/modules/custom/<module>/tests/

# Single test class
ddev exec vendor/bin/phpunit web/modules/custom/lms_quiz/tests/src/Kernel/GradingStrategyTest.php

# By group annotation
ddev exec vendor/bin/phpunit --group lms_enrollment
```

### Expected File Paths

```
web/modules/custom/{module}/tests/
  src/
    Unit/
      {ClassName}Test.php     # extends \PHPUnit\Framework\TestCase
    Kernel/
      {ClassName}Test.php     # extends \Drupal\KernelTests\KernelTestBase
    Functional/
      {ClassName}Test.php     # extends \Drupal\Tests\BrowserTestBase
```

### Base Classes

| Layer | Base class | Notes |
|---|---|---|
| Unit | `\PHPUnit\Framework\TestCase` | No Drupal bootstrap |
| Kernel | `\Drupal\KernelTests\KernelTestBase` | Install modules via `$modules` + `installEntitySchema()` |
| Functional | `\Drupal\Tests\BrowserTestBase` | Full Drupal with BrowserKit client |

### Common Anti-Patterns

- Do not use `\Drupal::service()` in Kernel tests; use `$this->container->get()` instead.
- Do not create real files in Functional tests without cleaning up in `tearDown()`.
- Do not hard-code entity IDs; use entity queries after creation to retrieve them.
- Do not test Stripe PaymentIntent in Kernel tests; mock `\Stripe\PaymentIntent` via interface substitution or env-gated test.

---

## Traceability

- Test strategy: [`../E01-test-strategy.md`](../E01-test-strategy.md) (planned — not yet materialized)
- Run results: [`../E04-test-results/`](../E04-test-results/)
- Requirement → Feature → Story → Test matrix: [`../E05-traceability.md`](../E05-traceability.md) (planned — not yet materialized)
- B05 Acceptance Criteria: [`../../B-Requirements/B05-acceptance-criteria/`](../../B-Requirements/B05-acceptance-criteria/)
- C12 Workflows: [`../../C-Design/C12-workflows/`](../../C-Design/C12-workflows/)
