# K04-lessons-learned - Lessons Learned

| Lesson | Context | Takeaway |
| ------ | ------- | -------- |
| Document from source, not drafts | Drafts were structurally stale vs updated guidelines | For a rebuild, code > config > DB > drafts. |
| Plugin hosts paid off | gateways/AI/grading | New backends are cheap; preserve this seam. |
| Secrets crept into config | gateway/AI/Zoom keys in YAML | Establish the Secrets module on day one; add secret-scan CI. |
| Sync-only payments are fragile | no webhooks | Build async reconciliation with the first gateway. |
| Duplicate module slipped in | `lms_paypal` vs `lms_payment` | One capability = one module; review plugin-id collisions. |
| Tests deferred = invisible regressions | ~0% coverage | Even a thin kernel/functional baseline beats none. |
| Reproducible seed is invaluable | `lms_content_tools` | Deterministic data made this documentation pass possible. |
