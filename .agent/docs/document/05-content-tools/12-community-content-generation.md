# Hướng dẫn tạo Community Content (Forum Topics & Badges)

Tính năng import Community trong module `lms_content_tools` cho phép tự động chèn các Chủ đề Thảo luận (Forum Topics), Các câu trả lời (Replies) và Hệ thống Huy hiệu (Badges) vào nền tảng.

## Vị trí file
File JSON luôn phải nằm ở:
`.agent/generated/community/forum-topics.json`

## Cấu trúc JSON chuẩn

File JSON cần chứa 2 keys chính là `forum_topics` và `badges`.

```json
{
  "forum_topics": [
    {
      "title": "Topic: Tên chủ đề cần thảo luận",
      "body": "<p>Nội dung topic hỗ trợ định dạng HTML.</p>",
      "replies": [
        {
          "body": "<p>Câu trả lời số 1 từ member khác.</p>"
        },
        {
          "body": "<p>Câu trả lời số 2 đóng góp ý kiến.</p>"
        }
      ]
    }
  ],
  "badges": [
    {
      "criteria_type": "course_completion"
    },
    {
      "criteria_type": "quiz_master"
    },
    {
      "criteria_type": "perfect_score"
    }
  ]
}
```

## Giải thích chi tiết
### 1. Forum Topics
- **`title`**: (String) Tiêu đề của khung thảo luận. Sẽ được lưu vào field `name` của entity `lms_forum_topic`.
- **`body`**: (HTML String) Đoạn mô tả mào đầu. Thường chứa HTML `<p>`, `<strong>`, `<ul>`.
- **`replies`**: (Array) Một mảng gồm các Câu trả lời (Replies).
  - Khác với Topic, Reply chỉ cần key `body`. 
  - Hệ thống (`ContentToolsCommands.php`) sẽ tự map UUID của Reply vào cái Topic cha đang được tạo (thông qua `field_topic`).
- Mặc định, Topics và Replies được gắn `uid = 1` hoặc random tuỳ biến trong code gốc.

### 2. Badges
- **`criteria_type`**: (String) Xác định loại tiêu chí để được nhận huy hiệu. Thường là:
  - `course_completion` (Hoàn thành khoá học)
  - `quiz_master` (Trùm giải đố)
  - `perfect_score` (Điểm tuyệt đối)

## Quy trình Clean & Update
Để tránh rác hệ thống (Data trùng lặp quá nhiều), `ContentToolsManager.php` đã được cập nhật `ENTITY_GROUPS` bằng việc kết nạp nhóm `community` bao gồm 3 thực thể sau:
- `'lms_forum_topic'`
- `'lms_forum_reply'`
- `'lms_badge'`

Vì thế, bất cứ khi nào bạn chạy `ddev drush everlms:clean`, mọi chủ đề, câu trả lời, huy hiệu cũ do AI Tool tạo ra sẽ bốc hơi sạch sẽ, chừa chỗ cho một mẻ Data nháp xịn xò mới.

## Script tự động Generator
Bạn có thể tự động chạy đẻ 80 Topic (cho 40 khoá học từ `seed-input.json`) bằng lệnh sau mà không cần mớm prompt cho AI một cách thủ công:
```bash
php .agent/scripts/generate_community.php
```
