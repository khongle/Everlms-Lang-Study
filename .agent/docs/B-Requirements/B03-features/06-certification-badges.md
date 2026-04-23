# Feature: Certification & Badges

## 1. Tổng quan

Hệ thống chứng chỉ và huy hiệu tự động, tạo động lực cho learner. Certificate được generate dưới dạng PDF, có thể verify online.

---

## 2. Certificate Management

### 2.1 Certificate Templates
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CT-01 | Template builder | Tạo template: background image, layout, placeholder tokens |
| CT-02 | Dynamic tokens | `{student_name}`, `{course_name}`, `{completion_date}`, `{certificate_id}`, `{score}` |
| CT-03 | Multiple templates | Nhiều template cho different courses/categories |
| CT-04 | Template preview | Preview template với dummy data |
| CT-05 | Template status | Active / Inactive |
| CT-06 | Certificate orientation | Portrait / Landscape |

### 2.2 Certificate Issuance
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CI-01 | Auto-issue | Tự động cấp khi hoàn thành course + pass quiz (nếu có) |
| CI-02 | Manual issue | Admin cấp certificate thủ công |
| CI-03 | PDF generation | Generate certificate dạng PDF |
| CI-04 | Download | User download PDF từ profile |
| CI-05 | Share | Share certificate link (unique URL có thể verify) |
| CI-06 | Verification | Public verification page: nhập certificate ID → xem chi tiết |
| CI-07 | Revocation | Admin thu hồi certificate |
| CI-08 | Re-issue | Cấp lại certificate với thông tin mới |

### 2.3 User Certificate Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| UC-01 | My certificates | User xem danh sách certificates đã nhận |
| UC-02 | Certificate detail | Xem chi tiết: course, date, score, download link |
| UC-03 | Admin listing | Admin xem tất cả certificates đã cấp |
| UC-04 | Filter & search | Filter: course, user, date range |
| UC-05 | Export | Export danh sách certificates CSV |

---

## 3. Badges & Gamification

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| B-01 | Badge types | Course completion, Quiz master, Streak (learning days), Community helper |
| B-02 | Badge creation | Name, description, icon/image, criteria |
| B-03 | Auto-award | Tự động trao badge khi đạt criteria |
| B-04 | Badge display | Hiển thị badges trên user profile |
| B-05 | Leaderboard | Bảng xếp hạng theo badges/points |
| B-06 | Points system | Tích điểm cho actions: complete lesson, pass quiz, forum post |
| B-07 | Level progression | Level system dựa trên points: Beginner → Expert |
