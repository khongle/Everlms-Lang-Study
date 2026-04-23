# G02 — Risk Register

> **Authoritative source:** A-Project-Information/A03-project-context.md §Known Issues / Risks + §New Critical Findings.
> Last synced: 2026-04-16. Review every sprint; full review at each CP checkpoint.
> **RISK** = known architectural/security weakness with a mitigation plan.
> **FIND** = concrete code defect or missing implementation discovered by source-code review (more specific than RISK).

---

## Severity Matrix

| | Low Impact | Medium Impact | High Impact | Critical Impact |
|---|-----------|--------------|-------------|-----------------|
| **High Likelihood** | Medium | High | Critical | Critical |
| **Medium Likelihood** | Low | Medium | High | Critical |
| **Low Likelihood** | Low | Low | Medium | High |

---

## Active RISKs

| ID | Description | Category | Likelihood | Impact | Severity | Owner | Mitigation | Status | Last Reviewed |
|----|-------------|----------|-----------|--------|----------|-------|------------|--------|---------------|
| RISK-001 | API endpoint input validation gaps — type, range, ownership checks missing on write endpoints | Security | High | High | **Critical** | Dev Lead | U-057; ADR-009 (DEC-001 pending SA); per-route DTO approach recommended | OPEN | 2026-04-16 |
| RISK-002 | Progress cascade race condition — concurrent lesson-complete requests can produce lost updates and duplicate CourseCompletedEvent (→ duplicate certificate) | Reliability | Medium | High | **High** | Dev Lead | U-059; ADR-010 (DEC-003 pending SA); SELECT FOR UPDATE recommended | OPEN | 2026-04-16 |
| RISK-003 | AI generation lacks rollback / compensation — partial AI-generated content can persist on failure mid-flow | Reliability | Medium | Medium | **Medium** | Dev Lead | U-063 + state machine; DEC-002 (rate-limit contract) | OPEN | 2026-04-16 |
| RISK-004 | Payment gateway is stub-only (Stripe + PayPal) — webhook path dead; orders cannot auto-fulfill | Reliability / Commerce | High | Critical | **Critical** | Dev Lead | U-028/U-029/U-060/U-061; ADR-007; see FIND-001 and FIND-002 | OPEN | 2026-04-16 |

---

## Active FINDs (Code-Derived Defects, 2026-04-16 source-code review)

| ID | Finding | Category | Severity | Affected Units | Owner | Mitigation | Status | Last Reviewed |
|----|---------|----------|----------|---------------|-------|------------|--------|---------------|
| FIND-001 | `lms_stripe.handleWebhook()` is a stub — logs and returns 200; no signature verification; no order fulfillment. Stripe webhook path is dead. | Commerce / Security | **Critical** | U-031, U-060 | Dev Lead | Implement signature verification + event dispatch; ADR-007 | OPEN | 2026-04-16 |
| FIND-002 | `lms_paypal` gateway entirely unimplemented (TODO stubs); no webhook routing entry in `*.routing.yml`. | Commerce | **Critical** | U-031, U-061 | Dev Lead | Implement PayPal gateway; add route; ADR-007 | OPEN | 2026-04-16 |
| FIND-003 | OpenAI calls have no `max_tokens` cap — runaway cost risk in production. | Cost / Operations | **High** | U-047 (extend scope) | Dev Lead | Add `max_tokens` + per-user rate limit; update `AiProviderInterface`; ADR-006 | OPEN | 2026-04-16 |
| FIND-004 | `checkout/{course_id}` and `checkout/membership/{plan_id}` use `_access: TRUE` at routing layer — fully unauthenticated. Auth lives in controller only (not defense-in-depth). | Security | **High** | U-058 | Dev Lead | Move auth check to routing layer (`_user_is_logged_in: TRUE` + custom access); ADR-009 | OPEN | 2026-04-16 |
| FIND-005 | No atomic transaction between order creation and enrollment creation — paid user can have no course access on partial failure. | Data Integrity | **High** | U-062 (extend scope) | Dev Lead | Wrap order + enrollment in DB transaction; ADR-010 (locking model) | OPEN | 2026-04-16 |
| FIND-006 | `RatingController::toggleApprove` and `toggleFeatured` are dead code — methods exist, no routes registered. | Code Quality | **Low** | cleanup task | Dev | Remove or register routes; low priority | OPEN | 2026-04-16 |
| FIND-007 | Sitemap references `/forum/{id}` and `/admin/lms/reports/revenue` — neither route exists in `*.routing.yml`. | Functionality | **Medium** | U-043 (forum route), U-050 (revenue report) | Dev | Add routes or remove sitemap entries | OPEN | 2026-04-16 |
| FIND-008 | Gemini API key passed via URL query string — leaks through access logs and browser history. | Security | **High** | U-047 | Dev Lead | Move key to `Authorization` or `x-goog-api-key` header; rotate key after fix | OPEN | 2026-04-23 |
| FIND-009 | `PayPalGateway` class duplicated across `lms_payment` and `lms_paypal` modules — conflict during autoload; divergent behavior likely. | Code Quality / Commerce | **High** | U-029, U-061 | Dev Lead | Consolidate into `lms_paypal`; remove the `lms_payment` copy; update service registration | OPEN | 2026-04-23 |
| FIND-013 | `GET /api/lms/notifications/unread-count` responds for anonymous users — info endpoint should require authentication. | Security | **High** | U-024 (scope extend) | Dev Lead | Add `_user_is_logged_in: TRUE` to route; verify per-user scoping in controller | OPEN | 2026-04-23 |

> FIND-008/009/013 were surfaced in subsequent source-code review after 2026-04-16 but not yet reviewed for gaps in between (IDs 010-012, 014-015 reserved — do not reuse). Per `A01-document-structure.md` §Numbering Convention, skipped IDs remain reserved for future use.

---

## Open Decisions Blocking Mitigations

| Decision ID | Question | Owner | Options | Recommendation | Blocking Units | Status |
|-------------|----------|-------|---------|----------------|---------------|--------|
| DEC-001 | API input validation strategy | SA | A: per-route DTO / B: global EventListener | A (per-route DTO) — see ADR-009 | U-057 | OPEN |
| DEC-003 | Progress race condition handling | SA | A: SELECT FOR UPDATE / B: queue worker / C: optimistic lock | A for v1 — see ADR-010 | U-059 | OPEN |

*(DEC-002 resolved: AI provider uses Plugin pattern — ADR-006 ACCEPTED.)*

---

## Escalation Policy

| Severity | Response Time | Action |
|----------|-------------|--------|
| Critical (P0) | Immediate | Block release; escalate to SA/PM; add to G03 blockers |
| High (P1) | Within current sprint | Schedule mitigation unit in current or next sprint |
| Medium (P2) | Within 2 sprints | Add to backlog; triage at sprint planning |
| Low (P3) | Backlog | Best-effort; resolve before CP5 (UAT) |

---

## Closed Risks

| ID | Risk | Category | Resolution | Date Closed |
|----|------|----------|------------|-------------|
| — | — | — | — | — |

---

## Review Schedule

- **Active risks/finds:** Review every sprint retrospective
- **Full register:** Review at each checkpoint (CP3 in progress → CP4)
- **Post-incident:** Update after any H01 incident report

---

*End of G02-risk-register.md*
