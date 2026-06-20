# Task: @Mention System

**ID:** TASK-138
**Unit:** — (extend `lms_notification`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Add @mention support to forum posts, course Q&A, and lesson comments. When a user types `@` in a supported text area, an autocomplete dropdown shows matching enrolled users, instructors, and admins. Saving the content triggers an in-app + email notification to each mentioned user.

## Context
- `lms_notification` dispatch service (TASK-009) handles notification delivery.
- Autocomplete scope must be context-aware: forum mentions from site users; Q&A mentions from course enrollees + instructors; lesson comments from course enrollees + instructors.
- Avoid notifying the author for their own @self mention.

## Acceptance Criteria
- [ ] Typing `@` in forum post body, Q&A question/answer body, or lesson comment body opens autocomplete
- [ ] Autocomplete fetches users via AJAX (`/api/mentions/autocomplete?q=&context=course:{id}`) with debounce 300ms
- [ ] Dropdown shows display name + avatar thumbnail; keyboard-navigable
- [ ] Selected mention inserted as `@[Display Name](uid:123)` token rendered as styled `<span>` on save
- [ ] On save, parse all `@[...](uid:NNN)` tokens; dispatch mention notification to each unique mentioned user (excluding author)
- [ ] Notification message: "User X mentioned you in [context link]"
- [ ] Respects TASK-140 notification preferences (in-app / email toggles)
- [ ] Graceful degradation: if JS disabled, plain text `@username` is stored without notification
- [ ] Max 10 mentions per post to prevent abuse

## Technical Notes
- Module: `web/modules/custom/lms_notification/` (extend, no new module)
- New service: `MentionParserService` — parses tokens, resolves UIDs, deduplicates, dispatches
- New route: `lms_notification.routing.yml` — `GET /api/mentions/autocomplete` → `MentionAutocompleteController`
- JS library: `lms_notification/mentions` — lightweight tribute.js wrapper or custom implementation
- Render token: `MentionFilter` text filter plugin OR post-process in Twig (simpler for MVP)
- Attach mention JS to relevant text areas via `#attached` libraries in the relevant form alters
- SCSS: `_mentions.scss` — autocomplete dropdown styles

## Dependencies
- TASK-009 (U-042 Notification dispatch service)
- TASK-136 (Course Q&A — mention target context)
- TASK-137 (Lesson comments — mention target context)
- TASK-039 (Forum — mention target context)
- TASK-140 (Notification preferences)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `MentionParserServiceTest` (token parse, dedup, self-exclusion, cap at 10)
- [ ] Autocomplete endpoint returns correct scoped results (PHPUnit + JS unit test)
- [ ] No new debt introduced
