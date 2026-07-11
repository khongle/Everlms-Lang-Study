---
id: WF-010-005
workflow: WF-010
title: AI provider failure message is styled as a success alert
severity: medium
category: ui
status: done
roles: instructor, content_editor
source: visual QA
found: 2026-06-30
---

# WF-010-005 - Error message styled as success

## Problem
The provider failure path correctly blocks persistence, but the visible message
`AI could not generate a valid course draft` renders as a green success alert
with a check icon.

## Evidence
- Screenshot
  `_screens/010-ai-assisted-content-generation/010-instructor-ai-builder-provider-failure-no-save.png`
  shows a provider failure using success styling.
- `ui-fixes-r2.css` styles generic `[role="contentinfo"]` messages as status
  without preserving Drupal error/warning variants.

## Impact
Editors may misread a failed generation as successful, then wait for a draft
that was never created.

## Expected
Provider failures render as an error alert with error color/icon while normal
registration/status confirmations remain green.

## Fix Plan
- Add later scoped CSS for `.messages--error` and `.messages--warning`.
- Retest WF-010 failure screenshot.

## Resolution
- `ui-fixes-r2.css` now preserves error and warning message variants after the
  generic status-message rule.
- Error messages use red alert color and error icon.

## Verification
- Screenshot `010-instructor-ai-builder-provider-failure-no-save.png` shows the
  Pollinations unsupported-provider failure as a red error alert.
