# Task: Question Difficulty & Discrimination Index Analytics

**ID:** TASK-175
**Unit:** U-030 (lms_quiz)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tính toán các chỉ số phân tích câu hỏi theo chuẩn đánh giá giáo dục (Item Analysis): p-value (tỷ lệ % học viên trả lời đúng) và discrimination index (khả năng câu hỏi phân biệt học viên giỏi vs kém). Đây là công cụ giúp instructor cải thiện chất lượng ngân hàng câu hỏi theo phương pháp khoa học.

Công thức: p-value = số_đúng / tổng_attempts; discrimination index (D) = (% đúng của top 27%) - (% đúng của bottom 27%). Câu hỏi tốt: 0.3 ≤ p ≤ 0.7 và D ≥ 0.3. Analytics hiển thị trong question bank UI (TASK-170) và per-quiz analytics report.

## Acceptance Criteria

- [ ] `QuestionAnalyticsService::calculate(int $questionId): QuestionStats` — trả `{ p_value, discrimination_index, total_attempts, correct_count, distractor_analysis }`
- [ ] `distractor_analysis`: per-answer-option `{ answer_id, label, selected_count, selected_pct }` — để biết distractors nào bị chọn nhiều nhất
- [ ] Top/bottom 27%: sort attempts by quiz total score → lấy top 27% và bottom 27% group → tính D
- [ ] Schedule: recalculate via cron mỗi ngày (hoặc on-demand khi admin click "Refresh analytics")
- [ ] Question bank UI: thêm cột `p-value` và `D-index` với color coding: xanh (tốt), vàng (cần xem xét), đỏ (cần cải thiện)
- [ ] Threshold config: `lms_quiz.analytics_settings` — p_value_min, p_value_max, d_index_min (default 0.3/0.7/0.3)
- [ ] Per-quiz analytics page: `/admin/lms/quiz/{id}/analytics` — bảng all questions với stats + export CSV
- [ ] Alert: nếu D < 0 (câu hỏi "ngược" — học viên giỏi sai nhiều hơn kém) → flag đỏ + tooltip giải thích
- [ ] Unit test: `QuestionAnalyticsServiceTest` — p=6/10=0.6; D với mock top/bottom groups
- [ ] Performance: cache kết quả analytics 24h per question (cache tag `lms_quiz_result_list:{quiz_id}`)

## Technical Notes

- Module: `web/modules/custom/lms_quiz/`
- Service: `src/Service/QuestionAnalyticsService.php`
- Data source: `lms_quiz_attempt_answer` table — `question_id`, `is_correct`, `attempt_id`, plus join to `lms_quiz_attempt` for total score
- Cache: `\Drupal::cache('data')->set("lms_quiz.question_stats:{$questionId}", $stats, Cache::PERMANENT, $tags)`
- Top/bottom 27%: `$count = count($attempts); $n = (int) round($count * 0.27)`; sort by score desc/asc
- Distractor analysis: GROUP BY `answer_id` count — works for MCQ; skip for essay type questions
- Cron: `lms_quiz_cron()` — batch process questions updated since last_calculated
- Coordinate: TASK-170 (question bank — display analytics), TASK-174 (export — shares attempt data)

## Dependencies

- G-ASSESSMENT (quiz_attempt_answer data)
- TASK-170 (question bank — UI host)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
