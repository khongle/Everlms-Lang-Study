# Task: User Import Templates

**ID:** TASK-237
**Unit:** U-050 (user tools)
**Group:** G-MIGRATION
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Provide downloadable CSV templates for bulk user import scenarios. Three templates: (1) bulk corporate import (employees of an org), (2) instructor onboarding batch, (3) student cohort enrollment. Each template includes correct column headers, example rows, and a companion field-mapping document. Uses `lms_user_tools` module (same module as TASK-131 bulk import).

## Context
- TASK-131 implemented the bulk user import processing; this task adds the downloadable template files and documentation to that existing flow.
- TASK-238 (org entity) defines the Org context for corporate import; template must include `org_id` column.

## Acceptance Criteria
- [ ] Three CSV templates downloadable from admin UI at `/admin/lms/users/import-templates`
- [ ] Template 1 — Corporate bulk import: columns `email, first_name, last_name, org_id, department, role (student|instructor), send_welcome_email (yes|no)` with 3 example rows
- [ ] Template 2 — Instructor onboarding: columns `email, first_name, last_name, bio, specialty_category, payout_email` with 2 example rows
- [ ] Template 3 — Student cohort: columns `email, first_name, last_name, cohort_id, enrollment_date` with 5 example rows
- [ ] Each template download triggers correct `Content-Disposition: attachment` header with filename `lms-import-{type}.csv`
- [ ] Admin page also links to an inline field mapping reference table (HTML, not a separate file) documenting each column, its data type, allowed values, and whether it is required
- [ ] Templates validate cleanly when uploaded to the existing TASK-131 bulk importer (column names must match exactly what TASK-131 expects)

## Technical Notes
- Module: `lms_user_tools`
- Route: `lms_user_tools.import_templates` → `ImportTemplatesController::download(string $type)` — generates CSV string from hardcoded schema arrays and streams it
- No file system writes: generate CSV on the fly using PHP's `fputcsv` into a `php://output` stream
- Field mapping reference table: rendered in `lms_user_tools/templates/admin/import-templates.html.twig`
- `$type` param validates against allowlist: `['corporate', 'instructor', 'cohort']`; 404 on unknown type
- Column names for Template 1 must align with TASK-131 field mapping config; if TASK-131 field names differ, map in `ImportTemplatesController`

## Dependencies
- TASK-131 (bulk user import — column names must match its expected headers)
- TASK-238 (org entity — `org_id` column requires Org entity to exist for validation)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: CSV output columns match spec; functional: all three downloads return valid CSV with correct headers)
- [ ] No new debt introduced
