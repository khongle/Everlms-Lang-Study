# F02 — Release Checklist: Index

> Per-release checklist folder. One file per release or internal milestone.
> Templates cover pre-release gates and post-release verification.

---

## Templates

| File | Purpose |
|------|---------|
| [TEMPLATE-pre-release.md](TEMPLATE-pre-release.md) | Pre-release checklist: gates G1-G4, CVE scan, config diff, DB backup, comms |
| [TEMPLATE-post-release.md](TEMPLATE-post-release.md) | Post-release checklist: smoke tests, error baseline, alert revert, retro schedule |

## Release Records

| File | Version | Type | Date | Status |
|------|---------|------|------|--------|
| [0.1.0-scaffold.md](0.1.0-scaffold.md) | v0.1.0 | Internal milestone | 2026-04-15 | Pre-prod (no public release) |

---

## How to Use

1. Copy the appropriate template(s) for your release
2. Rename: `{version}-{descriptor}.md` (e.g., `1.0.0-rc1.md`, `0.2.0-feature-x.md`)
3. Fill in the checklist during the release process
4. Archive completed files here — do not delete

**Rule:** Every production deploy and named internal milestone must have an F02 record, even if abbreviated.

---

## When to Use Each Template

| Template | Use When |
|----------|----------|
| Pre-release | Before any deploy to production or staging for release candidate |
| Post-release | Immediately after deploy; complete within 24 hours |
| Both | Default for all production releases |
| Pre-release only | Internal milestone with no deploy (e.g., scaffold marker, design freeze) |

---

*Deployment config: F01-deployment.md. Changelog: F03-changelog.md. Monitoring: M04-post-release-monitoring.md.*
