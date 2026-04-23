# E01 — Test Strategy

## Testing Approach

EverLMS sử dụng 5-layer testing strategy: Unit → Kernel → Integration → E2E → Performance. Mỗi unit phải pass G1-G4 gates trước khi chuyển sang Review.

## Coverage Targets

| Test Type | Target | Tool | Frequency |
|-----------|--------|------|-----------|
| Unit tests | ≥80% coverage | PHPUnit | Every unit, every gate (G3) |
| Kernel tests | All entity types | PHPUnit + Drupal Kernel | Per entity implementation |
| E2E tests | 22 critical scenarios | Playwright | Pre-release, per group CP4 |
| Performance tests | NFR targets | k6 / custom | Pre-release (CP5) |
| Static analysis | PHPCS + PHPStan | PHPCS, PHPStan | Every commit (G2) |

## Gate Sequence

```
G1 (Build) → G2 (Lint) → G3 (Tests) → G4 (Adversarial Self-Review)
```

### Gate Definitions

| Gate | Command | Pass Criteria | Max Retries |
|------|---------|---------------|-------------|
| G1 | `ddev exec drush cr` | Exit code 0, zero errors | 3 |
| G2 | `ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/` | Exit code 0 | 3 |
| G3 | `ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/` | All pass, coverage ≥80% | 3 |
| G4 | Manual adversarial review | 6 categories examined, Critical/High fixed | 3 |

## G4 Adversarial Categories

1. **Security** — SQL injection, XSS, CSRF, auth bypass, secrets in code
2. **Error Handling** — Unhandled exceptions, silent failures, retry logic
3. **Performance** — N+1 queries, unbounded results, missing caching
4. **Edge Cases** — Null/empty, concurrency, boundary values, Unicode
5. **Architecture** — Layer violations, circular deps, god classes, \Drupal:: static calls
6. **Data Integrity** — Missing transactions, orphan records, validation gaps

## Unit Test Categories

### Question Handler Tests (8 handlers)

| Handler | Test Focus |
|---------|-----------|
| MultipleChoiceHandler | Score calculation, partial scoring |
| SingleChoiceHandler | Correct/incorrect |
| TrueFalseHandler | Boolean scoring |
| FillBlankHandler | Case-sensitive/insensitive matching |
| ShortAnswerHandler | Text matching, keyword scoring |
| MatchingHandler | Pair matching, partial scoring |
| OrderingHandler | Sequence validation |
| EssayHandler | Manual grading flag, word count |

### Service Tests (4 core services)

| Service | Test Focus |
|---------|-----------|
| ProgressManager | Cascade calculation, atomicity, no duplicates |
| PaymentManager | Gateway routing, order creation, status transitions |
| CertificateIssuer | Eligibility check, auto-issuance, no duplicates |
| NotificationDispatcher | Event mapping, preference respect, delivery |

### Kernel Tests (10 entity types)

| Entity | Test Focus |
|--------|-----------|
| Course | CRUD, field validation, status transitions |
| Chapter | CRUD, course reference, ordering |
| Lesson | CRUD, 7 type variants, chapter reference |
| Quiz | CRUD, config validation (threshold, timer) |
| Question | CRUD, 8 bundles, score calculation |
| QuizResult | CRUD, score validation, attempt tracking |
| Order | CRUD, status lifecycle, item management |
| UserCertificate | CRUD, verify code uniqueness |
| Notification | CRUD, type validation, recipient |
| UserMembership | CRUD, status & date management |

## E2E Test Scenarios (22)

### Student Flows (10)

| ID | Scenario | Priority |
|----|----------|----------|
| E2E-001 | Browse catalog → filter → view course detail | P0 |
| E2E-002 | Enroll free course → start learning → mark complete | P0 |
| E2E-003 | Purchase course → checkout → access granted | P0 |
| E2E-004 | Take quiz (multiple choice) → view result | P0 |
| E2E-005 | Complete course → receive certificate | P0 |
| E2E-006 | Continue learning (resume last lesson) | P0 |
| E2E-007 | View dashboard → progress overview | P1 |
| E2E-008 | Add to wishlist → view wishlist | P1 |
| E2E-009 | Rate course → submit review | P1 |
| E2E-010 | Verify certificate (public link) | P0 |

### Instructor Flows (4)

| ID | Scenario | Priority |
|----|----------|----------|
| E2E-011 | Create course → add chapters → add lessons | P0 |
| E2E-012 | Create quiz → add questions → configure | P0 |
| E2E-013 | Grade essay question → update score | P1 |
| E2E-014 | View course analytics (own courses) | P1 |

### Admin Flows (5)

| ID | Scenario | Priority |
|----|----------|----------|
| E2E-015 | Manage users → assign roles → verify access | P0 |
| E2E-016 | Manage orders → view details | P1 |
| E2E-017 | Manage certificate templates → create/edit | P1 |
| E2E-018 | View admin analytics dashboard | P1 |
| E2E-019 | Manage categories → CRUD | P1 |

### Commerce Flows (3)

| ID | Scenario | Priority |
|----|----------|----------|
| E2E-020 | Checkout → payment success → access | P0 |
| E2E-021 | Checkout → payment fail → retry | P0 |
| E2E-022 | Apply coupon → discounted price → checkout | P1 |

## Performance Targets

| Metric | Target | Test Tool |
|--------|--------|-----------|
| Homepage load | <2s | k6 / Lighthouse |
| Catalog page load | <3s | k6 |
| API response (p95) | <500ms | k6 |
| Report page load | <3s | k6 |
| Quiz submit response | <1s | Custom |
| PDF generation | <5s | Custom |

## Test Data Strategy

- Factory pattern cho test data generation (Drupal entity factories)
- Mỗi test creates và cleans up own data (Kernel test base)
- Shared fixtures cho read-only reference data (taxonomies, roles)
- Seed script (`scripts/fix_dummy_quiz_results.php`) cho test environment

---

*End of E01-test-strategy.md*
