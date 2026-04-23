# G03 — Blockers & Impediments

> **How to add a blocker:** Add a row to Active Blockers. Set severity. If Critical → notify SA immediately (do not wait for next session). Include the unit(s) that cannot proceed.
> **How to clear a blocker:** Move the row to Resolved Blockers. Fill in Resolution and Resolved Date. Update the blocked unit's D03 spec status.
> Review active blockers at the start of every sprint and every SA session.

---

## Active Blockers

| ID | Description | Severity | Blocked Units | Raised | Owner | Status |
|----|-------------|----------|---------------|--------|-------|--------|
| BLK-001 | **ADR-009 awaiting SA sign-off.** API input validation strategy (DEC-001) unresolved. Agents cannot implement input validation on write endpoints until SA chooses Option A (per-route DTO) or Option B (global middleware). See ADR-009, A10 DEC-001. | **High** | U-057 (and all G-HARDENING endpoints that require validation) | 2026-04-16 | SA | ACTIVE — awaiting SA decision |
| BLK-002 | **ADR-010 awaiting SA sign-off.** Progress race condition strategy (DEC-003) unresolved. Progress cascade refactor and `SELECT FOR UPDATE` implementation cannot proceed until SA chooses Option A / B / C. See ADR-010, A10 DEC-003. | **High** | U-059 (progress race fix) | 2026-04-16 | SA | ACTIVE — awaiting SA decision |

---

## Blocker Severity Guide

| Level | Meaning | Required Response |
|-------|---------|------------------|
| **Critical** | Blocks all active development. Release cannot proceed. | Notify SA immediately; add to A10 active WIP as blocker. |
| **High** | Blocks one or more units / one group. Development cannot continue on those units. | Notify SA within 4 hours; attempt workaround documentation. |
| **Medium** | Blocks individual units but workaround exists. | Notify SA at next session end; document temporary workaround in D03. |

---

## Resolved Blockers

| ID | Description | Raised | Resolved | Duration | Resolution |
|----|-------------|--------|----------|----------|------------|
| — | No resolved blockers yet | — | — | — | — |

---

## Escalation Protocol

1. Agent identifies blocker → log in this file with all required columns.
2. **If Critical:** notify SA in the same session; add blocker row to A10 §Active WIP.
3. **If High:** notify SA at session end; document in A10 §Active WIP.
4. **If Medium:** notify SA at next session end; document workaround in affected D03 spec.
5. Agent documents any available workaround or partial progress while awaiting resolution.
6. SA reviews, decides, and signs off (updates relevant ADR status to ACCEPTED/REJECTED).
7. Agent updates this file: move row to Resolved Blockers; update D03 spec; update A10 §Active WIP.

---

*End of G03-blockers.md*
