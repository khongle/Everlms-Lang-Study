# ISSUE-018 — lesson/chapter Field Naming Inconsistency: Demo Generator Uses lesson.field_chapter; Importer Uses chapter.field_lessons; lms_lesson Hook References field_chapter Not in baseFieldDefinitions

| Field | Value |
|---|---|
| ID | `ISSUE-018` |
| Title | Schema inconsistency — LmsDemoContentCommand uses lesson.field_chapter (child→parent); ContentToolsCommands importer uses chapter.field_lessons (parent→child); lms_lesson hook references field_chapter absent from entity baseFieldDefinitions |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | `lms:generate-demo` Drush command; `everlms:import` Drush command; lesson-related hooks in `lms_lesson.module` |
| Owning Epic | E-01 (Content) — |
| Owning Story | [`lms_lesson`](../../../D-Development/D03-stories/lms_lesson.md) |
| Owning module(s) | `lms_base`, `lms_content_tools`, `lms_lesson`, `lms_chapter` |

## Summary

The lesson–chapter relationship is modelled inconsistently across three codebases, creating a data-integrity ambiguity and a missing-field reference bug:

### Inconsistency 1 — Relationship direction mismatch

| Code path | Direction | Field used |
|---|---|---|
| `LmsDemoContentCommand::generate()` (in `lms_base`) | Child → Parent: lesson stores reference to chapter | `lms_lesson.field_chapter` (entity_reference → lms_chapter) |
| `ContentToolsCommands::importCourse()` (in `lms_content_tools`) | Parent → Child: chapter stores reference to lessons | `lms_chapter.field_lessons` (entity_reference, UNLIMITED → lms_lesson) |
| `lms_chapter__field_lessons` DB table | Parent → Child | Junction table exists with data (`YES` per db-schema.md) |

The database has a `lms_chapter__field_lessons` junction table containing data, which confirms the **parent→child direction** (`chapter.field_lessons`) is the canonical as-built schema. The `LmsDemoContentCommand` writes `lms_lesson.field_chapter` — but there is no evidence that `field_chapter` is declared in `lms_lesson.baseFieldDefinitions()`. If `field_chapter` does not exist as a proper base field, the demo command may silently fail to save the reference, or may rely on a stale field storage definition left over from an earlier schema version.

The `db-schema.md` facts file lists no `lms_lesson__field_chapter` junction table (there is a `lms_chapter__field_lessons` table but no inverse), suggesting the child→parent direction is not the active stored field.

### Inconsistency 2 — lms_lesson hook references field_chapter absent from baseFieldDefinitions

The facts base notes: "Note: Lesson `field_chapter` is referenced from lesson; chapter `field_course` is referenced from chapter. This is the INVERSE of the pattern used by the importer in lms_content_tools (which uses parent→child references)." A hook in `lms_lesson.module` also references `field_chapter` — but if this field is not declared in `lms_lesson.baseFieldDefinitions()`, the hook will throw a `FieldException` or silently return empty when the field is accessed.

This means content created via `lms:generate-demo` may have the chapter reference stored in a field that does not exist in the active entity schema, creating orphan demo data that is inconsistent with importer-created content.

## Reproduction

1. Run `drush lms-gen --courses=1 --chapters=2 --lessons=3` — creates demo content via `LmsDemoContentCommand`.
2. Inspect created lessons: `drush entity:load lms_lesson {id}` and check for `field_chapter` value.
3. Inspect created chapters: check for `field_lessons` values — likely empty (lessons set chapter reference, not the other way).
4. Compare with importer: run `drush lms-import` with a test course JSON; inspect resulting chapter for `field_lessons` — should contain lesson IDs.
5. Attempt to load a lesson view in the learner portal — if the curriculum display relies on `chapter.field_lessons`, demo-generated lessons may not appear in the curriculum.

Confirm missing field:
```bash
grep -n "field_chapter" web/modules/custom/lms_lesson/src/Entity/Lesson.php
# If not found in baseFieldDefinitions, the field does not exist on lms_lesson
grep -n "field_chapter" web/modules/custom/lms_lesson/lms_lesson.module
# If found here, the hook references a non-existent field
```

## Root Cause

The `lms_lesson` module was designed with a child→parent `field_chapter` reference (lesson knows its chapter). The `lms_chapter` module was designed with a parent→child `field_lessons` reference (chapter knows its lessons). Both patterns were implemented at different points in development. The `lms_content_tools` importer and the active database schema converged on the **parent→child** direction (`chapter.field_lessons`). However, `LmsDemoContentCommand` in `lms_base` was written (or not updated) to use the child→parent pattern. The `lms_lesson.module` hook references the child→parent `field_chapter` field, which may be a vestige of the earlier design.

Source files:
- `web/modules/custom/lms_base/src/Commands/LmsDemoContentCommand.php` — uses `field_chapter` on lesson
- `web/modules/custom/lms_content_tools/src/Commands/ContentToolsCommands.php` — uses `field_lessons` on chapter
- `web/modules/custom/lms_lesson/lms_lesson.module` — hook references `field_chapter`
- `web/modules/custom/lms_lesson/src/Entity/Lesson.php` — `baseFieldDefinitions()` — verify whether `field_chapter` is declared

## Resolution (filled at RESOLVED)

1. **Establish canonical direction:** the parent→child direction (`chapter.field_lessons`) is confirmed by the active database schema and the importer. This is the canonical pattern.

2. **Update `LmsDemoContentCommand`:**
   - After creating each lesson, use `$chapter->get('field_lessons')->appendItem($lesson->id()); $chapter->save()` instead of `$lesson->set('field_chapter', $chapter->id()); $lesson->save()`.

3. **Audit `lms_lesson.module` hook:**
   - Remove or update any reference to `field_chapter` on `lms_lesson` entities in `hook_*` implementations.
   - If `field_chapter` is genuinely needed as a convenience back-reference, declare it properly in `lms_lesson.baseFieldDefinitions()` and ensure it is kept in sync with `chapter.field_lessons`.

4. **Verify `lms_lesson.baseFieldDefinitions()`:**
   - If `field_chapter` is not declared there, confirm the field storage does not exist in the database (`SHOW TABLES LIKE 'lms_lesson__field_chapter'`).
   - If the column does not exist, remove all references to `field_chapter` on `lms_lesson` from module hooks.

5. Add a data-integrity test: after `lms:generate-demo`, assert that created lessons appear in the `field_lessons` reference on their parent chapter.

## Trace links

- **Story:** `D-Development/D03-stories/lms_lesson.md`; `D-Development/D03-stories/lms_base.md`
- **C05 module specs:** `C-Design/C05-modules/lms_lesson.md`, `C-Design/C05-modules/lms_chapter.md`
- **Facts base:** `modules-foundation.md` §lms_base §LmsDemoContentCommand — "Note: Lesson `field_chapter` is referenced from lesson; chapter `field_course` is referenced from chapter. This is the INVERSE of the pattern used by the importer in lms_content_tools (which uses parent→child references). May be a schema inconsistency."
- **DB schema:** `db-schema.md` — `lms_chapter__field_lessons` (junction table, data present); no `lms_lesson__field_chapter` table listed
- **K04:** Reusable rule — when a relationship exists between two entities, define and enforce a single canonical direction (parent→child or child→parent); all code paths (demo generators, importers, hooks, templates) must use the same direction; document the canonical direction in the C04 entity spec.
