# F-020 - Analytics & reporting

**Status:** partial
**Primary module:** [lms_analytics](../../C-Design/C05-modules/lms_analytics.md)
**Epic:** [E12 Analytics](../../D-Development/D02-epics/E12-analytics.md)
**Related workflows:** —
**Acceptance:** [B05 F-020](../B05-acceptance-criteria/F-020-analytics-and-reporting.md)
**Source:** `web/modules/custom/lms_analytics/`

## Requirement
Manager/instructor xem dashboard và report: enrollment theo thời gian, completion rate, quiz pass rate, doanh thu, và progress theo từng course/từng learner (`lms_analytics`, ~7 route). Dữ liệu được suy ra (derive) từ progress, order và quiz result.

## Business value
Analytics biến hoạt động thành quyết định — course nào bán chạy, learner drop off ở đâu, hiệu suất của instructor — và là yếu tố thiết yếu cho B2B reporting.

## Scope notes
- Lớp reporting chỉ đọc (read-only) đặt trên các entity đã có (không tạo domain entity mới).
- ⚠️ Độ bao phủ as-built còn partial — một số report/KPI còn cơ bản hoặc chỉ là placeholder; độ sâu cần được xác nhận thêm ở E06.
- Cần tôn trọng role scoping (manager thấy dữ liệu toàn org; instructor chỉ thấy course của mình).

## Cross references
- Design: [C05 lms_analytics](../../C-Design/C05-modules/lms_analytics.md)
- Status: [E06 implementation status](../../E-Quality/E06-implementation-status/_index.md)
