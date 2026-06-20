---
id: F-006
name: Certification & Badges
priority: Must
status: APPROVED
parent-requirements: [BR-006]
owning_modules: [lms_certificate, lms_badge]
primary_source: C05
story_anchors: []
---

# F-006 — Certification & Badges

## User story

As a **learner**, I want to automatically receive a PDF certificate when I pass a course quiz, and be able to verify it publicly, so that I can share proof of my achievement with employers or institutions.

## Description

Certification is fully implemented end-to-end. `lms_certificate` provides two entities (`lms_certificate_template`, `lms_user_certificate`), three services, an event subscriber, and complete admin UI. PDF generation uses `mpdf/mpdf` (Composer dependency). Auto-issuance is triggered by a Drupal event subscriber listening to `lms_quiz.QuizPassedEvent`: `CertificateEventListener::onQuizPassed()` traverses Quiz → Lesson (`field_quiz`) → Chapter (`field_lessons`) → Course (`field_chapters`) to identify the parent course, then calls `CertificateManager::checkAndIssueCertificate()` which prevents double-issuance, selects the course-specific template (via `field_certificate_template` on `lms_course`) or falls back to the first active template, generates a 10-character uppercase MD5 code, and saves the `lms_user_certificate` entity with a PDF at `public://certificates/issued/Certificate_{code}.pdf`.

Certificates have lifecycle management: they can be revoked (`RevokeCertificateForm`, sets `field_revoked` + `field_revoke_reason`) and regenerated (`CertificateAdminController::regenerateCertificate`). A public verification page at `/certificate/verify` accepts the certificate code and returns the result.

**Known data-type bug (as-built):** `lms_user_certificate.field_expiry_date` is declared as `datetime` (date-only) but the underlying column was originally created as `varchar` — see risk register.

**Badges are a near-stub (as-built):** `lms_badge` ships two entities (`lms_badge`, `lms_user_badge`) with a documented JSON criteria format (`{"type":"courses_completed","count":5}` in `field_criteria`), but there is **zero award logic** — no service, no event subscriber, no cron job evaluates criteria or creates `lms_user_badge` records. Badges are defined in data but never awarded automatically.

## In scope

### Certificates (as-built — fully implemented)

- Certificate template entity (`lms_certificate_template`) with fields: name, `field_body` (token text), `field_design` (classic/modern/elegant/minimal), `field_background` (image), `field_signature` (image), `field_logo` (image), `field_seal` (image), `field_orientation` (landscape/portrait), `field_layout` (raw Twig template), `field_color_scheme`, status.
- Token replacement: `[student_name]`, `[course_name]`, `[issue_date]` in `field_body`.
- PDF generation via mPDF (A4, orientation from template); graceful degradation if mPDF is absent (logs error, returns NULL).
- Auto-issuance on `lms_quiz.passed` event — one certificate per learner per course (duplicate check in `CertificateManager`).
- Unique certificate code (10-char uppercase MD5); `lms_user_certificate.field_certificate_number` (max 32 chars).
- Public verification page at `/certificate/verify` — shows revoked status.
- Admin dashboard at `/workspace/certificates` — total, revoked, recent 7d stats; lists up to 50 most recent.
- Template management at `/workspace/certificate-templates` — list, add, edit, live preview (returns inline PDF).
- Revocation via `RevokeCertificateForm` (ConfirmFormBase, adds revoke reason textarea).
- Regeneration at `/workspace/certificates/{id}/regenerate` (blocked if revoked).
- Learner view at `/my-certificates` — lists all `lms_user_certificate` entities owned by the current user.
- Course-to-template assignment via `lms_course.field_certificate_template` (entity reference).
- `hook_entity_operation()` adds "Revoke" operation link on `lms_user_certificate` entity list for users with `revoke any certificate`.

### Badges (target-state — entities only, no award logic)

- `lms_badge` entity: name, `field_description`, `field_icon` (image), `field_type` (taxonomy: badge_type vocabulary), `field_xp_reward` (integer), `field_criteria` (JSON string, max 1024 chars).
- `lms_user_badge` entity: uid (owner), `field_badge` (reference to lms_badge), `field_awarded_at` (created timestamp).
- `badge_type` taxonomy vocabulary (seeded by `lms_base` install hook).
- Admin CRUD for badge definitions at `/manage/badges` (default entity routes via `AdminHtmlRouteProvider`).
- XP reward value is stored but never totalled or surfaced in any UI.

## Out of scope

- Badge award automation — no criteria evaluation, no event subscribers, no cron processing. This is a known gap (stub).
- Leaderboard / points ranking — no implementation; XP values stored but not aggregated.
- Level progression (Beginner → Expert) — no implementation.
- Certificate sharing via social media links — verification URL exists but no share-button UI.
- Certificate export/bulk CSV — admin dashboard shows list but no export endpoint exists for certificates themselves (the analytics module handles enrollment/cert metrics in reports).
- SCORM-compliant completion signal as certificate trigger — only `lms_quiz.passed` triggers auto-issuance; SCORM completion does not.
- Manual certificate issuance UI for admins — admin can only revoke/regenerate; manual issuance for non-quiz courses requires calling `CertificateManager` programmatically.

## Acceptance summary

When a learner passes a quiz (`lms_quiz.passed` event fires), a `lms_user_certificate` entity is created automatically, a PDF is generated at `public://certificates/issued/Certificate_{code}.pdf`, and the learner can download it from `/my-certificates`. An administrator can view issued certificates at `/workspace/certificates`, revoke one with a reason, or regenerate the PDF. Anyone can verify a certificate at `/certificate/verify` by entering the code. Badges can be created via admin UI but are not automatically awarded. Full Given/When/Then in `B05-acceptance-criteria/F-006.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Certificate template design | Admin / Brand owner | administrator | `/workspace/certificate-templates/add`, `/edit` | field_layout accepts raw Twig HTML |
| Certificate template body text | Course owner | manager, administrator | Same entity form | Token: [student_name], [course_name], [issue_date] |
| Badge definitions (name, icon, criteria) | Admin | administrator | `/manage/badges/add`, `/manage/badges/{id}/edit` | Criteria is a JSON string; no UI parser |
| Badge type taxonomy | Admin | administrator | Drupal taxonomy admin UI | Vocabulary: badge_type |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-002 | Quiz pass event triggers certificate issuance |
| Feature | F-003 | Course completion state is read by certificate manager |
| Workflow | WF-003 | Certificate issuance workflow |
| Integration | INT-mpdf | `mpdf/mpdf` Composer package required for PDF generation |

## Open questions

- [ ] `field_expiry_date` on `lms_user_certificate` is varchar (not int/date) — needs schema correction before expiry-based revocation can work. Logged in risk register.
- [ ] Badge award logic: what triggers, what criteria types, and what threshold should be implemented first (courses_completed, quiz_passed, streak)?
- [ ] Should course completion without a quiz (100% lesson progress) also trigger certificate issuance? Currently only `lms_quiz.passed` fires the event.
- [ ] Manual certificate issuance for admin: is a UI button in the admin dashboard required, or is the `CertificateManager` service call via Drush sufficient?
