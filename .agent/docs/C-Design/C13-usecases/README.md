# C13 — Use Case Catalogs

**Purpose:** Structured use case catalog grounding each business workflow in real as-built behavior, entity mutations, observability hooks, and links to B05 acceptance criteria.

**Scope:** A–E layers only. Each use case maps to a specific workflow (WF-xxx), documents real code flow based on the facts base, and flags gaps as target-state or not-implemented where applicable.

---

## Folder structure

```
C13-usecases/
  WF-001-enrollment-learning/     — Course purchase and enrollment
  WF-002-quiz-grading/            — Quiz submission and automated grading
  WF-003-certificate-issuance/    — Auto-certificate on quiz pass
  WF-004-order-checkout/          — Full order & payment checkout flows
  WF-005-membership-subscription/ — Membership plan purchase and expiry
  WF-006-course-creation/         — Instructor/manager course authoring
  WF-007-user-registration/       — Web + mobile OTP registration and login
  WF-008-forum-flow/              — Forum topic creation, replies, best-answer
  WF-009-coupon-flow/             — Coupon creation and redemption (planned)
```

---

## Workflow catalog index

### WF-001 — Enrollment & Learning

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-001-enrollment-learning/](WF-001-enrollment-learning/README.md) | — | — | Folder index |
| UC-001-01 | WF-001 | happy | Paid course Stripe checkout → auto-enrollment |
| UC-001-02 | WF-001 | happy | Free course checkout → immediate enrollment |
| UC-001-03 | WF-001 | permission | Anonymous visitor → login redirect |
| UC-001-04 | WF-001 | edge | Bank transfer → pending order, manual admin approval required |

### WF-002 — Quiz Grading

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-002-quiz-grading/](WF-002-quiz-grading/README.md) | — | — | Folder index |
| UC-002-01 | WF-002 | happy | Objective quiz submission → binary grade → Passed → event dispatched |
| UC-002-02 | WF-002 | edge | Essay question → result always Pending → no certificate issued |
| UC-002-03 | WF-002 | permission | Prerequisite quiz unmet → attempt blocked on take page |

### WF-003 — Certificate Issuance

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-003-certificate-issuance/](WF-003-certificate-issuance/README.md) | — | — | Folder index |
| UC-003-01 | WF-003 | happy | Quiz pass event → course traversal → PDF certificate issued |
| UC-003-02 | WF-003 | edge | Student re-passes quiz → duplicate certificate prevented |
| UC-003-03 | WF-003 | edge | No course-specific template → fallback to first active template |
| UC-003-04 | WF-003 | permission | Admin revokes certificate → verification shows revoked |

### WF-004 — Order & Checkout

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-004-order-checkout/](WF-004-order-checkout/README.md) | — | — | Folder index |
| [UC-WF004-001](WF-004-order-checkout/UC-WF004-001-stripe-checkout.md) | WF-004 | happy | Student purchases a paid course via Stripe |
| [UC-WF004-002](WF-004-order-checkout/UC-WF004-002-bank-transfer-receipt.md) | WF-004 | error/edge | Student selects bank transfer; admin approves receipt |
| [UC-WF004-003](WF-004-order-checkout/UC-WF004-003-anon-checkout-redirect.md) | WF-004 | permission | Anonymous user attempts checkout and is redirected to login |

### WF-005 — Membership Subscription

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-005-membership-subscription/](WF-005-membership-subscription/README.md) | — | — | Folder index |

### WF-006 — Course Creation

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-006-course-creation/](WF-006-course-creation/README.md) | — | — | Folder index |

### WF-007 — User Registration & Login

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-007-user-registration/](WF-007-user-registration/README.md) | — | — | Folder index |
| [UC-WF007-001](WF-007-user-registration/UC-WF007-001-web-registration.md) | WF-007 | happy | Visitor registers via `/user/register`; `lms_base` auto-assigns `student` role |
| [UC-WF007-002](WF-007-user-registration/UC-WF007-002-mobile-otp-registration.md) | WF-007 | happy | Flutter app registers via OTP flow; bearer token issued on verify |
| [UC-WF007-003](WF-007-user-registration/UC-WF007-003-otp-expired-registration.md) | WF-007 | error | Mobile OTP session expires before verification; client must restart |

### WF-008 — Forum Topic & Reply Flow

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-008-forum-flow/](WF-008-forum-flow/README.md) | — | — | Folder index |
| [UC-WF008-001](WF-008-forum-flow/UC-WF008-001-create-topic-reply.md) | WF-008 | happy | Authenticated user creates topic and receives reply with in-app notification |
| [UC-WF008-002](WF-008-forum-flow/UC-WF008-002-mark-best-answer.md) | WF-008 | happy | Topic owner marks a reply as Best Answer; topic set to resolved |
| [UC-WF008-003](WF-008-forum-flow/UC-WF008-003-anonymous-access-denied.md) | WF-008 | permission | Anonymous user attempts forum topic creation — access denied; teaser mode only |

### WF-009 — Coupon Creation & Redemption

| File | ID | Type | Summary |
|------|----|------|---------|
| [WF-009-coupon-flow/](WF-009-coupon-flow/README.md) | — | — | Folder index |
| [UC-WF009-001](WF-009-coupon-flow/UC-WF009-001-coupon-creation.md) | WF-009 | happy | Admin creates percentage-discount coupon (target-state — `lms_coupon` entity not yet implemented) |
| [UC-WF009-002](WF-009-coupon-flow/UC-WF009-002-coupon-redemption.md) | WF-009 | happy | Student applies coupon at checkout; discount applied to order (target-state) |
| [UC-WF009-003](WF-009-coupon-flow/UC-WF009-003-invalid-coupon.md) | WF-009 | error | Student enters invalid, expired, or over-limit coupon; validation errors returned (target-state; scaffold fields as-built) |

---

## Use case shape (per file)

Each use case file contains:
- **Frontmatter:** `usecase_id`, `workflow`, `type` (happy | error | edge | permission)
- **Actors** — table of participants
- **Preconditions** — system state required before trigger
- **Trigger** — what initiates the flow
- **Main flow** — numbered Given/When/Then steps
- **Entities mutated** — table of entity + field + change
- **Observability hooks** — logs, events, and signals
- **Acceptance link** — B05 AC id(s) and planned E03 TEST id(s)
- **Notes** — bugs, risks, known gaps

---

## Cross-references

| Layer | Document |
|-------|----------|
| Business workflows | [`../../B-Requirements/B04-business-workflows/`](../../B-Requirements/B04-business-workflows/) |
| Acceptance criteria | [`../../B-Requirements/B05-acceptance-criteria/`](../../B-Requirements/B05-acceptance-criteria/) |
| Entity definitions | [`../C04-entities/`](../C04-entities/) |
| Module architecture | [`../C05-modules/`](../C05-modules/) |
| Test definitions | see E03 |
| Visual workflows | see C12 |
