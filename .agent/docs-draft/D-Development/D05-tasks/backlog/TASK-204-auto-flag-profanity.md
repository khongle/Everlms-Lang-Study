# Task: Auto-Flag Profanity and Suspicious Patterns

**ID:** TASK-204
**Unit:** — (lms_moderation filter service)
**Group:** G-COMMUNICATION
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Automatically detect and queue suspicious content before it is published. Detection rules: profanity dictionary (Vietnamese + English), excessive URL count, all-caps text, and repeated character spam. Posts triggering rules are routed to the moderation queue with an auto-flag reason. Strictness is configurable by admins.

## Acceptance Criteria

- [ ] **ProfanityFilterService:**
  - [ ] Dictionary stored as YAML config files: `lms_moderation/config/install/lms_moderation.profanity_en.yml` and `lms_moderation.profanity_vn.yml`
  - [ ] Case-insensitive match; handles leet-speak substitutions (4→a, 3→e, 0→o, $→s)
  - [ ] Match found → content routed to queue with flag reason "profanity"
  - [ ] Dictionary extendable via admin UI (TASK-205 dashboard links to dictionary editor)
- [ ] **URL count rule:** more than N URLs in a single post → flag as "spam" (N configurable, default 3)
- [ ] **All-caps rule:** post where >70% of alpha characters are uppercase → flag as "shouting/spam"
- [ ] **Repeated character rule:** any sequence of same character ≥ 6 repeats (e.g., "!!!!!!" or "aaaaa") → flag
- [ ] **Strictness levels** (admin config `lms_moderation.settings.filter_strictness`):
  - [ ] `low`: only profanity + URL rules active
  - [ ] `medium` (default): all 4 rules active
  - [ ] `high`: all rules + any post from accounts < 24h old goes to queue
- [ ] Flagged content: set `ModerationQueueItem::auto_flagged = true`, `flag_reason` field lists triggered rules
- [ ] Moderator sees flag reasons in queue UI (TASK-205)
- [ ] PHPUnit test: each rule independently; leet-speak bypass; strictness level switching

## Technical Notes

- Module: `lms_moderation` — new `ContentFilterService`
- Service registration: `lms_moderation.services.yml` → `lms_moderation.content_filter`
- Called from: `ModerationQueueService::createItem()` — filter runs before queue item is saved
- Leet-speak normalizer: `LeetNormalizer::normalize(string $text): string` — simple str_replace map
- Config schema: `lms_moderation.schema.yml` must define `filter_strictness` (string, allowed: low/medium/high) and `max_urls` (integer)
- Do NOT store profanity list in code comments — use YAML config files only

## Dependencies

- TASK-202 (moderation queue — filtered posts routed here)
- TASK-205 (moderator dashboard — flag reasons displayed, dictionary editor linked)

## Definition of Done

- [ ] Code complete — ContentFilterService, LeetNormalizer, config YAML, strictness switch
- [ ] G1-G4 gates passed
- [ ] Each detection rule independently unit-tested with Vietnamese + English examples
- [ ] Strictness levels toggle correctly (feature-flagged if needed via H05)
- [ ] No false-positive rate regression (test with clean sample posts)
- [ ] No new debt introduced
