# Task: Analytics Dashboards

**ID:** TASK-014
**Unit:** U-050
**Group:** G-INTEGRATION
**Priority:** P1
**Estimate:** 14h
**Assigned:** unassigned
**Status:** DONE (70%)
**Completed:** 2026-04-15

## Audit Notes
- 3 report services: CourseReportManager, UserReportManager, QuizAnalyticsManager — all use efficient JOIN-based DB queries ✅
- AnalyticsService: aggregate KPIs (enrollments, revenue, completion rate) ✅
- Controllers with CSV export (StreamedResponse + UTF-8 BOM) ✅
- 4 Twig templates for report views ✅
- 7 admin-only routes with proper permissions ✅
- **Known gaps**: No `#cache` metadata on render arrays. No instructor-scoped views (admin-only). No chart/graph JS library. CSV export hardcoded limit 10000. No date range filters.

## Description
Dashboard báo cáo cho Admin/Manager/Instructor: course analytics, student progress, revenue, quiz statistics.

## Acceptance Criteria
- [ ] Admin dashboard: tổng quan system (users, courses, revenue, enrollments)
- [ ] Manager dashboard: user/order/membership reports với filters
- [ ] Instructor dashboard: own course stats (enrollment, completion, ratings)
- [ ] Filters: date range, course, user
- [ ] Export CSV cho báo cáo quan trọng
- [ ] Report load time ≤3s (average data)

## Technical Notes
- Module: `lms_analytics`
- Controller: `AnalyticsController`
- Routes: `/manage/reports/courses`, `/manage/reports/students`, `/manage/reports/revenue`, `/manage/reports/quizzes`
- Service: `AnalyticsService` (extend 3 existing queries)
- Templates: `lms-report-*.html.twig`

## Dependencies
- U-046 (AnalyticsService base) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Performance test (load time ≤3s)
- [ ] Role access tests
