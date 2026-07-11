---
id: WF-003-001
workflow: WF-003
title: Public certificate verifier (and other surfaces) shows login username instead of full name
severity: medium
category: content-privacy
status: done
roles: anonymous, student, instructor
source: round-2 real-session QA
found: 2026-07-11
---

# WF-003-001 - "maria.santos" shown where "Maria Santos" is expected

## Problem
The public certificate verifier shows **Recipient Name: `maria.santos`** — the
raw account/login name — rather than the learner's real name. `getDisplayName()`
returned the account name everywhere because no name-format hook existed. This
leaks login usernames on a public page and looks unprofessional; it also shows on
the WF-002 grading form ("Learner: maria.santos").

## Expected
Show the person's full name ("Maria Santos") from `field_first_name` +
`field_last_name` on the verifier, grading form, profile chips, etc.

## Evidence
- `04-anonymous-verify-result-valid.png` (before) — Recipient Name "maria.santos".

## Fix (implemented)
- `lms_base_user_format_name_alter()` sets the formatted name to
  `field_first_name field_last_name` when present. This corrects every
  `getDisplayName()` surface at once.

## Verification
- Public verifier now shows **"Maria Santos"**; grade form shows the full name.

## Resolution (2026-07-11)
Added lms_base_user_format_name_alter(); verifier + grade form now show 'Maria Santos'.
