# Feature: Assessment System (Quiz & Exam)

## 1. Tổng quan

Hệ thống đánh giá đa dạng loại câu hỏi, hỗ trợ cả auto-grading và manual-grading. Tích hợp chặt chẽ với course progress và certification.

---

## 2. Quiz Management

### 2.1 Quiz Creation
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| Q-01 | Tạo quiz | Title, description, instructions |
| Q-02 | Quiz settings | Pass threshold (%), time limit (minutes), max attempts |
| Q-03 | Question assignment | Gắn questions vào quiz (drag-drop reorder) |
| Q-04 | Random question pool | Rút ngẫu nhiên N câu từ bank |
| Q-05 | Display style | One-page / Step-by-step (question per page) |
| Q-06 | Certificate linkage | Gắn certificate template cho quiz passed |
| Q-07 | Prerequisite quizzes | Phải pass quiz trước mới được thi quiz sau |
| Q-08 | Result display | Cấu hình hiển thị đáp án đúng sau khi nộp |
| Q-09 | Difficulty level | Taxonomy reference |
| Q-10 | Quiz status | Draft / Published |

### 2.2 Quiz Administration
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| QA-01 | Quiz listing | Filter: course, status, difficulty |
| QA-02 | Quiz analytics | Tỷ lệ pass/fail, điểm trung bình, phân bố điểm |
| QA-03 | Duplicate quiz | Clone quiz + questions |
| QA-04 | Export results | CSV/Excel export |

---

## 3. Question Types

### 3.1 Supported Types (8 loại)

| # | Type | Mô tả | Scoring |
|---|------|-------|---------|
| QT-01 | **Multiple Choice** | Chọn nhiều đáp án đúng (checkboxes) | Auto |
| QT-02 | **Single Choice** | Chọn 1 đáp án đúng (radio) | Auto |
| QT-03 | **True/False** | Đúng hoặc Sai | Auto |
| QT-04 | **Fill in the Blank** | Điền vào chỗ trống (single/multi-gap) | Auto |
| QT-05 | **Short Answer** | Trả lời ngắn (exact/contains/keywords matching) | Auto |
| QT-06 | **Matching** | Ghép cặp (dropdown hoặc drag-drop) | Auto |
| QT-07 | **Ordering** | Sắp xếp thứ tự (drag-drop) | Auto |
| QT-08 | **Essay** | Bài luận (word count, rubric) | Manual |

### 3.2 Question Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| QM-01 | Question bank | Quản lý câu hỏi tập trung, tái sử dụng giữa các quiz |
| QM-02 | Question CRUD | Tạo/sửa/xoá cho mỗi type |
| QM-03 | Question tagging | Gắn tag cho question (topic, difficulty) |
| QM-04 | Partial scoring | Hỗ trợ chấm điểm từng phần cho matching/ordering/fill-blank |
| QM-05 | Question preview | Preview câu hỏi trước khi publish |
| QM-06 | Bulk import | Import questions từ CSV/JSON |

---

## 4. Quiz Taking Experience

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| QT-01 | Timer countdown | Đồng hồ đếm ngược với cảnh báo |
| QT-02 | Auto-save | Tự động lưu câu trả lời khi thay đổi (AJAX) |
| QT-03 | Navigation panel | Hiển thị trạng thái từng câu (answered/skipped) |
| QT-04 | Auto-submit | Tự động nộp khi hết giờ |
| QT-05 | Review before submit | Xem lại tất cả câu trả lời trước khi nộp |
| QT-06 | Instant feedback | Hiển thị kết quả ngay sau khi nộp |
| QT-07 | Result detail | Xem chi tiết đúng/sai từng câu (tuỳ cấu hình) |

---

## 5. Quiz Results Management

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| QR-01 | Results listing | Danh sách kết quả theo quiz/user/course |
| QR-02 | Manual grading | Teacher/admin chấm điểm essay questions |
| QR-03 | Score override | Cho phép admin điều chỉnh điểm |
| QR-04 | Result export | Export kết quả CSV/Excel |
| QR-05 | Attempt history | Xem lịch sử các lần thi của user |
| QR-06 | Grading queue | Dashboard cho teacher thấy bài chờ chấm |
