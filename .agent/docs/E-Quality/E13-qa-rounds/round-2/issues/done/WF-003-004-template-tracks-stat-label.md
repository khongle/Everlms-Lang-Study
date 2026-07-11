---
id: WF-003-004
workflow: WF-003
title: My Certificates stat labelled "Template tracks" is jargon to learners
severity: low
category: content
status: done
roles: student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-003-004 - "Template tracks" stat label is unclear on the learner page

## Problem
The `/my-certificates` summary shows a stat "Template tracks" (count of distinct
certificate templates). This is internal jargon that means nothing to a learner.

## Evidence
- `01-student-my-certificates.png` — "1 Template tracks".

## Fix (implemented)
- Relabelled to "Credential types".

## Verification
- Stat now reads "Credential types".

## Resolution (2026-07-11)
Relabelled stat to 'Credential types'.
