---
id: WF-007-003
workflow: WF-007
title: Web registration confirmation message is too subtle
severity: medium
category: ui
status: done
roles: visitor
source: visual QA
found: 2026-06-30
---

# WF-007-003 - Web registration confirmation message is too subtle

## Problem
After submitting `/user/register`, Drupal redirects to the homepage and renders
the confirmation as a small line of text near the top of the page:
`A welcome message with further instructions has been sent to your email address.`

The message uses `role="contentinfo"` but does not include the normal
`.messages--status` class, so the existing alert styles do not apply.

## Evidence
- Screenshot:
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/007-user-registration-and-onboarding/003-visitor-web-register-submitted-confirmation.png`
- DOM after submit:
  `.lms-messages-wrapper [role="contentinfo"]` without `.messages--status`.

## Impact
Visitors can miss the only post-registration instruction, especially because the
homepage hero starts immediately below it.

## Expected
The registration confirmation should render as a visible success alert with
clear spacing, contrast, and an icon, consistent with other Drupal status
messages.

## Fix Plan
- Add a global fallback style for `.lms-messages-wrapper [role="contentinfo"]`.
- Keep the rule scoped to message wrappers so normal page content is untouched.
- Retest web registration and capture the confirmation state again.

## Resolution
- Added a scoped fallback style in `ui-fixes-r2.css` for
  `.lms-messages-wrapper [role="contentinfo"]`.
- The rule gives registration confirmations the same success-alert treatment as
  standard Drupal status messages.

## Verification
- Retested `/user/register` through the real web form.
- Screenshot `003-visitor-web-register-submitted-confirmation.png` now shows a
  clear green success alert with spacing, icon, and readable contrast.
- DB verification: `wf007web20260630170211@example.test` is active and has the
  `student` role.
