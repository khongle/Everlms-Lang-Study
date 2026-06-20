# E01-test-strategy - Test Strategy

**Last updated:** 2026-06-21 · **source_state:** AS_BUILT + TARGET

## Current reality
⚠️ **Automated test coverage is ~0%** — no PHPUnit suites exist in `web/modules/custom/`. The G3 gate currently passes vacuously. Verification today is **manual** + config/DB inspection. Closing this is task [X01](../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md) (R-006 / BLK-004).

## Target test pyramid
| Level | Tool | Scope | Priority |
| ----- | ---- | ----- | -------- |
| Unit | PHPUnit | 8 quiz grading strategies; cert idempotency; gateway/AI provider routing (mocked) | P1 |
| Kernel | PHPUnit (KernelTestBase) | entity CRUD + access (course/lesson/quiz/order); progress rollup; enrollment gating matrix | P1 |
| Functional | PHPUnit (BrowserTestBase) | enroll→learn→quiz→certificate happy path; checkout; forum | P1 |
| API contract | PHPUnit/Behat | `/api/v1` auth (OTP→token), protected routes, web/mobile parity | P1 |
| E2E | Playwright (optional) | catalog→checkout→player UI flows | P2 |
| Accessibility | axe / manual | WCAG 2.1 AA on key pages | P2 |

## What tests assert (intent → observed)
- Editorial-usability smoke: editing an entity field changes the rendered page (per C11 visible-content inventory).
- Access matrix: each role reaches only permitted routes (C02).
- Idempotency: certificate issuance, webhook handling (once added).

## Definitions
- Test defs live in [E03](../E03-test-definitions/); results by date/build in [E04](../E04-test-results/).
- Coverage threshold (target): ≥60% on new/changed code; happy-path flows covered before release (CP5).

## Gate mapping
G1 Build (`composer install`/`drush cr`) → G2 Lint (`phpcs Drupal,DrupalPractice`) → G3 Tests (the pyramid above) → G4 Adversarial (7 categories). See [A06 gates](../../../00-Guidelines/A06-validation-and-adequacy-gates.md).
