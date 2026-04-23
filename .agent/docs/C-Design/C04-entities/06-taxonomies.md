# Taxonomy Vocabularies

> 8 taxonomy vocabularies cho classification và filtering.

---

| # | Vocabulary | Machine Name | Type | Mô tả | Ví dụ |
|---|-----------|-------------|------|-------|-------|
| 1 | Course Category | `course_category` | Hierarchical | Phân loại khoá học (parent/child) | Web Dev > Frontend, Design > UI/UX |
| 2 | Course Level | `course_level` | Flat | Trình độ học viên | Beginner, Intermediate, Advanced |
| 3 | Course Language | `course_language` | Flat | Ngôn ngữ giảng dạy | English, Vietnamese, Japanese |
| 4 | Course Tags | `course_tags` | Flat | Tags tự do | PHP, JavaScript, Marketing, AI |
| 5 | Quiz Difficulty | `quiz_difficulty` | Flat | Độ khó câu hỏi | Easy, Medium, Hard |
| 6 | Quiz Results | `quiz_results` | Flat | Kết quả quiz | Passed, Failed |
| 7 | FAQ Category | `faq_category` | Flat | Phân loại FAQ | General, Payment, Technical |
| 8 | Badge Type | `badge_type` | Flat | Loại huy hiệu | Completion, Streak, Quiz Master |

---

## Course Category Hierarchy Example

```
Programming
├── Frontend
│   ├── HTML/CSS
│   ├── JavaScript
│   └── React
├── Backend
│   ├── PHP
│   ├── Node.js
│   └── Python
└── Mobile
    ├── Flutter
    └── React Native

Business
├── Marketing
├── Finance
└── Management

Design
├── UI/UX
├── Graphic Design
└── Video Production
```
