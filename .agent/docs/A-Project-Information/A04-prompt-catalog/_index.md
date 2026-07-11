# A04-prompt-catalog - Prompt Catalog

**Last updated:** 2026-06-21

The lifecycle prompts that drive work on EverLMS. IDs are `P-{NAME}`; phases use the 7-phase model (see [A06-workflow-primer](../A06-workflow-primer/_index.md)). Canonical prompt text lives in the guideline package.

| ID | Phase | Purpose | Source |
| -- | ----- | ------- | ------ |
| P-IDEA | 1 Ideation/Requirements | Produce/refresh B01/B02/B03 + A02 overview (as-built capture for EverLMS) | `A10-lifecycle-prompts.md#ideation` |
| P-ARCHITECTURE | 2 Architecture | Produce B04-B06 + C01-C16 + S-layer + adequacy pass 1 | `A10-lifecycle-prompts.md#architecture` |
| P-FRAMEWORK_SETUP | 3 Framework setup | Scaffold A01-CLAUDE/A03/D01/D02/K + the 3 standard modules | `A10-lifecycle-prompts.md#framework-setup` |
| P-DEVELOPMENT | 4 Development | Per-story development loop + G1-G4 gates | `A10-lifecycle-prompts.md#development` |
| P-IMPLEMENTATION_RUN | 4 Development | Chạy implementation từng task (code + gate loop) theo D09/D04 | `A10-lifecycle-prompts.md#implementation-run` |
| P-QUALITY | 5 Quality | E-layer: tests, traceability, NFR, coverage | `A10-lifecycle-prompts.md#quality` |
| P-RELEASE | 6 Release | F01-F04 + H verification + flag posture + go/no-go | `A10-lifecycle-prompts.md#release` |
| P-MAINTENANCE | 7 Maintenance | M01 work-type workflows (hotfix/bug-fix/enhancement/refactor/dep-update) | `A10-lifecycle-prompts.md#maintenance` |
| P-POST_RELEASE | 7 Post-release | M04 monitoring; product evolution log M05 | `A10-lifecycle-prompts.md#post-release` |
| P-INCIDENT_RESPONSE | 7 Incident | IC persona: H01 incident log + runbooks H02 | `A10-lifecycle-prompts.md#incident-response` |
| P-FRAMEWORK_RETRO | — Retro | Improve the documentation framework itself | `A10-lifecycle-prompts.md#framework-retro` |

> Project-local working prompts/rules also live in `.agent/rules/` and `.agent/workflows/` (bootstrap, backup, test, refactor, …); the doc-build workflow is `.agent/_LodeStar/00-Guidelines-v6/A08-build-prompt-pack.md`; per-slot prompts in `.agent/_LodeStar/00-Guidelines-v6/A12-layer-prompts/`.
