---
entity: lms_certificate_template + lms_user_certificate
mode: as-built
owner_module: lms_certificate
content_kind: editorial (lms_certificate_template) / transactional (lms_user_certificate)
editor_owned: true (lms_certificate_template) / false (lms_user_certificate)
revisionable: false
translatable: false
moderation_workflow: null
---

# C04 — Entities: Certificate (lms_certificate_template + lms_user_certificate)

**Owner module:** [`lms_certificate`](../C05-modules/lms_certificate.md)
**Mode:** as-built
**Description:** Certificate template definitions (editorial) and auto-issued user certificates (transactional). PDF generation via mPDF; issuance driven by an event subscriber on `QuizPassedEvent`.

---

## Entity 1: lms_certificate_template (Certificate Template)

**Storage:** `lms_certificate_template` (single flat table, no `_field_data` split — non-translatable)
**Description:** Defines the visual and structural layout of a PDF certificate; referenced by courses and issued certificates.
**Content kind:** editorial
**Editor owned:** true
**Management UI:** `/workspace/certificate-templates`
**Create / Edit / Delete forms:** `lms_certificate.admin_template.add`, `lms_certificate.admin_template.edit`, auto-generated delete route via `AdminHtmlRouteProvider`
**Display modes:** default (EntityViewBuilder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A (non-translatable)
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | External-facing identifier |
| 3 | `name` | Name | string (255) | 1 | Yes | — | No | — | Base field | Template title; entity label key |
| 4 | `field_body` | Body | text_long | 1 | No | — | No | — | Base field | HTML template with tokens: `[student_name]`, `[course_name]`, `[issue_date]` |
| 5 | `field_design` | Design | list_string | 1 | No | `classic` | No | classic, modern, elegant, minimal | Base field | Visual preset |
| 6 | `field_background` | Background | image → file | 1 | No | — | No | — | Base field | `file_directory: certificates/backgrounds` |
| 7 | `field_signature` | Signature | image → file | 1 | No | — | No | — | Base field | `file_directory: certificates/signatures` |
| 8 | `field_logo` | Logo | image → file | 1 | No | — | No | — | Base field | `file_directory: certificates/logos`; added by `lms_certificate_update_10001` |
| 9 | `field_seal` | Seal | image → file | 1 | No | — | No | — | Base field | `file_directory: certificates/seals`; added by `lms_certificate_update_10002` |
| 10 | `field_orientation` | Orientation | list_string | 1 | No | `landscape` | No | landscape, portrait | Base field | Added by `lms_certificate_update_10001` |
| 11 | `field_layout` | Layout | string_long | 1 | No | — | No | — | Base field | Raw Twig/HTML template structure used by mPDF renderer |
| 12 | `field_color_scheme` | Color Scheme | string (255) | 1 | No | — | No | — | Base field | Accent color string |
| 13 | `status` | Status | boolean | 1 | No | TRUE | No | — | Base field | Active/Inactive; inactive templates not selected for auto-issuance |

#### DB table: lms_certificate_template (as-built, AUTO_INCREMENT ≈ 36, ~35 rows)

```
id                          int NOT NULL AUTO_INCREMENT  PK
uuid                        varchar(128) UNIQUE
name                        varchar(255)
field_body__value           longtext
field_body__format          varchar(255)
field_design                varchar(255)
field_background__target_id int unsigned  (FK → file_managed)
field_background__alt/title/width/height
field_signature__target_id  int unsigned  (FK → file_managed)
field_signature__alt/title/width/height
field_logo__target_id       int unsigned  (FK → file_managed)
field_logo__alt/title/width/height
field_seal__target_id       int unsigned  (FK → file_managed)
field_seal__alt/title/width/height
status                      tinyint
field_orientation           varchar(255)
field_layout                longtext
field_color_scheme          varchar(255)
```

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| status | `status` | B-Tree | Filter active templates |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_course` | M:1 (referenced) | `lms_course_field_data.field_certificate_template` | No cascade; course retains reference after template edit |
| `lms_user_certificate` | 1:M | `lms_user_certificate.field_template` | No cascade; issued certs keep template reference |
| `file` | M:1 | `field_background__target_id`, `field_logo__target_id`, `field_signature__target_id`, `field_seal__target_id` | Drupal file usage tracking |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/workspace/certificate-templates` (list), `/workspace/certificate-templates/add` (add), `/workspace/certificate-templates/{id}/edit` (edit), `/workspace/certificate-templates/{id}/preview` (PDF preview inline) |
| Editor role / permission | `manage certificate templates` (custom); `administer lms_course` (admin) |
| Form display | Standard `CertificateTemplateForm` (ContentEntityForm); all fields exposed. Preview button available from list. |
| View displays / view modes | `default` (EntityViewBuilder) |
| Revision / moderation | Not revisionable; no workflow |
| Translation | N/A |
| Preview | `/workspace/certificate-templates/{id}/preview` — calls `CertificateGenerator::preview()`, returns inline PDF using mock user/course data |
| Cache invalidation | `entity:lms_certificate_template:{id}` cache tag invalidated on save |
| Smoke verification | Edit template name → save → load `/workspace/certificate-templates` → confirm updated name |

### Business rules

- A template can be assigned to one or more courses via `lms_course.field_certificate_template`.
- `CertificateManager::checkAndIssueCertificate()` selects the course-specific template; falls back to the first `status=1` template if none is assigned.
- `status=0` (inactive) templates are never auto-selected during issuance.
- `field_body` tokens resolved at PDF generation time by mPDF via `certificate_pdf` theme; supported tokens: `[student_name]`, `[course_name]`, `[issue_date]`.
- `field_layout` stores a raw Twig/HTML structure rendered directly into mPDF — no sandbox; admin-only edit.
- Image fields (background, logo, signature, seal) are converted to server real-paths by `template_preprocess_certificate_pdf()` before mPDF reads them.
- Template deletion does not cascade; issued `lms_user_certificate` rows retain their `field_template` reference (FK in DB, no ON DELETE CASCADE).

---

## Entity 2: lms_user_certificate (User Certificate — Issued)

**Storage:** `lms_user_certificate` (single flat table, non-translatable, no `_field_data` split)
**Description:** A single issued certificate record linking a student, a course, a quiz result, and a generated PDF file. Created programmatically by `CertificateManager`; not editor-created.
**Content kind:** transactional
**Editor owned:** false (no form/route_provider defined; managed programmatically only)
**Management UI:** `/workspace/certificates` (admin dashboard list)
**Create / Edit / Delete forms:** N/A (no create/edit form); delete via `lms_certificate.revoke` form
**Display modes:** default (EntityViewBuilder)
**Revision / moderation:** not revisionable; N/A
**Translation:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (serial) | 1 | Yes | auto | No | — | Base field | Auto-increment PK |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | External-facing identifier |
| 3 | `uid` | Owner | entity_reference → user | 1 | — | — | No | — | Base field | Certificate recipient (EntityOwnerTrait) |
| 4 | `field_template` | Template | entity_reference → lms_certificate_template | 1 | Yes | — | No | — | Base field | Template used at issuance time |
| 5 | `field_quiz_result` | Quiz Result | entity_reference → lms_quiz_result | 1 | No | — | No | — | Base field | Triggering quiz result; nullable (manual issuance path) |
| 6 | `field_course` | Course | entity_reference → lms_course | 1 | No | — | No | — | Base field | Course the certificate is for |
| 7 | `field_certificate_number` | Certificate Number | string (32) | 1 | No | — | No | — | Base field | 10-char uppercase MD5-derived code; used on public verification page |
| 8 | `field_pdf_file` | PDF File | file | 1 | No | — | No | extensions: pdf | Base field | `file_directory: certificates/issued`; managed file |
| 9 | `field_issued_at` | Issued At | created (timestamp) | 1 | No | auto | No | — | Base field | Unix timestamp set on save |
| 10 | `field_expiry_date` | Expiry Date | datetime (date-only declared in PHP) | 1 | No | — | No | — | Base field | **BUG:** stored as `varchar(20)` in DB, not Unix int — inconsistent with other date fields. See known issue. |
| 11 | `field_revoked` | Revoked | boolean | 1 | No | FALSE | No | — | Base field | Set to TRUE via RevokeCertificateForm; added by `lms_certificate_update_10001` |
| 12 | `field_revoke_reason` | Revoke Reason | string (255) | 1 | No | — | No | — | Base field | Populated on revocation; added by `lms_certificate_update_10001` |

#### DB table: lms_user_certificate (as-built, AUTO_INCREMENT ≈ 52, ~51 rows)

```
id                          int NOT NULL AUTO_INCREMENT  PK
uuid                        varchar(128) UNIQUE
uid                         int unsigned   (FK → users_field_data.uid)
field_template              int unsigned   (FK → lms_certificate_template.id)
field_quiz_result           int unsigned   (FK → lms_quiz_result.id, nullable)
field_course                int unsigned   (FK → lms_course.id)
field_certificate_number    varchar(32)    unique cert code
field_pdf_file__target_id   int unsigned   (FK → file_managed)
field_pdf_file__display     tinyint
field_pdf_file__description text
field_issued_at             int            Unix timestamp
field_expiry_date           varchar(20)    ← BUG: declared datetime, stored as varchar
field_revoked               tinyint
field_revoke_reason         varchar(255)
```

### Known bug — field_expiry_date type mismatch

`field_expiry_date` is declared as `datetime` (date-only) in the PHP entity definition but the MariaDB column is `varchar(20)`. This is inconsistent with all other date/timestamp fields in the system (which use Unix int columns). Reading/writing date values via Drupal's datetime field type against a varchar column will produce unpredictable behavior depending on PHP/Drupal datetime handling. Tracked as a known issue.

**Risk:** date comparison queries and range filters on `field_expiry_date` will not work correctly while the column is varchar.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK lookup |
| uuid | `uuid` | UNIQUE | UUID deduplication |
| uid | `uid` | B-Tree | Load user's certificates |
| field_course | `field_course` | B-Tree | Filter by course |
| field_template | `field_template` | B-Tree | Filter by template |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | No cascade; certificate remains if user deleted |
| `lms_certificate_template` | M:1 | `field_template` | No cascade |
| `lms_quiz_result` | M:1 | `field_quiz_result` | No cascade; nullable |
| `lms_course` | M:1 | `field_course` | No cascade |
| `file` | M:1 | `field_pdf_file__target_id` | Drupal file usage tracking |

### Issuance lifecycle

```
QuizPassedEvent dispatched
        ↓
CertificateEventListener::onQuizPassed()
        ↓
Traverse: Quiz → Lesson (field_quiz) → Chapter (field_lessons) → Course (field_chapters)
        ↓
CertificateManager::checkAndIssueCertificate(user, course, quiz_result)
  - Checks for existing certificate (no double-issue)
  - Selects template (course-specific or first active)
  - Generates 10-char uppercase certificate code
  - CertificateGenerator::generate() → mPDF → managed PDF file
  - Saves lms_user_certificate entity
        ↓
lms_user_certificate row created (field_revoked = FALSE)
        ↓
[admin] RevokeCertificateForm → field_revoked = TRUE, field_revoke_reason set
```

### Business rules

- Auto-issued on `QuizPassedEvent`; certificate is course-level (not per-quiz).
- `CertificateManager` prevents double-issuance: if a non-revoked certificate already exists for `(uid, course)`, no new certificate is created.
- `field_revoked = TRUE` → public verification page at `/certificate/verify` displays "Certificate Revoked".
- Admin can regenerate a non-revoked certificate via `/workspace/certificates/{id}/regenerate` (re-runs PDF generation, keeps same `field_certificate_number`).
- Revocation via `/admin/lms/certificates/{id}/revoke` (requires `revoke any certificate` permission).
- mPDF library is optional: `CertificateGenerator::generate()` checks `class_exists('\Mpdf\Mpdf')` and returns NULL gracefully if missing (logs error, no entity created).
- `field_expiry_date` has no automatic expiry enforcement in code — the varchar type bug means date comparison is unreliable. Manual admin check only.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/workspace/certificates` (admin dashboard: stats + list of 50 most recent) |
| Editor role / permission | `view any certificate`, `revoke any certificate`, `issue any certificate` (custom); `administer lms_course` |
| Form display | No create/edit form; managed programmatically. Revoke via `RevokeCertificateForm` (ConfirmFormBase). |
| View displays / view modes | `default` (EntityViewBuilder) |
| Revision / moderation | Not revisionable |
| Translation | N/A |
| Preview | N/A (certificates are generated PDFs, not previewed in-browser as entities) |
| Cache invalidation | `entity:lms_user_certificate:{id}` cache tag |
| Smoke verification | Trigger a quiz pass → check `/workspace/certificates` for new row → download PDF → verify `field_certificate_number` on public verification page |

---

## Cross-references

- Owner module: [`../C05-modules/lms_certificate.md`](../C05-modules/lms_certificate.md)
- Quiz pass event source: [`../C04-entities/02-assessment-entities.md`](02-assessment-entities.md) (lms_quiz_result)
- Course template assignment: [`../C04-entities/01-content-entities.md`](01-content-entities.md) (lms_course.field_certificate_template)
- Entity-module map: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
