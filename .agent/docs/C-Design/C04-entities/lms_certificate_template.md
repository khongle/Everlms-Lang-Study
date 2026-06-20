# C04 Entity - lms_certificate_template

**Display name:** Certificate Template
**Owning module(s):** [lms_certificate](../C05-modules/lms_certificate.md)
**Mode:** as-built (Content Entity — editor-managed layout)

## Purpose
The editable layout/design rendered to PDF when a certificate is issued. Branding (logo, signature, seal, colors, orientation) is editor-managed.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | template name |
| field_body | formatted certificate body/copy |
| field_design, field_layout, field_orientation, field_color_scheme | layout/design controls |
| field_background, field_logo, field_signature, field_seal | image assets (target_id + alt/title/dimensions) |
| status | active |

## Relationships
- Referenced by `lms_user_certificate.field_template`.

## Editorial/data management
Content editors/managers edit templates via the template entity form (management UI) — the editorial-usability anchor for certificates. All copy/branding in fields; mPDF renders. Changing a template affects newly-issued certificates (re-render via `regenerate_all_certs.php`). See [F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md).
