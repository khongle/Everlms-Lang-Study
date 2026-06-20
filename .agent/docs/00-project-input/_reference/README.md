# 00-project-input/_reference - Raw Evidence (reference-in-place)

**Last updated:** 2026-06-21

For a **greenfield** project this folder would hold copied-in raw evidence (brief, designs, interviews). EverLMS is a **rebuild of an existing system**, so the raw evidence *is the working repository*. To avoid (a) duplicating a ~21 MB database dump and a full codebase into a shareable docs tree and (b) carrying SENSITIVE artifacts (DB dumps, signing keys, PII) inside `.agent/docs/`, raw evidence is **referenced in place** by its repo-relative path.

The authoritative source index is one level up: [`../README.md`](../README.md) (columns: `Source ID | Path | Type | Status | Contains | Sensitive | Last checked`).

## Integrity rule (evidence is never rewritten)

- The in-repo sources (`web/`, `config/sync/`, `database/`, `app/`, `.agent/generated/`) are the **appeal court** for any disputed fact. They are never edited to fit a doc.
- If an `A-U` doc and a source disagree, the doc records the discrepancy inline (`INFERENCE`/`TODO`) and the source file stays as-is.
- Provenance precedence (rebuild): **code > config export > database > prior drafts**.

## Re-derivation commands (how facts here were obtained)

```bash
# Entity types (PHP 8 attributes)
grep -rl '#\[ContentEntityType\|#\[ConfigEntityType' web/modules/custom/*/src/Entity/

# Config inventory by type
ls config/sync/ | sed -E 's/^([a-z_]+\.[a-z_]+)\..*/\1/' | sort | uniq -c | sort -rn

# Live data-derived counts
ddev drush sqlq "SELECT COUNT(*) FROM lms_course"

# Routes
ddev drush route --format=json
```

> SENSITIVE (do not copy into docs): `database/db.sql`, `database/db_bk_version_1.sql`, `app/APN_AuthKey_*.p8`, `app/file.keystore`, `app/keystore.txt`.
