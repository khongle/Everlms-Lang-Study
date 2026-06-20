---
page: Content tools
drupal_concept: Form API + batch
mode: as-built
owner_module: lms_content_tools
route_group: Manage
route_name: lms_content_tools.dashboard
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Content tools

## Purpose
Seed/import/clean demo content at `/workspace/content-tools`: load seed input, import generated JSON, wipe generated content (F-023). The Content Tools standard module UI.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/content-tools` (seed-input, clean) |
| Route name | `lms_content_tools.dashboard` |
| Drupal page primitive | Form API + Batch API |
| Owner module | lms_content_tools |
| Access | administrator |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Actions | seed-input, import (`.agent/generated/*.json`), clean |
| Source | `SeedInputForm` → `ContentToolsManager`; mirrors Drush `lms:import`/`lms:clean` |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Import status/counts | content_tools service | administer | — | import → counts (40 courses) |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Tool labels | admin UI chrome | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | tool buttons + status |
| Empty | nothing seeded → import CTA |
| Access denied | non-admin → 403 |
| Validation error | bad seed JSON → error |
| Loading | batch progress |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Seed/import/clean | done | matches Drush commands |
