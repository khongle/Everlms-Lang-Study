# Task: Adaptive Difficulty for Practice Quizzes

**ID:** TASK-233
**Unit:** U-042 (quiz)
**Group:** G-PEDAGOGY
**Priority:** P3
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement adaptive difficulty for practice quiz sessions. The system tracks per-question correctness history for each user, assigns an ELO-style difficulty rating to each question and a skill rating to each user per topic, and adjusts the next question's difficulty up or down based on the user's recent performance. This applies only to "practice" quiz mode, not graded assessments.

## Context
- `lms_quiz` module exists.
- TASK-232 (flashcards) uses SM-2 for cards; adaptive difficulty uses ELO for quiz questions — two complementary systems.
- TASK-233 should not change graded quiz behavior — strictly practice mode only.

## Acceptance Criteria
- [ ] Each `lms_quiz` question entity gains `field_difficulty_rating` (float, range 0–3000, default 1500) storing current ELO rating
- [ ] New table `lms_quiz_user_skill (uid, topic_tid, elo_rating FLOAT, last_updated TIMESTAMP)` tracks user skill per topic
- [ ] Practice quiz sessions use `AdaptiveQuestionSelector::next(uid, quiz_nid)` to pick next question: select from pool questions whose rating is ±200 of user's current ELO in that topic
- [ ] After each answer: if correct → update user ELO up, question ELO down; if wrong → reverse; using standard ELO K-factor 32
- [ ] Practice session ends after 10 questions; summary shows accuracy by difficulty band (easy/medium/hard)
- [ ] Adaptive mode flag `field_quiz_adaptive` (boolean) on Quiz entity; practice quiz UI renders "Adaptive Practice" mode when enabled
- [ ] Non-adaptive and graded quizzes completely unaffected
- [ ] Admin UI at `quiz/{qid}/analytics` shows per-question ELO distribution chart

## Technical Notes
- Module: `lms_quiz` (extend with ELO fields + adaptive selector)
- `EloService::updateRatings(float $userElo, float $questionElo, bool $correct, int $k = 32): array` — returns `[newUserElo, newQuestionElo]`
- `AdaptiveQuestionSelector::next(uid, quiz_nid)`: query `lms_quiz_question` where `topic_tid` matches quiz topic AND `difficulty_rating` BETWEEN `(userElo - 200)` AND `(userElo + 200)`, ORDER BY RAND(), LIMIT 1; fallback to global RAND() if no match
- Session: practice session state (asked question IDs, running ELO snapshot) stored in `$_SESSION['lms_adaptive_{qid}']`
- ELO chart: use Chart.js (already in project) in `quiz/{qid}/analytics` route; data via JSON endpoint
- `lms_quiz_user_skill` table schema: install via `lms_quiz.install` hook_update or schema hook

## Dependencies
- None (extends lms_quiz internally)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: EloService all cases; functional: adaptive session — question difficulty shifts after correct/incorrect streaks)
- [ ] No new debt introduced
