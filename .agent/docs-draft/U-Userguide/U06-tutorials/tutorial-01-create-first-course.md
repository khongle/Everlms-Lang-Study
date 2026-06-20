# Tutorial: Tạo Khóa học Đầu tiên

> Step-by-step walkthrough từ zero đến một course đã publish với 3 chapters và 1 quiz.
> Estimated time: **45 phút**.

**Difficulty:** Beginner
**Prerequisites:** Role `instructor` đã gán. Đã đăng nhập.
**Features covered:** FEAT-01 (course-management), FEAT-02 (assessment-system)

## Goal

Publish một course miễn phí tên "Python Basics" với:
- 3 chapters (Introduction, Variables, Control Flow).
- 5 lessons tổng (mix text + video + quiz).
- 1 quiz 5 câu cuối Chapter 2.
- Preview video ở course detail.

## Steps

### Step 1: Tạo Course shell (5 min)

1. **Instructor Dashboard** → **Create Course** → bỏ qua AI Builder.
2. Điền:
   - **Title:** `Python Basics`
   - **Slug:** auto `python-basics`
   - **Category:** *Programming* (nếu chưa có, admin tạo taxonomy term trước).
   - **Level:** Beginner
   - **Summary:** `Introduction to Python for total beginners.`
   - **Description:** 2-3 đoạn (dùng rich text; paste từ outline).
   - **Thumbnail:** Upload 1280x720 JPG.
   - **Preview video:** YouTube URL `https://youtube.com/watch?v=...` (1-2 min intro).
   - **Price:** `0` (free).
3. **Save as Draft** → redirect về course edit.

### Step 2: Thêm Chapter 1 — Introduction (5 min)

1. Tab **Curriculum** → **Add Chapter**.
2. **Title:** `Introduction`
3. **Unlock rule:** Free (không khóa lesson).
4. Save.

### Step 3: Lesson 1.1 — "What is Python?" (text lesson, 5 min)

1. Click chapter → **Add Lesson**.
2. **Type:** text.
3. **Title:** `What is Python?`
4. **Body:** 300-500 từ về Python (dùng bullet + heading).
5. **Completion criteria:** `scroll >= 90% + time_on_page >= 60s`.
6. Save.

### Step 4: Lesson 1.2 — "Installing Python" (video lesson, 5 min)

1. **Add Lesson** → **Type:** video.
2. **Title:** `Installing Python`
3. **Source:** YouTube URL hoặc upload MP4.
4. **Duration:** 3:45 (auto-detect hoặc manual).
5. **VTT subtitle:** optional upload.
6. **Completion criteria:** `watched >= 80%`.
7. Save.

### Step 5: Thêm Chapter 2 — Variables (5 min)

1. **Add Chapter** → Title: `Variables` → Save.
2. **Lesson 2.1** (text): `Declaring Variables`.
3. **Lesson 2.2** (text): `Data Types`.

### Step 6: Tạo Quiz "Variables Check" (10 min)

1. **Instructor Dashboard** → **Quizzes** → **New Quiz**.
2. Config:
   - **Title:** `Variables Check`
   - **Passing score:** 60%
   - **Time limit:** 5 min
   - **Retry limit:** 3
   - **Shuffle questions:** on
3. **Questions** tab → **Add Question**:

**Q1** (single_choice): `Which is a valid Python variable name?`
- `my_var` (correct)
- `2var`
- `my-var`
- `class`

**Q2** (multi_choice): `Which are Python primitive types?`
- `int` (correct)
- `float` (correct)
- `str` (correct)
- `array`

**Q3** (true_false): `Python is dynamically typed. → True`

**Q4** (fill_in_blank): `To declare a variable x with value 5: x = {{blank_1}}` — accept `5`.

**Q5** (short_answer): `What function prints to console?` — accept `print`, `print()`.

4. Save quiz.

### Step 7: Gắn Quiz vào Chapter 2 (2 min)

1. Back to course edit → chapter 2 → **Add Lesson**.
2. **Type:** quiz.
3. **Title:** `Chapter 2 Check`.
4. **Quiz:** chọn `Variables Check` từ dropdown.
5. **Completion criteria:** Pass quiz.
6. Save.

### Step 8: Chapter 3 — Control Flow (5 min)

Tương tự Step 5: add chapter + 1 text lesson `if/else`.

### Step 9: Preview (3 min)

1. Course edit → **Preview** button.
2. Mở trong tab mới → xem như learner:
   - Course detail page render đúng thumbnail + preview video.
   - Curriculum tree hiển thị 3 chapters.
   - Click lesson 1.1 → text render đẹp.
   - Click lesson 1.2 → video player hoạt động.
   - Quiz load, trả lời vài câu test.

### Step 10: Publish (1 min)

1. Back to course edit → **Publish** button.
2. Confirm modal → **Yes**.
3. Course status đổi **Published**.
4. Open `/courses` → thấy card `Python Basics` trong list.

## Verification

- [ ] Course xuất hiện trong public `/courses`.
- [ ] Preview video phát được ở course detail.
- [ ] Tất cả 5 lessons accessible.
- [ ] Quiz submitable với các câu trả lời đúng → pass.
- [ ] Test enroll từ học viên dummy account → **My Learning** có course.

## Next Steps

- Thêm **Completion Certificate:** attach template ở course edit → [learner-05-earn-certificate.md](../U03-feature-guides/learner-05-earn-certificate.md).
- **Set price:** Change price > 0 → checkout flow áp dụng ([customer-01-checkout.md](../U03-feature-guides/customer-01-checkout.md)).
- **Add forum:** Enable course forum → learner có thể thảo luận ([learner-06-participate-forum.md](../U03-feature-guides/learner-06-participate-forum.md)).
- **Try AI Builder** để outline khóa tiếp theo nhanh hơn: [instructor-03-ai-course-builder.md](../U03-feature-guides/instructor-03-ai-course-builder.md).

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Save course fail "validation error" | Check required fields (Title, Category, Level). |
| Video upload fail | File quá lớn (> 500MB default). Chunk upload hoặc external host. |
| Quiz không hiện ở dropdown | Quiz chưa save. Refresh page. |
| Publish bị nút disable | Course thiếu: thumbnail / summary / ≥ 1 lesson. |
| Learner không thấy khóa | Cache. `drush cr` hoặc wait 5 min. |
