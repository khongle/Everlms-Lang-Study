# Task: User Account Merge Tool

**ID:** TASK-132
**Unit:** U-010 (lms_user / admin tools)
**Group:** G-USER-MGMT (extension)
**Priority:** P3
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Provide an admin tool to merge two user accounts into one, consolidating all enrollments, progress records, quiz results, certificates, and orders from the secondary account into the primary account. Common scenario: a learner registers twice with different email addresses. After merge, the secondary account is anonymised. All actions are written to an audit log.

## Context

- Module: admin tool in `lms_base` or `lms_user_tools`
- "Primary" account: the one that survives and retains all data
- "Secondary" account: data is migrated to primary, then account is anonymised (not deleted, to preserve referential integrity)

## Acceptance Criteria

- [ ] Admin form at `/admin/lms/users/merge`: select primary user (autocomplete) and secondary user (autocomplete)
- [ ] Merge preview: lists all data that will be migrated (X enrollments, Y certificates, Z orders, etc.)
- [ ] Confirmation step with explicit checkbox: "I understand this cannot be undone without restore from backup"
- [ ] Enrollments from secondary copied to primary (skip duplicates)
- [ ] Progress records from secondary merged into primary (take the more advanced progress where duplicates exist)
- [ ] Quiz results from secondary reassigned to primary user ID
- [ ] Certificates from secondary reassigned to primary user ID; original secondary user ID stored in metadata
- [ ] Orders from secondary reassigned to primary; billing email updated if requested
- [ ] After migration: secondary account anonymised (`name = "merged-{id}"`, `mail = "merged-{id}@deleted.local"`, `status = 0`)
- [ ] Audit log entry: admin UID, primary UID, secondary UID, timestamp, entity counts migrated
- [ ] Merge cannot be reversed via UI; restoration requires database restore

## Technical Notes

- New service: `web/modules/custom/lms_base/src/UserMergeService.php`
- Methods: `preview(User $primary, User $secondary): MergePreview` and `merge(User $primary, User $secondary): MergeResult`
- Transaction wrapper: run all entity reassignments inside a single `\Drupal\Core\Database\Connection::transactional()` call
- Entity reassignment: use `entityQuery` on each entity type filtered by `uid = secondary_uid`; bulk update via storage `->save()`
- Audit log: write to `watchdog` (dblog) with `type = 'lms_user_merge'` and structured data in `variables`
- Progress conflict resolution: compare `field_progress_percentage`; keep the higher value

## Dependencies

- TASK-027 (user CRUD and roles)
- TASK-062 (GDPR consent — ensure anonymisation is GDPR-compliant)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
