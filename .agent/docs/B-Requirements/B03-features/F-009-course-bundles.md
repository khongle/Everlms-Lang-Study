# F-009 - Course bundles

**Status:** built
**Primary module:** [lms_bundle](../../C-Design/C05-modules/lms_bundle.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-009](../B05-acceptance-criteria/F-009-course-bundles.md)
**Source:** `web/modules/custom/lms_bundle/`

## Requirement
Editor có thể gộp nhiều course thành một **bundle** (`lms_course_bundle.field_courses`) bán với một mức giá bundle duy nhất. Mua một bundle sẽ enroll buyer vào toàn bộ course có trong đó.

## Business value
Bundle làm tăng average order value và cho phép tổ chức đóng gói learning path theo vai trò (ví dụ một chương trình an toàn lao động đầy đủ).

## Scope notes
- `lms_course_bundle` tham chiếu course qua `field_courses`.
- Việc mua bundle sẽ fan out thành enrollment cho từng course (F-004) khi order hoàn tất.
- Pricing/discount so với việc mua course lẻ là một field ở cấp bundle.

## Cross references
- Design: [C04 lms_course_bundle](../../C-Design/C04-entities/lms_course_bundle.md), [C05 lms_bundle](../../C-Design/C05-modules/lms_bundle.md)
- Related: F-010 Orders, F-004 Enrollment
