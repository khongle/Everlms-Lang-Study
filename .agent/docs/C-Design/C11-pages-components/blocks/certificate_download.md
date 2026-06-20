---
block: Certificate download
drupal_concept: block plugin
owner_module: lms_certificate
region: content
block_type: plugin
adequacy: PASS
---

# C11 Block - Certificate download

## Purpose
Lists a learner's earned certificates with download links; on my-certificates and dashboard.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | certificate_download |
| Drupal primitive | block plugin |
| Owner module | lms_certificate |
| Placed on | P023, P017 |
| Cache | contexts: `user`; tags: cert |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | `lms_user_certificate.field_pdf_file` (own) |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | list + download button |
| Access | own certificates only |
| Responsive | yes |
| Editing | computed (issued) |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | `/my-certificates` |

## Tests / Checks
- Only own certificates; download serves the PDF; revoked certs flagged.
