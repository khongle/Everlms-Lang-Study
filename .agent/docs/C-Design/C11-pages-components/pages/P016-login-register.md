---
page: Login / Register
drupal_concept: core user forms
mode: as-built
owner_module: user
route_group: Public
route_name: user.login
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Login / Register

## Purpose
Account access at `/user/login` and `/user/register` (auth/util). New web accounts get the `student` role; mobile uses OTP (F-019/WF-007).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/user/login`, `/user/register`, `/user/password` |
| Route name | `user.login` (core) |
| Primitive | core user forms (themed) |
| Access | anonymous |
| Cache | uncacheable (forms) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Auth forms | core user module | — | — | register → student account |

## Page States
Normal: form · Empty: n/a · Access denied: authed → redirect · Validation: bad creds → error · Loading: submit.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Auth | done | mobile OTP via lms_mobile_api |
