# E08 — Solutions Index

> Patterns và giải pháp tái sử dụng được rút ra từ mã nguồn thực tế của EverLMS.
> Một file mỗi pattern. Tham khảo khi thiết kế tính năng mới hoặc refactor.

## Registry

| ID | File | Pattern Type | Source Module | Problem Solved |
|----|------|-------------|---------------|----------------|
| PATTERN-001 | PATTERN-001-strategy-handler-manager.md | Strategy Pattern | lms_quiz | Grading logic đa dạng theo loại câu hỏi |
| PATTERN-002 | PATTERN-002-plugin-provider-discovery.md | Plugin Manager | lms_ai, lms_payment | Provider/gateway discovery động |

## Khi nào dùng E08

- Sau khi resolve một issue phức tạp → extract pattern vào đây
- Khi phát hiện recurring problem trong G4 review → document solution
- Khi implement một architectural pattern mới → ghi lại để reference

## Links

- Issues: E02-issues/ (nguồn gốc của solutions)
- Retrospectives: E07-retrospectives/ (lessons learned)
- Architecture patterns (design-time): K03-architecture-patterns.md
