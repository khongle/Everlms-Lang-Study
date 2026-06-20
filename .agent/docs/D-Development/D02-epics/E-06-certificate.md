# D02 — Epic Spec: Certificate & Badge System

**Epic ID:** E-06  
**Maps to draft:** G-CERTIFICATE  
**Features contributed to:** F-050 (Certificate Issuance), F-051 (PDF Generation), F-052 (Public Verification), F-053 (Badge Gamification)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Certificate Service (lms_certificate, lms_badge)  
**Dependencies:** E-01 (Foundation — mpdf/mpdf Composer dep), E-02 (Content — lms_course entity + field_certificate_template), E-03 (Assessment — QuizPassedEvent)  
**Estimated Duration:** 1 week  
**Status:** PARTIAL — certificate system fully as-built; badge system is a near-stub (entities only, zero award logic)

---

## Purpose

Auto-issue PDF certificates when a student passes a course quiz: a `CertificateEventListener` subscribes to `lms_quiz.passed`, traverses the Quiz→Lesson→Chapter→Course chain, finds the course's associated template (or falls back to the first active template), generates a unique 10-char code, calls `CertificateGenerator` (mPDF-based PDF), and creates a `lms_user_certificate` entity. The Epic also delivers admin template management (10 HTML blueprint designs), a public verification endpoint, revoke/regenerate capability, and the `lms_badge` entity data model (badge award logic planned but not yet materialised).

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-06-01 | CertificateTemplate + UserCertificate entities | module | modules-cert-integration.md; [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) | lms_certificate | as-built | E-02 | Must |
| S-06-02 | CertificateManager + CertificateGenerator (mPDF) + issuance service | module | modules-cert-integration.md; [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) | lms_certificate | as-built | S-06-01, E-01 (mpdf) | Must |
| S-06-03 | CertificateEventListener — auto-issuance on lms_quiz.passed | module | modules-cert-integration.md; [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) | lms_certificate | as-built | S-06-02, E-03 | Must |
| S-06-04 | Admin controllers — dashboard, template CRUD, revoke, regenerate | page | modules-cert-integration.md; [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) | lms_certificate | as-built | S-06-01, S-06-02 | Must |
| S-06-05 | Public verification endpoint (/certificate/verify) | page | modules-cert-integration.md; [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md) | lms_certificate | as-built | S-06-01 | Must |
| S-06-06 | lms_badge — Badge + UserBadge entities (award logic planned) | module | modules-cert-integration.md; [C05-modules/lms_badge.md](../../C-Design/C05-modules/lms_badge.md) | lms_badge | stub / near-stub | E-01 | Could |

---

## Build Order Within Epic

1. **S-06-01** — CertificateTemplate + UserCertificate entities (foundation for all certificate stories)
2. **S-06-02** — CertificateManager + CertificateGenerator (depends on S-06-01; requires mpdf/mpdf from E-01)
3. **S-06-03** — CertificateEventListener (depends on S-06-02 and E-03 QuizPassedEvent)
4. **S-06-04, S-06-05** — Admin controllers + verification endpoint (parallel; both depend on S-06-01, S-06-02)
5. **S-06-06** — lms_badge entity (no inter-epic dependencies beyond E-01; can be done any time)

---

## Story Details

### S-06-01 — CertificateTemplate + UserCertificate entities
**As-built.** `lms_certificate_template`: fields `name`, `field_body` (tokens: [student_name], [course_name], [issue_date]), `field_design` (classic/modern/elegant/minimal), `field_background/logo/signature/seal` (images), `field_orientation` (landscape/portrait), `field_layout` (string_long — raw HTML/Twig structure), `field_color_scheme`, `status`. Fields added via update hooks: update_10001 (field_logo, field_orientation, field_layout, field_color_scheme), update_10002 (field_seal). `lms_user_certificate`: fields `field_template` (→ lms_certificate_template), `field_quiz_result` (→ lms_quiz_result), `field_course` (→ lms_course), `field_certificate_number` (string 32, unique code), `field_pdf_file` (managed file, public://certificates/issued/), `field_issued_at`, `field_expiry_date` (datetime), `field_revoked` (boolean), `field_revoke_reason`. **Known bug:** `lms_user_certificate.field_expiry_date` type is `datetime` but the column is stored as VARCHAR not INT in MariaDB — data integrity risk for date comparison queries. Source: [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md).

### S-06-02 — CertificateManager + CertificateGenerator (mPDF) + issuance service
**As-built.** `CertificateGenerator::generate()`: renders `certificate_pdf` Twig theme to HTML → calls mPDF (checks `class_exists('\Mpdf\Mpdf')`; degrades gracefully to NULL if missing, logging error) → A4 PDF with orientation from template → saves as managed file at `public://certificates/issued/Certificate_{code}.pdf` → returns FileInterface. `CertificateGenerator::preview()`: same flow with mock user/course, returns raw PDF binary string (not saved). `CertificateManager::checkAndIssueCertificate()`: checks for existing certificate (idempotent — no double-issue), finds template via `lms_course.field_certificate_template` or falls back to first active template, generates 10-char uppercase MD5 code (`strtoupper(substr(md5(uniqid()), 0, 10))`), calls generator, saves UserCertificate entity. 10 HTML blueprint templates in `lms_content_tools/src/Blueprints/` (academic, classic, clinical, corporate, creative, elegant, minimal, modern, professional, vintage). Source: [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md).

### S-06-03 — CertificateEventListener — auto-issuance on lms_quiz.passed
**As-built.** Service `lms_certificate.event_listener` tagged as `event_subscriber`. Subscribes to `QuizPassedEvent::EVENT_NAME` (`lms_quiz.passed`); subscription guarded by `class_exists(QuizPassedEvent::class)`. `onQuizPassed($event)`: traverses `Quiz → Lesson (field_quiz back-lookup) → Chapter (field_lessons reverse) → Course (field_chapters reverse)` to find the owning course, then calls `CertificateManager::checkAndIssueCertificate(user, course, quiz_result)`. If traversal fails (e.g. quiz not attached to any lesson), certificate is silently not issued (no error surfaced to student). Source: [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md).

### S-06-04 — Admin controllers: dashboard, template CRUD, revoke, regenerate
**As-built.** `CertificateAdminController`: `adminDashboard()` (stats: total/revoked/recent-7d, lists up to 50 certs), `adminTemplates()` (lists templates with edit/preview URLs), `templateAddForm()/templateEditForm()` (entity form in `lms_admin_entity_form` wrapper), `templatePreview()` (calls `CertificateGenerator::preview()`, streams inline PDF), `regenerateCertificate()` (blocks if revoked, re-runs generator with existing code). `RevokeCertificateForm` (ConfirmFormBase; adds `revoke_reason` textarea, sets field_revoked=1). `hook_entity_operation()` adds "Revoke" operation to UserCertificate list for users with `revoke any certificate` permission. Routes at `/workspace/certificates`, `/workspace/certificate-templates`, `/admin/lms/certificates/{id}/revoke`. Source: [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md).

### S-06-05 — Public verification endpoint (/certificate/verify)
**As-built.** Route `/certificate/verify` (`_access: TRUE`, public). `VerificationForm` (FormBase): text field for certificate code, looks up `lms_user_certificate` by `field_certificate_number` exact match, renders `certificate_verification_result` theme in same form rebuild. Shows: student name, course name, issue date, expiry date, revoked status. Source: [C05-modules/lms_certificate.md](../../C-Design/C05-modules/lms_certificate.md).

### S-06-06 — lms_badge — Badge + UserBadge entities (award logic planned)
**Near-stub.** Entity `lms_badge` (fields: `name`, `field_description`, `field_icon` image, `field_type` → taxonomy_term:badge_type, `field_xp_reward`, `field_criteria` string 1024 JSON format: `{"type":"courses_completed","count":5}`). Entity `lms_user_badge` (fields: `field_badge` → lms_badge, `field_awarded_at`). No services, no routes, no module hooks, no install schema, no templates. Admin CRUD via auto-generated routes only. **Badge award logic is completely absent** — no service, cron job, or event subscriber evaluates `field_criteria` or calls any badge award function. `field_criteria` JSON schema documented in code comments only. Source: [C05-modules/lms_badge.md](../../C-Design/C05-modules/lms_badge.md).

---

## Affected Modules

| Module | Role in Epic | Status |
|--------|-------------|--------|
| lms_certificate | All certificate entities, generator, manager, event listener, admin controllers, verification | as-built |
| lms_badge | Badge + UserBadge entities only — zero award logic | stub |

---

## Known Issues (log to E02-issues/)

| Issue | Severity | Story | Description |
|-------|----------|-------|-------------|
| field_expiry_date stored as VARCHAR | High | S-06-01 | `lms_user_certificate.field_expiry_date` is datetime type but MariaDB column is VARCHAR not INT; date comparison queries may malfunction |
| Certificate traversal silent failure | Medium | S-06-03 | If Quiz not attached to Lesson→Chapter→Course chain, no cert issued and no error reported to student |
| Certificate code is MD5 substr | Low | S-06-02 | Code generated as `strtoupper(substr(md5(uniqid()), 0, 10))` — not cryptographically random; guessable given small space; consider UUID v4 or HMAC-based code |
| lms_badge award logic absent | Medium | S-06-06 | `field_criteria` JSON schema defined but no evaluator; badges cannot be awarded programmatically |

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-050 | Quiz pass triggers certificate auto-issuance | F-050 | S-06-03 |
| AC-051 | Certificate PDF generated by mPDF; file saved to public://certificates/issued/ | F-051 | S-06-02 |
| AC-052 | Idempotent: passing same quiz twice does NOT issue duplicate certificate | F-050 | S-06-02 |
| AC-053 | /certificate/verify/{code} returns correct student/course/date information | F-052 | S-06-05 |
| AC-054 | Admin can revoke certificate; verification page shows revoked status | F-050 | S-06-04 |
| AC-055 | Admin can preview certificate template as inline PDF | F-050 | S-06-04 |
| AC-056 | Certificate issuance gracefully degrades if mPDF not installed (logs error, no crash) | F-051 | S-06-02 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-050 | lms_quiz.passed event → CertificateEventListener | S-06-03 | Subscribed conditionally on class_exists(QuizPassedEvent) |
| INT-051 | lms_course.field_certificate_template → CertificateManager (template lookup) | S-06-02 | Falls back to first active template if course has no specific template |
| INT-052 | mpdf/mpdf Composer package → CertificateGenerator | S-06-02 | From E-01; class_exists guard for graceful degradation |
| INT-053 | lms_content_tools `everlms:mock-certs` Drush command → lms_certificate.manager | S-06-02 | Used for seeding demo certificates |
| INT-054 | lms_progress.UserPortalController::myCertificates → lms_user_certificate storage | S-06-01 | UserPortalController in E-04 queries lms_user_certificate for the learner portal |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story; CertificateManager idempotency must have explicit test
- **Cross-Story integration tests:** End-to-end chain: QuizPassedEvent → CertificateEventListener → CertificateManager → CertificateGenerator → verify PDF created; verify verification endpoint resolves code; verify revoke blocks regeneration
- **Cross-Story Integration Test Owner:** `S-06-03` (CertificateEventListener is the entry point for the full certificate chain)
- **Acceptance tests:** AC-050 through AC-056

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_BADGE_AWARD | S-06-06 | Enable badge award evaluation logic | Medium | 0% until award service implemented |
| FF_FEAT_CERT_EXPIRY | S-06-01 | Enable certificate expiry date enforcement | Medium | 0% until field_expiry_date VARCHAR bug fixed |

---

## CP4 Readiness Criteria

- [x] S-06-01 through S-06-05 have status: DONE (all as-built)
- [ ] S-06-06 status: stub only — badge award logic NOT implemented
- [x] G1-G4 passed for S-06-01 through S-06-05
- [x] Cross-Story integration tests within Epic passing (certificate chain)
- [x] D07 code-map delta complete for all files touched in this Epic
- [x] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] Known issues logged in E02-issues/ (field_expiry_date VARCHAR bug, silent traversal failure, MD5 code space, badge award logic absent)
- [ ] Feature flags registered for FF_FEAT_BADGE_AWARD and FF_FEAT_CERT_EXPIRY
- [x] Every Story that touched code/config/UI lists relevant D00-conventions/ files
