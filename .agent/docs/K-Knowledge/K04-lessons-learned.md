# K04 — Lessons Learned

> Distilled patterns from retrospectives (E07), incidents (H01), impact analyses (E09), and TASK audits.
> Single file (not per-incident). Append a lesson after 3+ similar incidents or a major postmortem insight.
> Per-incident postmortems belong in `E-Quality/E07-retrospectives/`, not here.
> Most recent first.

---

## Lessons

### Lesson: Webhook stubs that return 200 mask incidents — dead handlers look healthy

**Date:** 2026-04-16
**Sources:** FIND-001 (Stripe webhook stub), FIND-002 (PayPal stub) — A10 New Critical Findings
**Category:** Architecture / Reliability

**What Happened:**
Both `lms_stripe.handleWebhook()` and the PayPal gateway equivalent were scaffolded as stubs: log the incoming request, return HTTP 200. No signature verification, no event parsing, no order fulfillment. Stripe and PayPal both consider a 200 response as "delivered successfully." The LMS silently never fulfilled paid orders that came through webhook paths. There were no error logs, no alerts, and no monitoring signal.

**What We Learned:**
A stub handler that returns 200 is worse than a 500 error. A 500 would surface in monitoring and trigger investigation. A 200 stub destroys the audit trail: the gateway records delivery, the LMS has no record of fulfillment, and the gap between "paid" and "enrolled" is invisible until a user complains. Any webhook endpoint that is not fully implemented must either: (a) return 501 Not Implemented explicitly, or (b) be blocked by a feature flag so no requests reach it.

**Action Taken:**
- FIND-001 and FIND-002 added to A10 risk register (Critical).
- U-060 (Stripe webhook implementation) and U-061 (PayPal) added to G-HARDENING batch.
- Anti-pattern documented in K03 §Anti-Patterns: "Webhook Stub Returning 200."
- Development checklist: any `handleWebhook()` stub must include `throw new \RuntimeException('Not implemented')` or return 501 until implementation is complete.

**References:**
- A-Project-Information/A03-project-context.md FIND-001, FIND-002
- G02-risk-register.md RISK-004
- K03-architecture-patterns.md §Anti-Pattern: Webhook Stub Returning 200
- ADR-007 (payment gateway interface)

---

### Lesson: Order creation and enrollment must be wrapped in an atomic transaction from day 1

**Date:** 2026-04-16
**Sources:** FIND-005 — A10 New Critical Findings (order+enrollment non-atomic)
**Category:** Architecture / Data Integrity

**What Happened:**
The order creation flow called three sequential steps: create Order entity → charge payment gateway → create enrollment (UserCourse). These steps were not wrapped in a DB transaction. A failure at step 3 (enrollment creation) left the user in a "paid but not enrolled" state with no automated rollback. The payment had already been captured; the user had no access to the course they paid for.

**What We Learned:**
Any flow where money changes hands and access must be granted atomically must use a DB transaction (or a compensating transaction / saga pattern). The golden rule: if step N failing means a user has paid and received nothing, steps 1..N must be atomic. "We'll add transactions later" is not viable — partial states that reach production require manual data repair and damage user trust.

**Action Taken:**
- FIND-005 added to A10.
- U-062 (order+enrollment atomic transaction) added to G-HARDENING; extends scope of U-028 (order flow).
- Anti-pattern documented in K03 §Anti-Patterns: "Order + Enrollment Without Atomic Transaction."
- D03 unit-spec template updated: "Transactional scope" is a required field for any unit that touches both commerce and progress/enrollment entities.

**References:**
- A-Project-Information/A03-project-context.md FIND-005
- K03-architecture-patterns.md §Anti-Pattern: Order + Enrollment Without Atomic Transaction
- ADR-007 (payment gateway)
- ADR-010 (progress race condition — related locking concern)

---

### Lesson: AI generation calls without `max_tokens` are an unbounded cost risk — cap before enabling in prod

**Date:** 2026-04-16
**Sources:** FIND-003 — A10 New Critical Findings (OpenAI calls without max_tokens cap)
**Category:** Architecture / Operations / Cost

**What Happened:**
OpenAI API calls in `lms_ai` were implemented without a `max_tokens` parameter. In development this is low-risk (limited traffic). In production, a single runaway prompt (large course outline, adversarial input, infinite-loop-like prompt injection) can generate a response large enough to consume significant API quota in one call. Multiplied by concurrent users or a bot, this becomes a cost incident with no circuit breaker.

**What We Learned:**
Every AI provider API call must have three guardrails before going to production: (1) `max_tokens` cap appropriate to the use case, (2) rate limiting per user or per feature-flag cohort, and (3) cost monitoring alert (e.g., alert if daily spend exceeds $X). None of these are "nice to have" — they are mandatory before the `FF_FEAT_AI_COURSE_BUILDER` flag is enabled in production. The feature flag itself is a soft guardrail, not a cost guardrail.

**Action Taken:**
- FIND-003 added to A10 (severity: High).
- U-047 scope extended to add `max_tokens` enforcement and per-user rate limit.
- `AiProviderInterface` to include a `getMaxTokens(): int` contract method (pending DEC-002 resolution on rate-limit responsibility).
- A-Project-Information/A01-CLAUDE.md: "All AI provider calls must include `max_tokens`. Missing `max_tokens` is a banned pattern."
- Runbook H02: "AI cost spike" runbook stub added to H-Operations.

**References:**
- A-Project-Information/A03-project-context.md FIND-003
- DEC-002 (A10 Open Decisions): plugin vs service+config for rate limiting
- `web/modules/custom/lms_ai/` module
- ADR-006 (AI provider plugin architecture)

---

### Lesson: Interface contracts must include all called methods — duck-typing violations silently break plugins

**Date:** 2026-04-15
**Sources:** TASK-012 audit (AI plugin audit)
**Category:** Architecture

**What Happened:**
The AI generation plugin declared an interface but the concrete implementation added methods that callers depended on but the interface did not declare. The mismatch was invisible until a different provider was swapped in and failed at runtime — no compile-time signal, no test coverage of the alternative path.

**What We Learned:**
Every method called on a plugin/service from outside the class boundary must appear in the interface contract. Duck-typing compliance is not sufficient in PHP 8 strict mode; only explicit interface membership prevents drift. If a caller needs `generateFromTemplate()`, that method must be on the interface, not added ad-hoc to the concrete class.

**Action Taken:**
- AI plugin interface reviewed; all called methods added to `AiProviderInterface`.
- Coding convention added in K02: "Interfaces must enumerate all public methods that external callers invoke — no side-channel duck-typing."
- A-Project-Information/A01-CLAUDE.md ban list updated: "Do not add public methods to a plugin class without mirroring in the interface."

**References:**
- TASK-012 audit notes
- K02-coding-conventions.md §Plugin Rules
- A-Project-Information/A01-CLAUDE.md §Banned Patterns

---

### Lesson: API write endpoints must have schema validation from day 1 — retrofitting is expensive and risky

**Date:** 2026-04-15
**Sources:** RISK-001 (A10 Known Issues); TASK-012 audit
**Category:** Architecture / Security

**What Happened:**
Multiple API write endpoints were scaffolded without input validation (no schema, no DTO validation, no sanitization). Discovered during audit (TASK-012). Retrofitting validation to live endpoints requires regression testing of every consumer and risks silent behavior changes.

**What We Learned:**
Input validation is a structural gate, not a polish step. Every POST/PUT/PATCH endpoint must declare its accepted schema before implementation, not after. A missing schema means unknown inputs reach business logic — a security and data-integrity risk that compound over time.

**Action Taken:**
- RISK-001 added to A10 risk register, priority P0.
- D03 unit-spec template updated: "Schema definition" is a required field for any write endpoint unit.
- A-Project-Information/A01-CLAUDE.md: `#markup` and direct `$_POST` reads banned; all write handlers must use typed DTOs or typed form state.

**References:**
- A-Project-Information/A03-project-context.md RISK-001
- TASK-012 audit notes
- DEC-001 (A10 Open Decisions): per-route vs global middleware TBD

---

### Lesson: Cascade services touching shared rows need an explicit concurrency strategy before first write

**Date:** 2026-04-15
**Sources:** RISK-002 (A10 Known Issues); DEC-003 (A10 Open Decisions)
**Category:** Architecture / Data Integrity

**What Happened:**
The progress cascade service (Lesson % → Chapter % → Course %) updates shared aggregation rows. Under concurrent requests (two tab completions at the same time), race conditions produce stale reads overwriting fresher writes, resulting in incorrect completion percentages persisted to the DB.

**What We Learned:**
Any service that reads-then-writes a shared aggregate must declare its concurrency strategy before implementation: either optimistic locking (version column + retry), pessimistic locking (SELECT FOR UPDATE), or queue serialization. "We'll add it later" is not a strategy — the race exists from the first dual-request.

**Action Taken:**
- RISK-002 added to A10 risk register, priority High.
- DEC-003 open (SA decision pending): DB lock vs queue worker.
- D03 template: "Concurrency strategy" is a required field for any service that performs read-modify-write on a shared row.

**References:**
- A-Project-Information/A03-project-context.md RISK-002, DEC-003
- `lms_progress` module (progress cascade service)

---

### Lesson: Hardcoded provider strings in plugin systems make swapping providers a code change, not a config change

**Date:** 2026-04-15
**Sources:** TASK-012 audit (AI plugin audit)
**Category:** Architecture

**What Happened:**
The AI generation plugin contained a hardcoded provider string (`'openai'`) in the plugin factory, bypassing the plugin manager discovery mechanism. Changing providers required a code deploy, not a configuration change. Additionally, the provider was not rate-limited, making runaway generation possible.

**What We Learned:**
Plugin selection must be driven by configuration (e.g., a `config.yml` key) resolved at runtime by the plugin manager. Rate limiting is a plugin-level concern and must be part of the interface contract (or a decorator), not an afterthought. Any plugin system without rate limit enforcement exposes an unbounded resource risk.

**Action Taken:**
- AI plugin refactored to read provider from config; plugin manager used for discovery.
- `AiProviderInterface` to include rate-limit metadata contract (pending DEC-002 decision).
- A-Project-Information/A01-CLAUDE.md: banned "hardcoded service class names in factory methods."

**References:**
- TASK-012 audit notes
- DEC-002 (A10 Open Decisions): plugin vs service+config
- `lms_ai` module

---

### Lesson: `#markup` in Drupal render arrays is a bypass — use `#theme` or typed render elements

**Date:** 2026-04-15
**Sources:** K02 coding conventions review; Drupal 11 security audit
**Category:** Technical / Security

**What Happened:**
Several render arrays used `#markup` to output HTML strings directly. `#markup` passes content through `Xss::filterAdmin()` but silently strips tags that are valid in the UI context. More critically, developer-controlled strings inserted into `#markup` become XSS vectors if not pre-sanitized.

**What We Learned:**
`#markup` is an escape hatch, not a pattern. All output should use `#theme` + template, `#plain_text`, or a typed render element (`#type => 'html_tag'`). If raw HTML is genuinely required, use `Markup::create()` explicitly so the intention is visible and auditable.

**Action Taken:**
- A-Project-Information/A01-CLAUDE.md banned list: "`#markup` with developer-controlled strings."
- K02 updated: "Use `#theme` for structured output; `Markup::create()` only for static/trusted strings."
- G4 adversarial checklist: `#markup` occurrences flagged as Security concern automatically.

**References:**
- A-Project-Information/A01-CLAUDE.md §Drupal Render Rules
- K02-coding-conventions.md

---

### Lesson: Entity types without a `views_data` handler are invisible to Views — declare it or document the omission

**Date:** 2026-04-15
**Sources:** Development experience across `lms_*` modules
**Category:** Technical

**What Happened:**
Several custom content entities were implemented without a `views_data` handler. They became invisible to Drupal Views, requiring workarounds (custom queries) in reporting features that could have used Views natively. Retrofitting `views_data` after the entity is in production requires cache rebuild and config export.

**What We Learned:**
Every custom entity that will ever appear in a list view, report, or admin UI needs a `views_data` handler declared in the entity annotation (`"views_data" = "Drupal\{module}\{Entity}ViewsData"`). If Views integration is intentionally excluded, document the omission in the entity's D03 spec so the next developer does not waste time investigating.

**Action Taken:**
- Entity D03 unit-spec template: "Views integration: required / excluded (reason)" added as a field.
- K02: "Custom entities must declare `views_data` handler unless Views integration is explicitly excluded in D03."

**References:**
- D03 unit-spec template
- K02-coding-conventions.md §Entity Rules

---

### Lesson: Readonly properties on form state objects break AJAX form rebuild — use mutable state or clone

**Date:** 2026-04-15
**Sources:** Development experience; Drupal 11 PHP 8.3 compatibility notes
**Category:** Technical

**What Happened:**
PHP 8.3 readonly properties were applied to a form state value object. When Drupal's AJAX system rebuilt the form and attempted to set new state values (e.g., `$form_state->setValue()`), the readonly constraint threw a fatal error — form rebuild silently died with a 500 response.

**What We Learned:**
Drupal's form API and AJAX system mutates form state objects throughout the request lifecycle. Readonly properties are incompatible with form state value objects. Use: (a) mutable properties with explicit setters, or (b) clone + replacement pattern. PHP 8.3 readonly is safe only on truly immutable value objects that Drupal never needs to mutate.

**Action Taken:**
- A-Project-Information/A01-CLAUDE.md: "Do not use `readonly` on form state value objects or any class that Drupal's form/AJAX system mutates."
- K02: "Readonly properties: safe for DTOs and pure value objects; forbidden on form state, plugin definitions, and entity field types."

**References:**
- A-Project-Information/A01-CLAUDE.md §PHP 8.3 Compatibility
- K02-coding-conventions.md §PHP Style

---

*End of K04-lessons-learned.md*
