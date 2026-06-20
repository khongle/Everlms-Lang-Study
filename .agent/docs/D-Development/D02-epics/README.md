# D02 — Epic Index

**Layer:** D-Development / D02-epics  
**Purpose:** Master index of all EverLMS delivery Epics. Each Epic groups related Stories that ship together to close a CP4 checkpoint. An Epic is a scheduling grouping — it does NOT define module ownership (see [C05-modules](../../C-Design/C05-modules/) for that).

**Vocabulary:** Epic > Story > Task. Never "Group" or "Unit" — those terms belonged to a prior draft and are retired.

**Naming convention:** `E-{NN}-{kebab-slug}.md` (e.g. `E-07-communication.md`).

---

## Epic Execution Order

Epics have hard sequential gates where noted. The diagram below shows the dependency chain:

```
E-01 (Foundation) ─────────────────────────────────────────┐
                   └─► E-02 (Content) ──────────────────────┤
                                        └─► E-03 (Assessment)─┤
                                            E-04 (Progress)   ├─► E-07 (Communication)
                                            E-05 (Commerce)   ├─► E-08 (Integration & AI)
                                            E-06 (Certificate) ├─► E-09 (Additional)
                                                               │
                                        All E-01..E-09 ───────┤
                                                               └─► E-10 (Hardening) ──► E-11 (UI-Polish)
```

**Key gates:**
- E-10 Hardening MUST be fully DONE before E-11 UI-Polish begins (enforced by template CP4 criteria).
- E-11 S-11-07 (Checkout UI) requires E-05 Commerce Stories to be DONE.
- E-11 S-11-09 (Forum UI) requires E-07 S-07-05 (forum cleanup) to be DONE.

---

## All Epics

| Epic ID | Title | Mapped from Draft | Status | Modules | Duration |
|---------|-------|-------------------|--------|---------|----------|
| [E-01](E-01-foundation.md) | Foundation & Theme | G-FOUNDATION | COMPLETE | lms_base, lms_content_tools, open_switcher, lms_theme, lms_classic | 1 week |
| [E-02](E-02-content.md) | Content | G-CONTENT | COMPLETE | lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq | 1 week |
| [E-03](E-03-assessment.md) | Assessment | G-ASSESSMENT | IN_PROGRESS | lms_quiz, lms_question | 1 week |
| E-04 | Progress & Portal | G-PROGRESS | *(not yet materialized)* | lms_progress, lms_note | 1 week |
| E-05 | Commerce | G-COMMERCE | *(not yet materialized)* | lms_order, lms_membership, lms_payment, lms_stripe, lms_paypal, lms_wishlist, lms_bundle | 2 weeks |
| E-06 | Certificate & Badge | G-CERTIFICATE | *(not yet materialized)* | lms_certificate, lms_badge | 1 week |
| [E-07](E-07-communication.md) | Communication | G-COMMUNICATION | IN_PROGRESS | lms_notification, lms_forum, lms_rating | 1 week |
| [E-08](E-08-integration-ai.md) | Integration & AI | G-INTEGRATION | IN_PROGRESS | lms_ai, lms_zoom, lms_analytics, lms_mobile_api | 2 weeks |
| [E-09](E-09-additional.md) | Additional Features | G-ADDITIONAL | IN_PROGRESS | lms_wishlist, lms_bundle, lms_note, lms_badge, lms_faq, lms_blog | 1 week |
| [E-10](E-10-hardening.md) | Quality & Hardening | G-HARDENING | NOT_STARTED (PLANNED) | all modules (cross-cutting) | 3 weeks |
| [E-11](E-11-ui-polish.md) | UI/UX Polish | G-UI-POLISH | NOT_STARTED (PLANNED) | lms_theme, lms_classic + all page controllers | 2 weeks |

**Total estimated duration:** ~14 weeks (sequential; parallel execution of E-01..E-09 possible where dependencies allow)

---

## Status Legend

| Status | Meaning |
|--------|---------|
| COMPLETE | All Stories DONE; CP4 signed off |
| IN_PROGRESS | At least one Story started or code as-built; not all Stories DONE |
| NOT_STARTED | No Stories started; all are target-state or planned |
| PLANNED | Deliberately deferred; design decisions outstanding before start |

---

## Epic-to-Module Coverage Matrix

| Module | Primary Epic | Notes |
|--------|-------------|-------|
| lms_base | E-01 | Foundation service |
| lms_content_tools | E-01 | Seeder/bulk-clean |
| open_switcher | E-01 | Impersonation utility (not EverLMS-branded) |
| lms_theme, lms_classic | E-01, E-11 | Scaffolded in E-01; polished in E-11 |
| lms_course, lms_chapter, lms_lesson | E-02 | Core content hierarchy |
| lms_blog, lms_faq | E-02, E-09 | E-02: entities; E-09: dual-track blog UX |
| lms_quiz, lms_question | E-03 | Assessment engine + 8 question types |
| lms_progress | E-04 | Enrollment + lesson progress |
| lms_note | E-04, E-09 | E-04: entity; E-09: video-timestamp feature |
| lms_order, lms_membership | E-05 | Commerce |
| lms_payment, lms_stripe, lms_paypal | E-05 | Payment gateways (PayPal = dead code, see risk) |
| lms_wishlist, lms_bundle | E-05, E-09 | E-05: checkout integration; E-09: entity CRUD |
| lms_certificate | E-06 | PDF issuance + verification |
| lms_badge | E-06, E-09 | E-06: scaffold; E-09: S-09-05 award logic |
| lms_notification | E-07 | In-app complete; email/push stubs |
| lms_forum | E-07 | Entities + CRUD as-built; debug cleanup pending |
| lms_rating | E-07 | RatingForm as-built; 3 missing routes (bug) |
| lms_ai | E-08 | Plugin system + course builder |
| lms_zoom | E-08 | Zoom API + Web SDK |
| lms_analytics | E-08 | Internal reports + CSV export |
| lms_mobile_api | E-08 | Flutter backend `/api/v1/` |

---

## Cross-References

- Module specs: [C05-modules/](../../C-Design/C05-modules/)
- Entities: [C04-entities/](../../C-Design/C04-entities/)
- Stories: [D03-stories/](../D03-stories/)
- Issues/risks: [E02-issues/](../../E-Quality/E02-issues/)
- Risk register: `G02-risk-register.md` (planned, layer G — not yet materialized)
