# E03-test-definitions - Test Definitions

Descriptive-slug test definitions (the WHAT to assert), referenced by [E05 traceability](../E05-traceability/_index.md). Most are **TARGET** (suites not yet implemented — R-006/X01).

| Definition | Target | Status |
| ---------- | ------ | ------ |
| [gating-matrix](gating-matrix.md) | enrollment/membership content gating across roles | TARGET |
| [grading-per-strategy](grading-per-strategy.md) | each of 8 quiz grading strategies | TARGET |
| issuance-idempotency | certificate issued once per learner-course | TARGET |
| access-matrix | each role reaches only permitted routes (C02) | TARGET |
| api-auth-parity | `/api/v1` auth + web/mobile data parity | TARGET |
| edit-smoke (editorial-usability) | editing an entity field changes the rendered page | TARGET |

> Add one file per definition as suites are built; record runs in [E04](../E04-test-results/README.md).
