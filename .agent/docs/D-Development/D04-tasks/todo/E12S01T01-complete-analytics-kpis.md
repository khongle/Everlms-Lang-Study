---
id: E12S01T01
task_id: E12S01T01
title: Complete analytics KPIs and role scoping
status: todo
story: E12S01
owning_modules: [lms_analytics]
modules: [lms_analytics]
difficulty: 3
risk: "-"
adequacy: NOT_RUN
---

# E12S01T01 - Complete analytics KPIs and role scoping

## Goal
Hoàn thiện các report/KPI analytics còn thiếu và enforce role scoping (manager=org, instructor=own) (P2).

## Steps
1. Audit report hiện có so với KPI yêu cầu (enrollment/completion/pass-rate/revenue).
2. Implement các aggregate + chart còn thiếu; verify CSV export.
3. Enforce quyền truy cập dữ liệu theo role (role-scoped).

## Acceptance / gates
- KPI khớp với DB; instructor chỉ thấy course của mình; export chính xác.

## Links
[story](../../D03-stories/E12S01-lms_analytics.md) · [F-020](../../../B-Requirements/B03-features/F-020-analytics-and-reporting.md)
