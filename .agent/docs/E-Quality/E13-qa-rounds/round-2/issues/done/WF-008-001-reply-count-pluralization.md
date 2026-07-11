---
id: WF-008-001
workflow: WF-008
title: Forum teaser shows "1 replies" (bad pluralization)
severity: low
category: content
status: done
roles: anonymous
source: round-2 real-session QA
found: 2026-07-11
---

# WF-008-001 - "There are 1 replies in this topic"

## Problem
The anonymous topic teaser hard-coded "replies", producing "There are 1 replies
in this topic."

## Expected
Singular/plural aware: "There is 1 reply…" / "There are N replies…".

## Evidence
- `09-anon-topic-teaser.png` — "There are 1 replies in this topic."

## Fix (implemented)
- `lms-forum-topic.html.twig` teaser uses `{% trans %} … {% plural total_replies %} … {% endtrans %}`.

## Verification
- 1 reply → "There is 1 reply…"; N replies → "There are N replies…".

## Resolution (2026-07-11)
teaser uses {% plural %}; verified '1 reply' renders for a single reply.
