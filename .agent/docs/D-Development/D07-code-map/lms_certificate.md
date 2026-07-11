# D07 Code Map - lms_certificate

`web/modules/custom/lms_certificate/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/{CertificateTemplate,UserCertificate}.php` |
| Controller | `src/Controller/CertificateAdminController.php` (admin dashboard, template CRUD, regenerate PDF) |
| Forms | `src/Form/{CertificateTemplateForm,RevokeCertificateForm,VerificationForm}.php` |
| List Builder | `src/CertificateTemplateListBuilder.php` |
| Service | `src/Service/{CertificateGenerator,CertificateManager}.php` (PDF render + issuance logic) |
| Event Subscriber | `src/EventSubscriber/CertificateEventListener.php` (listens for `QuizPassedEvent` from lms_quiz → auto-issue) |
| Routes | `lms_certificate.routing.yml` (~9: `/certificate/verify`, `/workspace/certificates/*` revoke/regenerate, admin dashboard + templates CRUD) |
| Config | `lms_certificate.permissions.yml`, `lms_certificate.install` (entity update hooks) |
| Templates | `templates/*.html.twig` (certificate-pdf, verification-result, admin certificates/templates list) |
| JS/CSS | `css/verification.css` via `lms_certificate.libraries.yml` |
| Script | `scripts/fix_certificates.php` (one-off drush script backfilling certificates for existing courses) |

Story: [E05S01](../D03-stories/E05S01-lms_certificate.md) · Module: [C05](../../C-Design/C05-modules/lms_certificate.md)
