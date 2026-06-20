# Task: Spaced Repetition Flashcards

**ID:** TASK-232
**Unit:** — (new module)
**Group:** G-PEDAGOGY
**Priority:** P3
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a spaced repetition flashcard system using the SM-2 algorithm. Each course can have a flashcard deck attached to it. Students review cards daily; the SM-2 algorithm schedules the next review date based on recall quality (0–5 rating). A daily review queue is presented in the student dashboard.

## Context
- New module `lms_flashcard`; no existing flashcard functionality in the project.
- Complements TASK-231 (microlearning bites) and TASK-233 (adaptive difficulty) as advanced pedagogy features.
- SM-2 algorithm: open standard, straightforward implementation in PHP.

## Acceptance Criteria
- [ ] New module `lms_flashcard` scaffolded with correct `lms_{slug}` naming convention
- [ ] `FlashcardDeck` config entity: belongs to a course, has name + description
- [ ] `Flashcard` content entity: `front` (text/HTML), `back` (text/HTML/image), belongs to a deck
- [ ] Deck management UI for instructors at `node/{nid}/flashcards` — CRUD for decks and cards within a course
- [ ] Student review UI at `/flashcards/review` — shows due cards, one at a time; after revealing back, student rates recall 0–5
- [ ] SM-2 algorithm implemented in `SM2Service::calculate(int $prevInterval, float $prevEFactor, int $quality): array` returning next interval (days) + new E-factor
- [ ] Per-user per-card review state stored in `lms_flashcard_review` table: `(uid, fcid, interval, ef, due_date, last_reviewed)`
- [ ] Daily review queue widget on student dashboard shows card count due today
- [ ] Cards with quality < 3 are re-shown in current session (SM-2 rule)
- [ ] Decks are exportable as CSV (front, back columns)

## Technical Notes
- New module: `lms_flashcard` at `web/modules/custom/lms_flashcard/`
- Entities: `FlashcardDeck` (config entity), `Flashcard` (content entity with bundle = deck)
- Schema: `lms_flashcard_review (uid INT, fcid INT, interval INT, ef FLOAT, due_date DATE, last_reviewed TIMESTAMP, PRIMARY KEY (uid, fcid))`
- SM-2 reference: `https://www.supermemo.com/en/archives1990-2015/english/ol/sm2` — implement as stateless pure PHP service
- Review UI: `FlashcardReviewController` → Drupal AJAX form; each card flip + rating is an AJAX call to save review state
- Dashboard widget: `FlashcardDueTodayBlock` (`@Block`) — count query on `lms_flashcard_review WHERE due_date <= TODAY AND uid = :uid`
- Import/export: `lms_flashcard/src/Plugin/Action/ExportDeckCsvAction`

## Dependencies
- None (standalone new module)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: SM2Service — all quality 0–5 transitions; functional: review queue, due date scheduling)
- [ ] No new debt introduced
