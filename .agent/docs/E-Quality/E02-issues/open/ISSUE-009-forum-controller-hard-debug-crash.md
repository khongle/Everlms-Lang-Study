# ISSUE-009 — ForumController::viewTopic Throws HARD DEBUG Exception; LmsForumController Leaks Debug Message

| Field | Value |
|---|---|
| ID | `ISSUE-009` |
| Title | ForumController::viewTopic throws literal "HARD DEBUG!" exception; LmsForumController::viewTopic leaks debug addMessage to all users |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `entity.lms_forum_topic.canonical` → `/forum/{lms_forum_topic}` |
| Owning Epic | E-04 (Communication) — |
| Owning Story | [`lms_forum`](../../../D-Development/D03-stories/lms_forum.md) |
| Owning module(s) | `lms_forum` |

## Summary

Two debug artifacts co-exist in `lms_forum` and together represent a HIGH-severity stability risk:

1. **`ForumController` (dead-code class)** — `src/Controller/ForumController.php::viewTopic()` starts with `throw new \Exception("HARD DEBUG! ...")`. The entire method body after the throw is unreachable dead code. The class is not wired to any current route (the canonical topic route is overridden by `RouteSubscriber` to `LmsForumController::viewTopic`). However, if the `RouteSubscriber` is disabled, the config cache is cleared in an inconsistent state, or a new developer re-wires the canonical route to `ForumController`, the site throws an unhandled 500 on every forum topic page load.

2. **`LmsForumController` (active controller)** — `src/Controller/LmsForumController.php::viewTopic()` calls `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")`. This Drupal status message is displayed on every authenticated topic-view page for every user, polluting the UI with developer noise.

Both artifacts indicate the module was shipped with developer debug scaffolding present in production code, which is flagged as BUG-FORUM-01 and BUG-FORUM-02 in the [D03 story](../../../D-Development/D03-stories/lms_forum.md).

## Reproduction

**Bug 1 — Exception risk:**
1. Open `web/modules/custom/lms_forum/src/Controller/ForumController.php`.
2. Locate `viewTopic()` method — first statement is `throw new \Exception("HARD DEBUG! ...")`.
3. If `RouteSubscriber` were absent or bypassed, Drupal would route `entity.lms_forum_topic.canonical` to `ForumController::viewTopic` (as defined in the entity's `links.canonical`), causing HTTP 500 on all forum topic URLs.
4. Even with `RouteSubscriber` active: the class compiles, so a misconfigured route can trigger it inadvertently.

**Bug 2 — Debug message visible to users:**
1. Log in as any authenticated user.
2. Navigate to any forum topic at `/forum/{id}`.
3. A Drupal status message "FRESH CONTROLLER LOADED! ..." appears at the top of the page on every load — visible to all users including learners.

## Root Cause

During development, `ForumController` was the initial implementation class; it was replaced by `LmsForumController` (wired via `RouteSubscriber`). The original class was never deleted — only superseded. A debug `throw` statement was left at the top of `viewTopic()` to force a 500 if the old class were accidentally invoked. Similarly, a `messenger()->addMessage()` call was left in `LmsForumController::viewTopic()` as a "controller loaded" sanity check and was never removed before the module went into production use.

Source files:
- `web/modules/custom/lms_forum/src/Controller/ForumController.php` — dead code; contains live `throw`
- `web/modules/custom/lms_forum/src/Controller/LmsForumController.php` — active controller; contains debug `addMessage`

## Resolution (filled at RESOLVED)

1. **Delete** `web/modules/custom/lms_forum/src/Controller/ForumController.php` entirely. No route references this class; it is pure dead code and a latent crash risk.
2. **Remove** the `\Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! ...")` call from `LmsForumController::viewTopic()`.
3. Clear Drupal caches (`drush cr`) and verify forum topic pages render cleanly with no spurious status messages.
4. Add regression test `T-FORUM-005` (confirm no route points to `ForumController`) and `T-FORUM-006` (confirm no status message appears on topic view).

## Trace links

- **Story:** `D-Development/D03-stories/lms_forum.md` §"Known Bugs" — BUG-FORUM-01, BUG-FORUM-02
- **C05 module spec:** `C-Design/C05-modules/lms_forum.md`
- **Facts base:** `modules-progress-comms.md` §lms_forum §Controllers — ForumController status: "EFFECTIVELY DEAD / DEBUG ARTIFACT"
- **K04:** Reusable rule — delete superseded controller classes rather than leaving them as dead code; never commit debug `throw` or `messenger()->addMessage()` calls to production branches.
