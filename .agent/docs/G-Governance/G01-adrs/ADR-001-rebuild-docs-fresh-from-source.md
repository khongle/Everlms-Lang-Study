# ADR-001 - Rebuild .agent/docs fresh from source

**Status:** Accepted · **Date:** 2026-06-21

## Context
Two prior drafts (`docs-draft`, `docs-draft-1`) predate the updated `00-Guidelines` (folder-per-slot, full A–U+Z, new C09/C14-C16/D09 slots) and carried stale status labels. EverLMS is an existing, running system.

## Decision
Rebuild `.agent/docs/` from scratch into the full A–U+Z tree, using **live code > config export > database** as the source of truth (drafts as reference only). Counts are data-derived (drush/DB).

## Consequences
- Accurate, current-guideline-compliant tree; honest as-built status.
- Drafts demoted to `00-project-input` references (SRC-14/15).
- Larger up-front effort than synthesising drafts.
