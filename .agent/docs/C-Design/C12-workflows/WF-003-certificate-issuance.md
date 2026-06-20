---
workflow: WF-003
slug: certificate-issuance
mode: as-built
b04_source: B04/WF-003-certificate-issuance.md
adequacy: NOT_RUN
adequacy_run: null
---

# C12 — Workflow: WF-003 Certificate Issuance

**Trigger:** `lms_quiz.passed` event (`QuizPassedEvent`) dispatched by `QuizController::submitQuiz()` when result term = Passed.
**Outcome:** `lms_user_certificate` entity persisted with a PDF file at `public://certificates/issued/Certificate_{code}.pdf`; student can view and download from `/my-certificates`; public verification available at `/certificate/verify`.
**B04 source:** [`../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md`](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)
**Mode:** as-built

---

## Actors

| Actor (C02 role) | Step participation | Permissions needed |
|---|---|---|
| System | Steps 1–8 (event subscriber → PDF generation → entity persist) | — (runs in event loop) |
| Student (authenticated) | Steps 9–10 (view + download; public verification) | `_user_is_logged_in: TRUE` (view); public (verify) |
| Admin | Revoke, regenerate, manage templates | `administer lms_course`; `revoke any certificate` |

## Entry Points

| URL | Page (C11) | Method | Form | Feature Flag |
|---|---|---|---|---|
| `/my-certificates` | my-certificates | GET | — | — |
| `/certificate/verify` | certificate-verify | GET+POST | `VerificationForm` | — |
| `/workspace/certificates` | admin-certificates | GET | — | `administer lms_course` |
| `/admin/lms/certificates/{id}/revoke` | revoke-certificate | GET+POST | `RevokeCertificateForm` | `revoke any certificate` |
| `/workspace/certificates/{id}/regenerate` | — | GET | — | `administer lms_course` |
| `/workspace/certificate-templates` | admin-templates | GET | — | `administer lms_course` |
| `/workspace/certificate-templates/add` | — | GET+POST | `CertificateTemplateForm` | `administer lms_course` |
| `/workspace/certificate-templates/{id}/edit` | — | GET+POST | `CertificateTemplateForm` | `administer lms_course` |
| `/workspace/certificate-templates/{id}/preview` | — | GET | — (inline PDF) | `administer lms_course` |

## Entities Touched

| Entity | Operation | Fields Written | State Transition |
|---|---|---|---|
| `lms_quiz_result` | READ (from event) | — | — |
| `lms_quiz` | READ (entity traversal source) | — | — |
| `lms_lesson` | READ (via `field_quiz` back-reference) | — | — |
| `lms_chapter` | READ (via `field_lessons` back-reference) | — | — |
| `lms_course` | READ (via `field_chapters` back-reference) | — | — |
| `lms_certificate_template` | READ (course-specific or first active) | — | — |
| `lms_user_certificate` | CREATE (if not already exists) | `uid`, `field_template`, `field_quiz_result`, `field_course`, `field_certificate_number`, `field_pdf_file`, `field_issued_at` | — → issued |
| Drupal managed file | CREATE | PDF at `public://certificates/issued/Certificate_{code}.pdf` | — |

## State Transitions

```
lms_user_certificate lifecycle:

  [QuizPassedEvent fired]
       │
       ▼  CertificateEventListener::onQuizPassed()
  [traversal: Quiz → Lesson → Chapter → Course]
       │
       ├──▶ course NOT found (field_quiz empty on lessons)
       │       └──▶ SILENT SKIP — no certificate; no log warning
       │
       ▼  CertificateManager::checkAndIssueCertificate()
       │
       ├──▶ certificate already exists for uid + course
       │       └──▶ RETURN existing entity (idempotent)
       │
       ├──▶ no active lms_certificate_template
       │       └──▶ RETURN false — no certificate
       │
       ├──▶ mPDF not installed
       │       └──▶ LOG error, generator returns NULL → no PDF, no entity saved
       │
       └──▶ SUCCESS
               ├── CertificateGenerator::generate() → mPDF → managed file
               └── lms_user_certificate saved → field_status: [issued]

lms_user_certificate.field_revoked:
  FALSE (default) → TRUE (RevokeCertificateForm; requires revoke any certificate)
```

State guards:

| Transition | Trigger | Guard | Side Effects |
|---|---|---|---|
| `[none] → issued` | `CertificateManager::checkAndIssueCertificate()` | `lms_quiz.passed` event; no prior cert for uid+course; active template; mPDF installed | PDF file saved; `lms_user_certificate` entity created |
| `issued → revoked` | Admin `RevokeCertificateForm::submitForm()` | `revoke any certificate` permission | `field_revoked=TRUE`; `field_revoke_reason` set; `/certificate/verify` shows revoked |
| Regenerate PDF | Admin `/workspace/certificates/{id}/regenerate` | `administer lms_course`; cert NOT revoked | New PDF file written; `field_pdf_file` updated |

## Traversal Path (as-built)

```
CertificateEventListener::onQuizPassed($event):
  $quiz = $event->getQuiz()                          // lms_quiz entity
  foreach $quiz->field_quiz (back-ref on lms_lesson):
    $lesson = loaded lms_lesson
    foreach $lesson's parent chapter (field_lessons on lms_chapter):
      $chapter = loaded lms_chapter
      foreach $chapter's parent course (field_chapters on lms_course):
        $course = loaded lms_course → BREAK

  CertificateManager::checkAndIssueCertificate($user, $course, $quiz_result)
```

> **Risk**: traversal assumes `field_quiz` exists on at least one `lms_lesson`, and that lesson belongs to a chapter that belongs to a course. If the quiz is standalone (not linked to any lesson), traversal returns NULL course and issuance silently fails.

## PDF Generation Details

```
CertificateGenerator::generate(CertificateTemplate, UserInterface, Course, $code):
  1. Render 'certificate_pdf' Twig theme
       template_preprocess_certificate_pdf():
         resolves field_background, field_logo, field_signature, field_seal
         URI → server real-path (so mPDF can read from filesystem)
  2. class_exists('\Mpdf\Mpdf') guard → NULL + log if missing
  3. new \Mpdf\Mpdf(['format' => 'A4', 'orientation' => field_orientation])
  4. WriteHTML($html)
  5. Output() → raw PDF string
  6. file_save_data() → public://certificates/issued/Certificate_{code}.pdf
  7. Return FileInterface
```

Token replacement in `field_body`: `[student_name]`, `[course_name]`, `[issue_date]` substituted in the Twig template.

## Modules Involved

| Module | Responsibility in This Workflow |
|---|---|
| `lms_quiz` | Dispatches `QuizPassedEvent`; provides `lms_quiz`, `lms_quiz_result` entities |
| `lms_certificate` | `CertificateEventListener` (subscriber); `CertificateManager` (deduplication, template selection, code generation); `CertificateGenerator` (mPDF render + file save); `VerificationForm`; `RevokeCertificateForm`; `CertificateAdminController` |
| `lms_progress` | `UserPortalController::myCertificates()` renders student view at `/my-certificates` |

## Permissions

| Step | Public (anon) | Student (auth) | Manager | Admin |
|---|---|---|---|---|
| Event → issue certificate | — | — (automatic) | — | — |
| View `/my-certificates` | — | own certificates | own | all (with `view any certificate`) |
| Verify at `/certificate/verify` | Public | Public | Public | Public |
| Revoke | — | — | — | `revoke any certificate` |
| Regenerate PDF | — | — | — | `administer lms_course` |
| Manage templates | — | — | `manage certificate templates` | `administer lms_course` |

## Notifications

| Trigger | Recipient | Channel | Template |
|---|---|---|---|
| `QuizPassedEvent` | Student | in-app `quiz_result` type (NotificationEventSubscriber) | lms_notification entity |
| Certificate issued | — | **None** — `CertificateEventListener` does not dispatch a notification | — |

> **Gap**: there is no `certificate` notification dispatched on issuance. The student receives a `quiz_result` notification (from `NotificationEventSubscriber`) but no dedicated certificate notification. `lms_notification.field_type` includes `certificate` as an allowed value but it is never set by any code.

## Cache Invalidation

| Step | Cache Tag(s) | Search Index | List Views |
|---|---|---|---|
| `lms_user_certificate` saved | No explicit cache tag defined (as-built) | — | `/workspace/certificates` (dynamic) |
| `/my-certificates` (UserPortalController) | No `#cache` on render array | — | — |

## Operations Queues

| Page | Filters | Owner Role |
|---|---|---|
| `/workspace/certificates` | date, revoked status | `administer lms_course` |
| `/workspace/certificate-templates` | — | `administer lms_course` |

## Editorial / Management Surfaces

| Entity / Content | Create/Edit UI | Role / Permission | State | Translation | Visible C11 Surfaces |
|---|---|---|---|---|---|
| `lms_certificate_template` | `/workspace/certificate-templates/add`, `.../edit` | `manage certificate templates` / `administer lms_course` | `status` boolean (active/inactive) | — | `/workspace/certificate-templates` |
| `lms_user_certificate` | No create UI (issued automatically); revoke via `/admin/lms/certificates/{id}/revoke` | `revoke any certificate` | `field_revoked` boolean | — | `/workspace/certificates`, `/my-certificates` |

## Failure Modes

| Failure | Detection | Handling |
|---|---|---|
| `QuizPassedEvent` not dispatched (essay pending) | EssayStrategy always sets `pending_review=TRUE`; Pending term blocks event | Instructor must grade essay manually — **no route, planned** |
| Quiz not linked to any lesson | Traversal returns NULL course | Silent skip — no certificate, no error log (gap: should log warning) |
| Duplicate issuance attempt | `CertificateManager` queries `uid + field_course` before create | Returns existing entity; idempotent |
| No active `lms_certificate_template` | Manager finds no entity with `status=1` | Returns `false`; logs nothing; no certificate |
| `mpdf/mpdf` not installed | `class_exists('\Mpdf\Mpdf')` check | Logs error, generator returns NULL; `lms_user_certificate` not saved |
| File write failure | `file_save_data()` returns FALSE | Generator returns NULL; `UserCertificate` entity not saved; check `public://certificates/issued/` permissions |
| `field_expiry_date` type mismatch | `lms_user_certificate.field_expiry_date` declared `datetime` but DB column is `varchar` | Data integrity bug — **bug, as-built**; fix: migrate column to proper datetime type |

## Acceptance (B05)

- [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/) — `lms_user_certificate` entity persists with `field_certificate_number` and `field_pdf_file`; PDF exists at `public://certificates/issued/`; `/certificate/verify` returns valid result; duplicate invocation is idempotent

## Implementation Status

| Step | Status | Notes |
|---|---|---|
| CertificateEventListener subscriber | DONE | Conditional on QuizPassedEvent class existence |
| Entity traversal Quiz→Lesson→Chapter→Course | DONE | As-built; silent fail if quiz not linked to lesson |
| CertificateManager deduplication | DONE | uid + course query |
| Template selection (course-specific → fallback) | DONE | As-built |
| CertificateGenerator mPDF PDF | DONE | Graceful degrade if mPDF missing |
| RevokeCertificateForm | DONE | As-built |
| VerificationForm (public) | DONE | As-built |
| CertificateAdminController (dashboard, regenerate) | DONE | As-built |
| Notification on certificate issue | PLANNED | No notification dispatched currently |
| Essay re-trigger after instructor grading | PLANNED | No mechanism to re-dispatch QuizPassedEvent |
| `field_expiry_date` DB type fix | PLANNED | Bug — varchar column |
