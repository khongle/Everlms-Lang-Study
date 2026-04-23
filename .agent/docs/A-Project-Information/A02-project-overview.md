# A02 — Project Overview

> Stable vision document. Update only when scope, outcome, or constraints change.

---

## Vision

Nền tảng Learning Management System chuyên nghiệp cho doanh nghiệp/trường đào tạo, xây dựng trên Drupal 11 + PHP 8.3, hướng tới thị trường giáo dục trực tuyến B2B/B2C với khả năng mở rộng enterprise-grade.

## Scope

### In Scope
- Course → Chapter → Lesson management (9 lesson types: video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed)
- Assessment engine (8 question types, auto-grading via GradingStrategyFactory, manual essay grading)
- Progress tracking (cascade lesson → chapter → course)
- Commerce (order, coupon, membership, bundle, Stripe/PayPal)
- Certificate issuance + public verification
- Communication (forum, notification 10 types, rating, comments, FAQ)
- Analytics & reporting (admin/manager/instructor dashboards)
- AI provider architecture (course builder, content assistant — OpenAI, Gemini, Pollinations)
- Responsive web (desktop + mobile-ready)
- Theme system (Bootstrap 5.3 dark premium)

### Out of Scope
- Multi-tenant DB separation — *Reason:* complexity, phiên bản sau
- Native mobile app production release — *Reason:* chỉ yêu cầu API/UX readiness
- Marketplace mở cộng đồng provider bên thứ 3 — *Reason:* phiên bản sau

### Explicit Non-Goals
- Không xây dựng CMS tổng quát — tập trung 100% vào LMS domain
- Không hỗ trợ multi-language UI trong MVP (chỉ content multilingual-ready)

## Goals

| Goal | Target | Measured by |
|------|--------|-------------|
| Chuẩn hóa đào tạo | 100% khóa học có cấu trúc Course→Chapter→Lesson | System audit |
| Monetization | Positive MRR từ bán khóa/membership | Payment dashboard |
| Giảm chi phí vận hành | -60% thời gian xử lý thủ công | Operations log |
| Tỷ lệ hoàn thành | Course completion rate > 40% | Analytics dashboard |

## Stakeholders

| Role | Name | Responsibility |
|------|------|----------------|
| SA (Solutions Architect) | hainn | Technical direction, CP1-CP6 sign-off |
| Product Owner | hainn | Scope, prioritisation, B-layer changes |

## Constraints

| Type | Constraint | Implication |
|------|------------|-------------|
| Technical | Drupal 11 + PHP 8.3 | Entity API, Twig rendering, Drupal conventions |
| Technical | MariaDB 10.11 | Primary data store |
| Technical | DDEV for local dev | Standardized dev environment |
| Technical | 26 custom modules scaffolded | Module architecture fixed |
| Budget | Bootstrap/open-source stack | Không dùng licensed UI frameworks |

## Milestones

| Milestone | Checkpoint | Notes |
|-----------|------------|-------|
| Ideation frozen | CP1 | B01/B02/B03 signed off |
| Architecture frozen | CP2 | C01 + C03 signed off |
| Scaffold ready | CP3 | D01 approved, A01-CLAUDE validated |
| First group complete | CP4 | G1-G4 pass, integration tests pass |
| Quality + ops readiness | CP5 | NFR met, H-layer operational |
| Production release | CP6 | Deployment verified |

---

*End of A02-project-overview.md*
