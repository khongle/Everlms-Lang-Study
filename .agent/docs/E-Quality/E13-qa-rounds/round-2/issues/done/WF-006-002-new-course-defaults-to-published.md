---
id: WF-006-002
workflow: WF-006
title: New courses default to Published, contradicting draft-first authoring
severity: medium
category: functionality
status: done
roles: instructor, content_editor
source: round-2 real-session QA
found: 2026-07-11
---

# WF-006-002 - Course `status` defaulted to published on create

## Problem
The `lms_course` `status` base field used `->setDefaultValue(TRUE)`, so a course
created from `/workspace/courses/create` was **immediately published** to the
public catalog (verified: course 139 saved as `status = 1` with the Published
box untouched). This contradicts C12 WF-006 ("draft `status = 0`" default; step 5
"saves as draft by leaving `status` unchecked"; step 9 publish on reopen) and the
form's own "Save Draft" affordance.

## Expected
A new course starts as a draft (`status = 0`); the author publishes explicitly by
ticking Published. Drafts are hidden from `/courses`; publishing makes them
visible.

## Evidence
- Course 139 created via the form → `status = 1` (before fix).

## Fix (implemented)
- `Course::baseFieldDefinitions()` status field → `->setDefaultValue(FALSE)`.

## Verification
- New course 140 created via the form → `status = 0` (draft), owner uid 56.
- Draft 140 absent from anonymous `/courses?search=WF006-R2`; after publishing
  (Published checkbox on the Course Collection tab → status 1) it appears.
- Screens `05-anon-catalog-draft-hidden.png`, `08-anon-catalog-published-visible.png`.

## Resolution (2026-07-11)
Course status default -> FALSE; new courses are drafts, hidden from catalog until published. Verified via course 140.
