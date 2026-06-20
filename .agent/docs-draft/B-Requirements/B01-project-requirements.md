# B01 — Project Requirements

## Business Context

EverLMS là nền tảng LMS chuyên nghiệp cho doanh nghiệp/trường đào tạo, giải quyết 4 vấn đề kinh doanh cốt lõi:
1. **Chuẩn hóa quy trình đào tạo online** từ tạo nội dung đến cấp chứng chỉ.
2. **Monetization** thông qua bán lẻ khóa học, bundle, membership.
3. **Giảm chi phí vận hành** qua tự động hóa (notification, certificate, AI support).
4. **Tăng tỷ lệ hoàn thành** khóa học và mức độ gắn kết học viên.

## Stakeholders

| ID | Stakeholder | Role | Interest | Communication |
|----|------------|------|----------|---------------|
| SH-001 | Product/Tech Owner | SA | Technical direction, architecture, quality | Every session |
| SH-002 | Administrator | System admin | Cấu hình hệ thống, phân quyền, vận hành tổng thể | Weekly |
| SH-003 | Manager | Operations | Quản trị users/orders/memberships/reports | Weekly |
| SH-004 | Instructor | Content creator | Tạo khóa học, theo dõi tiến độ học viên | Sprint review |
| SH-005 | Student | End user | Tìm khóa học, học tập, nhận chứng chỉ | UAT |

## Business Requirements

| ID | Requirement | Priority | Rationale | Stakeholder |
|----|------------|----------|-----------|-------------|
| BR-001 | Course management (CRUD Course→Chapter→Lesson) | Must | Core feature, nền tảng cho toàn bộ hệ thống | SH-001 |
| BR-002 | Multi-format lessons (video, text, H5P, SCORM, Zoom, PDF) | Must | Đa dạng hình thức học tập | SH-004 |
| BR-003 | Assessment engine (8 loại câu hỏi + auto-grading) | Must | Đánh giá năng lực học viên | SH-004 |
| BR-004 | Progress tracking (cascade lesson→chapter→course) | Must | Theo dõi tiến độ, continue learning | SH-005 |
| BR-005 | Commerce (order, coupon, membership, bundle) | Must | Monetization | SH-003 |
| BR-006 | Payment gateway integration (Stripe, PayPal) | Must | Xử lý thanh toán | SH-003 |
| BR-007 | Certificate issuance + public verification | Must | Chứng nhận hoàn thành | SH-005 |
| BR-008 | Notification system (in-app, email) | Should | Tăng engagement | SH-005 |
| BR-009 | Forum & community (topic, reply, rating) | Should | Tương tác cộng đồng | SH-005 |
| BR-010 | Analytics & reporting (admin/manager/instructor dashboards) | Should | Insights kinh doanh | SH-002, SH-003 |
| BR-011 | AI course builder + content assistant | Could | Tăng tốc tạo nội dung | SH-004 |
| BR-012 | Zoom integration | Could | Live learning | SH-004 |
| BR-013 | Gamification (badges, leaderboard) | Could | Tăng engagement | SH-005 |
| BR-014 | Learning paths (multi-course sequences) | Could | Career development | SH-005 |
| BR-015 | Mobile-responsive UX | Must | Trải nghiệm đa thiết bị | SH-005 |

## Constraints

| ID | Type | Constraint | Impact |
|----|------|-----------|--------|
| CON-001 | Technical | Drupal 11 + PHP 8.3 | Entity API, Twig rendering, Drupal coding standards |
| CON-002 | Technical | MariaDB 10.11 | Relational data model, no NoSQL |
| CON-003 | Technical | Plugin architecture cho payment & AI provider | Extensible gateways |
| CON-004 | Technical | Theme Twig/SCSS, Bootstrap 5.3 | Frontend responsive |
| CON-005 | Integration | DDEV for local dev | Standardized environment |

## Success Metrics

| ID | Metric | Target | Baseline | Measurement Method | Frequency |
|----|--------|--------|----------|-------------------|-----------|
| SM-001 | Course completion rate | >40% | N/A | Analytics dashboard | Monthly |
| SM-002 | Time-to-first-lesson (sau enroll) | <30s | N/A | System logs | Weekly |
| SM-003 | Payment success rate | >95% | N/A | Payment dashboard | Weekly |
| SM-004 | System uptime | 99.5% | N/A | Monitoring | Daily |
| SM-005 | Page load time (homepage) | <2s | N/A | Performance tests | Per release |

## Assumptions

| ID | Assumption | Risk if Wrong | Validation Plan |
|----|-----------|---------------|-----------------|
| ASM-001 | Users có trình puyệt hiện đại (Chrome/Firefox/Safari recent) | UI/UX degradation | Browser compatibility test |
| ASM-002 | Payment gateway APIs ổn định | Checkout flow broken | Integration tests + monitoring |
| ASM-003 | Content volume <10K courses ban đầu | Performance issues | Load test |
| ASM-004 | AI provider (OpenAI/Google) available | AI features degraded | Provider abstraction + fallback |

---

*End of B01-project-requirements.md*
