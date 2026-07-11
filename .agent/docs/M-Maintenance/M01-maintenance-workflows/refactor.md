# M01 - REFACTOR workflow

Behaviour-preserving change (e.g. dedupe `lms_paypal` R-004, secrets→Key R-001). <!-- credential-sweep:allow: risk mention R-001, không phải secret -->
1. Ensure tests exist first (or add characterization tests) — critical given low coverage (R-006).
2. Impact analysis ([E09](../../E-Quality/E09-impact-analysis/README.md)); CR if cross-scope.
3. Refactor; gates G1→G4 (G4 ARCHITECTURE/MAINTAINABILITY focus); no behaviour change.
4. Update [D07 code-map](../../D-Development/D07-code-map/README.md), [K03 patterns](../../K-Knowledge/K03-architecture-patterns/_index.md), tech-debt ([H04](../../H-Operations/H04-tech-debt/_index.md)).
