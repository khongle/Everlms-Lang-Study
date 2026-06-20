# C10-prototypes - Prototype and Mockup Index

**Last updated:** 2026-06-21

EverLMS is a **rebuild of a running system** — the **live site** (`https://everlms.ddev.site`) is the authoritative visual reference, so no separate generated HTML/PNG prototype is maintained. **C11 is canonical** (the contract); this folder records where the visual evidence lives.

## HTML prototypes
| Version | Source | Notes |
| ------- | ------ | ----- |
| live | running DDEV site (`ddev launch`) | the as-built visual reference for every page |
| theme | `web/themes/custom/lms_theme/` + 15 homepage skins | the rendered design system |

No `html/v3/` generated prototype is produced for this rebuild (the live site supersedes it). If a future greenfield feature needs exploration, record it as `PROTO-NNN-<slug>.md` here, then promote the chosen design into C11.

## Mockups
None maintained separately; screenshots can be captured from the live site per page (mirrors C11 `P###`).

## Generation
- Not generated. To refresh visual reference: `ddev launch` and capture per-page screenshots into `_screens/` if needed.
- Each [C11 page](../C11-pages-components/pages/_index.md) marks `prototype_type: live-site`.
