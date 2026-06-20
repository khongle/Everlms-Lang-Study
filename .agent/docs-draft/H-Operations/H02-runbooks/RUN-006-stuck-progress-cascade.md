# RUN-006 — Stuck Progress Cascade

| Field | Value |
|-------|-------|
| **ID** | RUN-006 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV3 / SEV4 |

---

## Khi nào dùng

- User hoàn thành lesson nhưng course progress không cập nhật (vẫn 0% hoặc sai %)
- `UserCourse.completion_percentage` không đúng so với các `UserLesson` đã complete
- User báo "đã học xong nhưng không nhận được certificate" → progress bị kẹt
- Sau incident SEV2 có race condition trong progress tracking (RISK-002)
- Sau khi sửa bug progress calculation → cần re-compute cho users bị ảnh hưởng

---

## Prerequisites

- [ ] Xác định user ID bị ảnh hưởng (từ ticket hoặc query)
- [ ] Xác định course ID liên quan
- [ ] Có quyền chạy Drush trong DDEV

---

## Entities liên quan

| Entity | Class | Mô tả |
|--------|-------|-------|
| `UserCourse` | `lms_progress/Entity/UserCourse.php` | Progress toàn khóa học |
| `UserLesson` | `lms_progress/Entity/UserLesson.php` | Progress từng bài học |

> Lưu ý: `ProgressCascadeService` hiện chưa được implement như một service riêng.
> Cascade logic nằm trong controller hoặc entity hooks — xác nhận tại code trước khi chạy.

---

## Part A — Chẩn đoán

### 1. Kiểm tra UserLesson records của user

```bash
ddev drush sql:query "
  SELECT ul.id, ul.lesson_id, ul.status, ul.completed_at, ul.user_id
  FROM lms_user_lesson ul
  WHERE ul.user_id = {USER_ID}
    AND ul.course_id = {COURSE_ID}
  ORDER BY ul.lesson_id;"
```

### 2. Kiểm tra UserCourse record

```bash
ddev drush sql:query "
  SELECT uc.id, uc.course_id, uc.user_id, uc.completion_percentage,
         uc.status, uc.enrolled_at, uc.completed_at
  FROM lms_user_course uc
  WHERE uc.user_id = {USER_ID}
    AND uc.course_id = {COURSE_ID};"
```

### 3. Tính manual xem đúng không

```bash
ddev drush sql:query "
  SELECT
    COUNT(*) as total_lessons,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as pct
  FROM lms_user_lesson
  WHERE user_id = {USER_ID} AND course_id = {COURSE_ID};"
```

---

## Part B — Re-trigger Progress Cascade

### Option 1: Qua Drush php-eval (thủ công)

```bash
ddev drush php-eval "
  // Load user và course
  \$user = \Drupal\user\Entity\User::load({USER_ID});
  \$course_id = {COURSE_ID};

  // Tính lại completion từ UserLesson records
  \$lesson_ids = \Drupal::entityQuery('lms_user_lesson')
    ->condition('user_id', {USER_ID})
    ->condition('course_id', \$course_id)
    ->execute();
  \$lessons = \Drupal\lms_progress\Entity\UserLesson::loadMultiple(\$lesson_ids);

  \$total = count(\$lessons);
  \$completed = 0;
  foreach (\$lessons as \$lesson) {
    if (\$lesson->get('status')->value === 'completed') \$completed++;
  }
  \$pct = \$total > 0 ? round(\$completed * 100 / \$total, 2) : 0;

  // Update UserCourse
  \$uc_ids = \Drupal::entityQuery('lms_user_course')
    ->condition('user_id', {USER_ID})
    ->condition('course_id', \$course_id)
    ->execute();
  if (\$uc_ids) {
    \$uc = \Drupal\lms_progress\Entity\UserCourse::load(reset(\$uc_ids));
    \$uc->set('completion_percentage', \$pct);
    if (\$pct >= 100) {
      \$uc->set('status', 'completed');
      \$uc->set('completed_at', \Drupal::time()->getRequestTime());
    }
    \$uc->save();
    echo \"Updated: user={USER_ID}, course=\$course_id, pct=\$pct%\n\";
  }
"
```

### Option 2: Batch update nhiều users

```bash
ddev drush php-script /path/to/fix-progress-batch.php
# PROPOSED: tạo script batch nếu có nhiều users bị ảnh hưởng
```

---

## Part C — Verification

```bash
# Kiểm tra lại UserCourse sau fix
ddev drush sql:query "
  SELECT uc.completion_percentage, uc.status, uc.completed_at
  FROM lms_user_course uc
  WHERE uc.user_id = {USER_ID} AND uc.course_id = {COURSE_ID};"

# Nếu completion = 100% → kiểm tra certificate đã generate chưa
ddev drush sql:query "
  SELECT * FROM lms_certificate
  WHERE user_id = {USER_ID} AND course_id = {COURSE_ID};"
```

---

## Part D — Nếu Certificate cũng bị kẹt

Sau khi fix progress, nếu certificate chưa được cấp → chạy RUN-007.

---

## Root Cause

- Race condition khi nhiều requests cập nhật progress đồng thời (RISK-002 trong A10)
- Giải pháp dài hạn: implement queue-based cascade hoặc DB transaction lock

---

## Tham chiếu

- H01: Log nếu nhiều users bị ảnh hưởng (≥5 users = SEV3)
- RUN-007: Certificate regeneration
- lms_progress: `web/modules/custom/lms_progress/`
- E02: Mở issue cho RISK-002 nếu race condition đã xảy ra thực tế
