---
id: WF-006-001
workflow: WF-006
title: Workspace sidebar hard-codes the role label "Manager" for every user
severity: medium
category: content
status: done
roles: instructor, content_editor, admin
source: round-2 real-session QA
found: 2026-07-11
---

# WF-006-001 - Admin/workspace sidebar always shows "Manager"

## Problem
`lms-admin-sidebar.html.twig` rendered `{{ 'Manager'|t }}` as the profile role
for **all** workspace users. An instructor (`sabine.mueller`) authoring a course
was labelled "Manager"; content editors and admins were too.

## Expected
Show the user's real role: Instructor, Content Editor, Manager, Administrator, …

## Evidence
- `02-instructor-course-create-form-before.png` — sidebar shows "Manager /
  Sabine Müller" for an instructor.

## Fix (implemented)
- `lms_course_preprocess()` injects `current_user_role` = friendly label of the
  user's highest-privilege role.
- Sidebar template uses `{{ current_user_role|default('Member'|t) }}`.

## Verification
- instructor → "Instructor", manager → "Manager", administrator →
  "Administrator" on the workspace sidebar.

## Resolution (2026-07-11)
Added current_user_role to lms_course_preprocess + sidebar template; instructor/manager/admin now labelled correctly.
