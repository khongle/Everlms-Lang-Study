# E05 — Traceability Matrix

> REQ → FEAT → UNIT → TEST chain.

---

## EPIC-A: Content Lifecycle

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-001 | Course CRUD | U-005, U-008 | AC-A01 | Kernel(Course), E2E-011 | SCAFFOLD |
| BR-001 | Chapter CRUD | U-006 | AC-A06 | Kernel(Chapter) | SCAFFOLD |
| BR-001 | Lesson CRUD (7 types) | U-007 | AC-A03 | Kernel(Lesson) | SCAFFOLD |
| BR-001 | Catalog + search | U-009 | AC-A05 | E2E-001 | SCAFFOLD |
| BR-001 | Category management | U-010 | AC-A04 | E2E-019 | SCAFFOLD |
| BR-001 | Course detail | U-011 | AC-A02 | E2E-001 | SCAFFOLD |
| BR-001 | Homepage | U-012 | — | E2E-001 | SCAFFOLD |

## EPIC-B: Assessment & Grading

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-003 | Quiz entity | U-013 | AC-B03 | Kernel(Quiz) | SCAFFOLD |
| BR-003 | Question (8 bundles) | U-014, U-015 | AC-B01 | Unit(8 handlers) | SCAFFOLD |
| BR-003 | Grading engine | U-016, U-020 | AC-B01, AC-B02 | Unit(GradingStrategy) | SCAFFOLD |
| BR-003 | Quiz submit + result | U-017, U-018, U-019 | AC-B04, AC-B05 | E2E-004 | SCAFFOLD |

## EPIC-C: Learning Progress

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-004 | Progress cascade | U-021, U-022 | AC-C01, AC-C02 | Unit(ProgressManager) | SCAFFOLD |
| BR-004 | Dashboard | U-023 | AC-C04 | E2E-007 | SCAFFOLD |
| BR-004 | Continue learning | U-024 | AC-C03 | E2E-006 | SCAFFOLD |

## EPIC-D: Commerce & Membership

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-005 | Checkout workflow | U-030 | AC-D01, AC-D02 | E2E-020, E2E-021 | GAP: SPEC |
| BR-006 | Webhook handler | U-031 | AC-D03 | Unit(WebhookHandler) | GAP: SPEC |
| BR-005 | Coupon system | U-032 | AC-D04 | E2E-022 | GAP: SPEC |
| BR-005 | Membership lifecycle | U-033 | AC-D05, AC-D06 | Unit(MembershipManager) | GAP: SPEC |

## EPIC-E: Certificate

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-007 | Auto issuance | U-036 | AC-E01 | Unit(CertificateIssuer), E2E-005 | GAP: SPEC |
| BR-007 | PDF generation | U-037 | AC-E03 | Unit(PdfGenerator) | GAP: SPEC |
| BR-007 | Public verify | U-038 | AC-E02 | E2E-010 | GAP: SPEC |

## EPIC-F: Communication

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-008 | Notification dispatch | U-042 | AC-F02 | Unit(Dispatcher) | GAP: SPEC |
| BR-009 | Forum UI | U-043 | AC-F01, AC-F03 | E2E(Forum) | GAP: SPEC |
| BR-009 | Rating | U-041 | AC-F04 | E2E-009 | SCAFFOLD |

## EPIC-G: Analytics

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-010 | Analytics dashboards | U-050 | AC-G01, AC-G02, AC-G03 | E2E-018 | GAP: SPEC |

## EPIC-H: AI & Integrations

| Requirement | Feature | Unit(s) | AC | Test Case(s) | Status |
|------------|---------|---------|-----|-------------|--------|
| BR-011 | AI provider plugin | U-047 | AC-H01 | Unit(ProviderSwap) | GAP: SPEC |
| BR-011 | AI course builder | U-048 | AC-H02, AC-H04 | Unit(StateMachine) | GAP: SPEC |
| BR-012 | Zoom integration | U-049 | AC-H03 | Integration(Zoom) | GAP: SPEC |

## Coverage Summary

- **Must features:** 7/15 with scaffold traceability, 8/15 GAP (SPEC status)
- **Should features:** 3/5 scaffold, 2/5 GAP
- **Could features:** 0/5 started
- **Gaps:** Commerce, Certificate, Communication, Analytics, AI integration units

---

*End of E05-traceability.md*
