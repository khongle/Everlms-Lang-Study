# EverLMS — Content Tools

> Bộ công cụ quản lý content: clean data, nhập thông tin gốc, và cơ chế AI Agent tự generate toàn bộ content cho hệ thống training.

## Mục tiêu

- Cho phép admin **xóa sạch content** an toàn với cơ chế confirm 2 lượt
- Cung cấp form **nhập thông tin gốc** (seed input) để AI có đủ ngữ cảnh generate content
- Định nghĩa **cơ chế AI Agent** (Claude Code / Gemini) đọc seed input → tự generate content đầy đủ → import vào hệ thống

## Flow tổng quan

```
┌─────────────────────────────────────────────────────────────────┐
│                     CONTENT TOOLS FLOW                          │
│                                                                 │
│  ┌──────────┐    ┌──────────────┐    ┌───────────────────────┐  │
│  │  Clean    │    │  Seed Input  │    │  AI Agent Generator   │  │
│  │  Content  │    │  Form        │    │  (Claude/Gemini)      │  │
│  │  Form     │    │              │    │                       │  │
│  │           │    │  Lưu file    │    │  Đọc seed input       │  │
│  │  Confirm  │───▶│  JSON vào    │───▶│  + specs hệ thống    │  │
│  │  2 lượt   │    │  /seed/      │    │  → Generate content   │  │
│  │           │    │              │    │  → Output scripts     │  │
│  │  Drush    │    │              │    │  → Import vào DB      │  │
│  │  clean    │    │              │    │                       │  │
│  └──────────┘    └──────────────┘    └───────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Document Index

| # | File | Mô tả |
|---|------|-------|
| 01 | [Clean Content Form](./01-clean-content.md) | Form xóa content với confirm 2 lượt, gõ "CLEAN" |
| 02 | [Seed Input Form](./02-seed-input-form.md) | Form nhập thông tin gốc cho AI generate content |
| 03 | [AI Content Generator](./03-ai-content-generator.md) | Cơ chế AI Agent đọc seed → generate → import |
| 04 | [Báo cáo Chạy thử](./04-report.md) | Kết quả chạy thử lần 1: 378 entities, 0 errors |
| 05 | [Step-by-Step Tạo Program Mới](./05-step-by-step-create-new-program.md) | Hướng dẫn từng bước tạo chương trình training mới |
| -- | [Problems & Notes](./PROBLEMS.md) | 12 vấn đề phát hiện trong quá trình chạy thử |

## Liên quan

- [01-requirement](../01-requirement/) — Entity structure, fields, relationships
- [06-development](../06-development/) — Drush commands, coding standards
