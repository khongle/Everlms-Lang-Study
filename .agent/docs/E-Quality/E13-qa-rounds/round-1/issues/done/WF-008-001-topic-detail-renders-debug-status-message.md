---
id: WF-008-001
workflow: WF-008
title: Forum topic detail renders a debug status message
severity: medium
category: ui
status: done
roles: visitor, student
source: code/visual QA
found: 2026-06-30
---

# WF-008-001 - Forum topic detail renders a debug status message

## Problem
`LmsForumController::viewTopic()` adds a product-visible debug status message on
every topic page:
`FRESH CONTROLLER LOADED! Found Title: ...`

The entity canonical route is altered to this controller, so normal topic views
can show debug copy to learners and anonymous visitors.

## Impact
The forum detail page looks unfinished and can confuse users. It also pollutes
screenshots and masks real status messages such as topic-created, reply-posted,
or best-answer confirmation.

## Expected
Forum topic pages should render only product messages caused by user actions.

## Fix Plan
- Remove the debug messenger call from `LmsForumController::viewTopic()`.
- Retest topic create, reply, and best-answer flows.

## Resolution
- Removed the debug messenger call from `LmsForumController::viewTopic()`.

## Verification
- Final WF-008 run passed with topic `43`.
- Screenshot `007-student-author-topic-created-detail.png` no longer contains
  `FRESH CONTROLLER LOADED`.
- Topic create, reply, and best-answer actions completed successfully.
