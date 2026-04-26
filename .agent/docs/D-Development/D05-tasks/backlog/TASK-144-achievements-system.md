# Task: Achievements System

**ID:** TASK-144
**Unit:** — (new module `lms_achievement` or extend `lms_badge`)
**Group:** G-GAMIFICATION
**Priority:** P3
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build an unlockable achievements system distinct from the badge system (TASK-047, TASK-063). Achievements are predefined milestones such as "First Quiz", "Perfect Score 5×", "30-Day Streak". When an achievement is unlocked, a toast notification fires. An achievement showcase is displayed on the user profile.

## Context
- Badges (TASK-063) are manually awarded by admins; achievements are automatically unlocked by system events.
- Achievements may award XP (TASK-143) on unlock.
- 30-Day Streak achievement requires TASK-142 streak milestone event.
- Separate from badges to allow different visual design and unlock logic without polluting `lms_badge` admin.

## Acceptance Criteria
- [ ] Pre-defined achievements seeded on install (see list below); each has name, description, icon, XP reward
- [ ] Seed list (minimum): First Lesson, First Quiz, First Course Complete, Perfect Score (100% quiz), Perfect Score 5×, 7-Day Streak, 30-Day Streak, 100-Day Streak, Night Owl (lesson after 22:00), Early Bird (lesson before 07:00), Speed Learner (course in under 24h), Social (first forum post), Helpful (first Q&A answer marked accepted), Certified (first certificate), Collector (10 courses completed)
- [ ] On unlock: toast "Achievement Unlocked: [Name]" shown; in-app notification logged; XP awarded via `lms_xp`
- [ ] Each achievement can only be unlocked once per user
- [ ] Profile page `/user/{uid}` shows achievement showcase grid: unlocked (full colour) + locked (greyed silhouette)
- [ ] Admin UI at `/admin/lms/achievements` — view achievement list, see unlock counts, edit icon/description/XP reward
- [ ] Achievement unlock event can be subscribed to by other modules

## Technical Notes
- New module: `web/modules/custom/lms_achievement/`
- DB tables: `lms_achievement` (id, machine_name, label, description, icon_path, xp_reward, created), `lms_achievement_unlock` (id, uid, achievement_id, unlocked_at)
- Service: `AchievementService::unlock(uid, machine_name)` — idempotency check, insert unlock, dispatch `lms_achievement.unlocked`, award XP
- Event subscribers: `StreakMilestoneSubscriber` (listens `lms_streak.milestone`), `QuizSubscriber` (listens quiz pass events), `ForumSubscriber`, etc.
- Time-based achievements (Night Owl, Early Bird) evaluated in lesson complete subscriber using `date('H', REQUEST_TIME)`
- Admin: `AchievementAdminController` + list builder + edit form
- Twig: `achievement-showcase.html.twig`, `achievement-toast.html.twig`
- JS: `lms_achievement/toast` triggers toast on `drupalSettings.lms_achievement.unlocked`
- SCSS: `_achievements.scss`

## Dependencies
- TASK-047 (Student-facing badges UI — UX reference for showcase grid)
- TASK-063 (Admin badges CRUD — parallel; achievements are distinct)
- TASK-142 (Learning streaks — streak milestone events)
- TASK-143 (XP system — award XP on unlock)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `AchievementServiceTest` (unlock, idempotency, XP award call)
- [ ] All 15 seed achievements install correctly
- [ ] Toast fires on unlock (smoke test noted in PR)
- [ ] No new debt introduced
