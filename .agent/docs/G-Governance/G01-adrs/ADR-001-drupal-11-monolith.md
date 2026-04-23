# G01-ADR-001: Drupal 11 Entity API + Monolithic Deployment

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

EverLMS cần một nền tảng để xây dựng hệ thống quản lý học tập (LMS) với các yêu cầu:
- Quản lý 40+ content entity (course, chapter, lesson, quiz, question, user progress, certificate, v.v.)
- 7 vai trò người dùng với RBAC chi tiết
- Tích hợp payment gateway (Stripe, PayPal), AI provider, Zoom
- Team nhỏ (1-2 developers), timeline tight
- Deployment đơn giản trên VPS/shared hosting

Tại thời điểm khởi động dự án (2026-03), nhóm đánh giá hai hướng kiến trúc chính: monolithic Drupal application và microservices.

---

## Decision

Sử dụng **Drupal 11 với Entity API** làm nền tảng ứng dụng và triển khai theo mô hình **monolithic single-process** trên một web server (Nginx + PHP-FPM).

Cụ thể:
- 26 custom modules với prefix `lms_`, tổ chức theo 9 layers có dependency graph rõ ràng
- Tất cả business logic nằm trong Service layer của Drupal modules
- Drupal Entity API xử lý toàn bộ CRUD, field validation, access control
- Deployment: DDEV local, single-server production với Nginx 1.24+ + MariaDB 10.11

---

## Rationale

Drupal 11 Entity API cung cấp sẵn: content entity system, field API, view modes, access handlers, REST/JSON:API, Form API, cache tags/contexts. Đây là những thành phần mà một LMS cần nhưng sẽ mất hàng tháng để tự build nếu theo microservices.

Với team nhỏ và scope lớn (26 modules, 40+ entities), monolith cho phép tập trung vào business logic thay vì service orchestration, distributed transactions, và network latency.

---

### Options Considered

#### Option 1: Drupal 11 Monolith (CHOSEN)
- **Pros:** Entity API mạnh, ecosystem chín muồi (Views, Rules, Permissions), Form API cho admin UI, cache system tích hợp, RBAC có sẵn, deployment đơn giản
- **Cons:** Coupling cao hơn microservices, horizontal scale phức tạp hơn (session affinity), không phù hợp nếu cần chia team độc lập
- **Fit:** Rất phù hợp — LMS là domain coherent, team nhỏ, không cần độc lập deploy từng service

#### Option 2: Laravel + Microservices (REJECTED)
- **Pros:** Flexible, dễ scale từng service, modern REST API design
- **Cons:** Phải tự build entity system, RBAC, admin UI, form handling; distributed transactions cho progress/payment phức tạp; cần nhiều infrastructure (API gateway, service mesh)
- **Reason rejected:** Chi phí build infrastructure quá cao cho team nhỏ; không có sẵn entity management tốt như Drupal

#### Option 3: Headless CMS + React SPA (REJECTED)
- **Pros:** Rich frontend UX, SPA navigation nhanh
- **Cons:** Phải duy trì hai codebase (backend + frontend), SEO phức tạp hơn, SCORM player integration khó hơn trong SPA
- **Reason rejected:** Over-engineering cho MVP; Drupal Twig đủ mạnh cho LMS UI

---

## Consequences

### Positive
- Entity API cho phép định nghĩa 40+ entities nhanh chóng với field storage, CRUD, và access handler tích hợp
- Drupal module system cung cấp natural boundaries giữa các domain (course, quiz, payment, AI)
- Admin UI miễn phí thông qua Drupal admin theme
- Cache system (page cache + entity cache + Redis) hoạt động out-of-the-box
- Deployment đơn giản: một process, một database, không cần service discovery

### Negative
- Vertical scale có giới hạn; nếu traffic tăng mạnh cần xem xét load balancer + session sharing
- Drupal update cycle cần quản lý (security updates, major version migration)
- Một số Drupal conventions (hook system, service container boilerplate) tốn thời gian học

### Risks
- **RISK-010** (G02): Database migration failure khi upgrade — mitigate bằng staging test + backup pre-deploy
- Vendor lock-in vào Drupal — chấp nhận được vì ecosystem stable và long-term support

---

## Related

- **Design docs:** C01-architecture-decision.md
- **Other ADRs:** ADR-002 (PHP 8.3), ADR-003 (Twig-first), ADR-004 (Bootstrap 5)
- **Modules:** `lms_base` (foundation), tất cả `lms_*` modules
