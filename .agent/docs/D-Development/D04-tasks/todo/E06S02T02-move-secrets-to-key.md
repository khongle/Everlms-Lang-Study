---
id: E06S02T02
task_id: E06S02T02
title: Move secrets from config YAML to Key/env
status: todo
story: E06S02
owning_modules: [lms_payment, lms_ai, lms_zoom]
modules: [lms_payment, lms_ai, lms_zoom]
difficulty: 3
risk: R-001
adequacy: NOT_RUN
---

# E06S02T02 - Move secrets to Key/env

## Goal
Loại bỏ secret của bên thứ ba (third-party) khỏi `lms_*.settings.yml`; load qua Key module / environment (R-001 / BLK-001, P0). Hiện thực hóa (realise) module chuẩn **Secrets** còn thiếu.

## Steps
1. Kiểm kê (inventory) secret trong settings của `lms_payment/lms_ai/lms_zoom`.
2. Cấu hình Key entity (hoặc env var) làm điểm vào (entry point) duy nhất cho secret.
3. Refactor gateway/provider để đọc qua Key; xóa sạch (purge) secret khỏi config + git history.
4. Thêm secret-scan CI check.

## Acceptance / gates
- Không còn giá trị secret trong `config/sync`; secret-scan sạch (clean); payment/AI/Zoom vẫn hoạt động bình thường.

## Links
[story](../../D03-stories/E06S02-lms_payment.md) · [C01 standard modules](../../../C-Design/C01-architecture-decision/_index.md) · [G02 R-001](../../../G-Governance/G02-risk-register/_index.md)
