# D02 — Group Spec: G-INTEGRATION

**Group ID:** G-INTEGRATION
**Group Name:** Integration & AI
**Order:** 8
**Units in Scope:** U-045 – U-050
**Dependencies:** G-CONTENT, G-ASSESSMENT
**Estimated Sprints:** 1
**Status:** PENDING (2 DONE, 4 SPEC)

---

## Purpose

Tích hợp external services và AI capabilities: ZoomService, AnalyticsService, AI provider plugin architecture, AI course builder, Zoom meeting integration (full), và analytics dashboards cho admin/manager.

---

## Units

| Unit ID | Name | Status | Gate | Feature Flag |
|---------|------|--------|------|-------------|
| U-045 | ZoomService (API stub) | DONE | G4 pass | — |
| U-046 | AnalyticsService (3 queries) | DONE | G4 pass | — |
| U-047 | AI provider plugin architecture | SPEC | — | FF_FEAT_AI_PROVIDER |
| U-048 | AI course builder | SPEC | — | FF_FEAT_AI_BUILDER |
| U-049 | Zoom meeting integration | SPEC | — | FF_FEAT_ZOOM |
| U-050 | Analytics dashboards (admin/manager) | SPEC | — | — |

---

## Build Order Within Group

1. U-045, U-046 — Service stubs (DONE)
2. U-047 — AI provider plugin (no SPEC dependencies)
3. U-048 — AI course builder (depends on U-047)
4. U-049 — Zoom meeting integration (depends on U-045)
5. U-050 — Analytics dashboards (depends on U-046)

---

## Key Modules

- `lms_ai` — U-047, U-048
- `lms_zoom` — U-045, U-049
- `lms_analytics` — U-046, U-050

---

## Feature Flags

| Flag | Unit | Rollout |
|------|------|---------|
| FF_FEAT_AI_PROVIDER | U-047 | 0% → 10% → 100% |
| FF_FEAT_AI_BUILDER | U-048 | After U-047 stable |
| FF_FEAT_ZOOM | U-049 | After U-045 stable |

---

## Exit Criteria

- [x] ZoomService + AnalyticsService stubs
- [ ] AI provider plugin with interface + 3 implementations (U-047)
- [ ] Course builder using AI provider (U-048)
- [ ] Zoom meeting create/join/webhook flow (U-049)
- [ ] Analytics dashboards rendering real data (U-050)

---

## Key Risks

- **RISK-003** (Medium): AI generation thiếu rollback/compensation → U-047 must include state machine. Hardening in U-063.
- **RISK-004**: Zoom API credentials management; live credentials must not be hardcoded.
- U-047 known bugs (from TASK-012 audit): `generateText()` NOT in interface, hardcoded `createInstance('openai')`, Gemini API key in URL param — all must be fixed before SPEC→DONE.

---

*End of 08-G-INTEGRATION.md*
