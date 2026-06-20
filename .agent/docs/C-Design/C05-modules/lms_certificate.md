# C05 Module - lms_certificate

**Name:** Certificates (PDF) · **Phase:** 5 · **Epic:** [E05](../../D-Development/D02-epics/E05-certification.md) · **Status:** done

## Responsibility
Owns certificate templates + issued certificates. Subscribes to `QuizPassedEvent` and renders the course's template to PDF (mPDF), creating an idempotent `lms_user_certificate`. ~8 routes.

## Owned / related entities
- [lms_certificate_template](../C04-entities/lms_certificate_template.md) · [lms_user_certificate](../C04-entities/lms_user_certificate.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md) | Certificate issuance |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-003](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md) | Certificate issuance |

## Integrations / APIs (B06 / C06)
- mPDF (PDF rendering); email link via [INT-008](../C06-apis/INT-008-smtp-email.md).

## Backing pages
| Label | URL |
| ----- | --- |
| My certificates | `/my-certificates` |
| Certificate view/download | `/certificate/{certificate}` |
| Template manage | `/manage/certificate-templates` |

## Cross references
- Trigger source [lms_quiz](lms_quiz.md); [WF-003](../C12-workflows/WF-003-certificate-issuance.md).

## Dependencies
- lms_base, lms_quiz.

## Implementation notes
- Event subscriber on `QuizPassedEvent`; idempotency check (one cert per learner-course). Drush `lms:mock-certs` + `scripts/regenerate_all_certs.php` for bulk re-render. ~24 certs live.
