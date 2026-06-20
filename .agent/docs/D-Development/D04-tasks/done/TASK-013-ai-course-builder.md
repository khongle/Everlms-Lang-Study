# Task: AI Course Builder

**ID:** TASK-013
**Unit:** U-048
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** DONE (70%)
**Completed:** 2026-04-15

## Audit Notes
- CourseBuilderForm: AJAX wizard (topic, audience, tone) ✅
- CourseBuilderService: scaffolds course structure via AI JSON, creates Course+Lesson entities, queues content ✅
- CourseContentGenerator QueueWorker: async lesson content generation (cron-based, 60s limit) ✅
- 6 prompt templates in `prompt/` directory ✅
- **Known bugs**: Hardcodes `createInstance('openai')` instead of using default_provider. No state machine for multi-step generation. No explicit rollback on partial failure. QueueWorker calls `generateText()` not in interface.

## Description
AI-powered course creation: từ topic input, AI generate toàn bộ course structure (chapters + lessons + content).

## Acceptance Criteria
- [ ] Instructor input topic → AI generate course outline (chapters + lessons)
- [ ] Human review + edit trước khi accept
- [ ] AI generate lesson content (text, quiz questions)
- [ ] State machine cho multi-step generation (progress tracking)
- [ ] Rollback nếu generation fail giữa chừng
- [ ] Compensation logic cho partial content

## Technical Notes
- Module: `lms_ai`
- Service: `AiCourseBuilder::buildFromTopic()`
- State: INIT → GENERATING_STRUCTURE → REVIEW → GENERATING_CONTENT → DONE / FAILED
- Feature flag: `FF_FEAT_AI_BUILDER`

## Dependencies
- TASK-012 (AI provider plugin)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Rollback test (simulate mid-generation failure)
- [ ] State transition tests
