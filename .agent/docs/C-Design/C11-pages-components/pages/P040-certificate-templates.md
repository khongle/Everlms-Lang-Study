---
page: Certificate templates
drupal_concept: entity form + preview
mode: as-built
owner_module: lms_certificate
route_group: Manage
route_name: lms_certificate.templates
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Certificate templates

## Purpose
Editor-manage certificate layouts at `/workspace/certificate-templates`: design, logo/signature/seal, colors, orientation; preview. The editorial anchor for F-007.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/certificate-templates` (add, `/{template}/edit`, `/{template}/preview`) |
| Route name | `lms_certificate.templates` |
| Drupal page primitive | entity form + mPDF preview |
| Owner module | lms_certificate |
| Access | content_editor / manager |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Fields | body, design/layout/orientation/color_scheme, background/logo/signature/seal images |
| Preview | render to PDF preview before save |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Template fields | `lms_certificate_template` | manage cert templates | template tag | edit → new certs use layout |
| Preview PDF | mPDF render | editor | — | preview shows changes |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | layout/branding in entity fields | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | template list + edit form |
| Empty | no templates → create CTA |
| Access denied | non-editor → 403 |
| Validation error | required fields → inline |
| Loading | preview render |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Template editor + preview | done | re-render via regenerate script |
