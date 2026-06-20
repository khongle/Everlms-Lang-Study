# B05 — Acceptance Criteria

> Testable Given/When/Then conditions for each B03 feature. One file per feature. AC IDs are globally unique across all files.
>
> **Status key:** `as-built` = implemented in current code; `blocked — not implemented` = the criterion cannot pass because the implementing code does not exist.

## Files

| File | Feature | AC range |
|------|---------|----------|
| [AC-01-course-management.md](AC-01-course-management.md) | F-001 Course Management | AC-001 – AC-012 |
| [AC-02-assessment-system.md](AC-02-assessment-system.md) | F-002 Assessment System | AC-013 – AC-025 |
| [AC-03-learning-progress.md](AC-03-learning-progress.md) | F-003 Learning Progress | AC-026 – AC-035 |
| [AC-04-order-membership.md](AC-04-order-membership.md) | F-004 Order & Membership | AC-036 – AC-048 |
| [AC-05-content-media.md](AC-05-content-media.md) | F-005 Content & Media | AC-049 – AC-058 |
| [AC-06-certification-badges.md](AC-06-certification-badges.md) | F-006 Certification & Badges | AC-059 – AC-068 |

## AC ID allocation

AC IDs are globally unique (not reset per feature). Cross-feature traceability lives in `E05-traceability.md`.

## Vocabulary note

- **as-built** — criterion is testable against current code.
- **blocked — not implemented** — prerequisite code does not exist; criterion cannot pass until that code is written.
- **stub / dead-code** — implementing module exists but behaviour is absent or broken; criterion fails by design.
