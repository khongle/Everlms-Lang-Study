---
usecase_id: UC-003-03
workflow: WF-003-certificate-issuance
type: edge
---

# UC-003-03 — Course Has No Certificate Template → Fallback to First Active Template

**One-line purpose:** When `checkAndIssueCertificate()` finds that the course does not have a `field_certificate_template` value, it falls back to the first active `lms_certificate_template` entity rather than failing. A certificate is still issued.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Passes a quiz for a course with no course-specific template |
| `CertificateManager::checkAndIssueCertificate()` | Implements template selection with fallback |
| `CertificateGenerator::generate()` | Generates PDF from the fallback template |

---

## Preconditions

- All preconditions from UC-003-01 apply.
- `lms_course.field_certificate_template` is empty or null for the course.
- At least one `lms_certificate_template` entity exists with `status=1` (active).
- No existing `lms_user_certificate` for this student + course (otherwise UC-003-02 applies).

---

## Trigger

`QuizPassedEvent` dispatched; `CertificateEventListener` invokes `checkAndIssueCertificate()` for a course with no specific template set.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** `CertificateEventListener` resolves the course and calls `checkAndIssueCertificate($user, $course, $quiz_result)` |
| 2 | **When** `checkAndIssueCertificate()` reads `$course->field_certificate_template` |
| 3 | **Then** the field is empty; template is not directly available from the course |
| 4 | **Then** the manager queries `lms_certificate_template` storage: `status=1`, sorted or limited to find the first active template |
| 5 | **When** at least one active template exists |
| 6 | **Then** the first active `lms_certificate_template` entity is used as the fallback template |
| 7 | **Then** processing continues identically to UC-003-01 steps 12–18: code generated, PDF rendered from fallback template, `lms_user_certificate` entity created |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_user_certificate` | `field_template` | references the fallback (not course-specific) template |
| `lms_user_certificate` | all other fields | same as UC-003-01 |
| Drupal file entity | `uri` | `public://certificates/issued/Certificate_{code}.pdf` |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Fallback template selection | No log | Manager silently falls back; no distinction in logs between course-specific and fallback |
| No template found | `CertificateGenerator::generate()` returns NULL | If no active template exists, generator cannot proceed; `lms_user_certificate` may still be created without `field_pdf_file` |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-061](../../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md#ac-061-certificate-falls-back-to-the-first-active-template-if-no-course-specific-template-is-set) — Fallback to first active template | TEST-061 |

---

## Notes

- If NO active template exists (all templates are `status=0` or no templates have been created), `CertificateManager::checkAndIssueCertificate()` reaches `if (!$template) { return FALSE; }` and silently returns `FALSE`. No exception is thrown; no log is written; no certificate is issued. This is confirmed in `CertificateManager.php` source.
- The fallback is globally scoped — the same generic certificate design is used for all courses that lack a specific template. Instructors who want course-branded certificates must explicitly set `field_certificate_template` on each course.
- Template `field_orientation` (landscape/portrait) is honored from whichever template is selected (specific or fallback); this field was added by `lms_certificate_update_10001`.
