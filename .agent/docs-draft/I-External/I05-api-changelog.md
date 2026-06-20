# I05 — API Changelog

> Append-only. Newest first. Every breaking change requires a deprecation window (minimum 90 days).
> This file tracks the public-facing API surface (I01 endpoints + I03 webhook contracts).

---

## Deprecation Policy

- **Minor deprecation:** 90 days notice before removal.
- **Major deprecation:** 180 days notice before removal.
- **Breaking removal:** Only at scheduled major version bump (`v2`, `v3`, ...).
- All deprecations must have a migration guide linked.

---

## Entries

### 2026-04-16 — v0: Certificate Verify Endpoint (shipped as HTML form)

- **Type:** Additive (initial)
- **Summary:** Certificate verification is live at `/certificate/verify` as a server-rendered Drupal Form (HTML). No JSON API yet. Users submit a certificate code and receive an HTML response indicating validity.
- **Breaking?** No (initial entry).
- **JSON API:** Planned at `GET /api/v1/certificate/verify/{code}` — see I01. Not yet shipped.
- **Affected versions:** v0 (pre-API era).

---

### Future entries will be appended above this line.

---

## Entry Template

```markdown
### {YYYY-MM-DD} — {short title}

- **Type:** Additive / Breaking / Deprecation Notice / Removal
- **Summary:** {what changed and why}
- **Breaking?** Yes / No
- **Migration:** {link to migration guide or "N/A"}
- **Affected versions:** {list}
- **Timeline:**
  - Announced: {date}
  - Deprecated in: {version} (if applicable)
  - Removed in: {version} (if applicable)
```
