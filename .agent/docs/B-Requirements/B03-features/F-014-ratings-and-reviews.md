# F-014 - Ratings & reviews

**Status:** built
**Primary module:** [lms_rating](../../C-Design/C05-modules/lms_rating.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-014](../B05-acceptance-criteria/F-014-ratings-and-reviews.md)
**Source:** `web/modules/custom/lms_rating/`

## Requirement
Learner đã enroll rate một course (1–5 sao) và có thể để lại review dạng viết (`lms_rating`). Rating tổng hợp (aggregate) được hiển thị trên catalog (F-001) và course detail để giúp buyer đưa ra quyết định.

## Business value
Social proof thúc đẩy conversion; rating tổng hợp giúp learner lựa chọn và cho instructor feedback.

## Scope notes
- Thông thường một rating entity cho mỗi cặp user-course; giá trị aggregate được tính để hiển thị.
- Điều kiện thường yêu cầu đã enrollment/có một mức progress nhất định.
- Được hiển thị trên catalog card và course detail; có sẵn qua mobile API.

## Cross references
- Design: [C04 lms_rating](../../C-Design/C04-entities/lms_rating.md), [C05 lms_rating](../../C-Design/C05-modules/lms_rating.md)
- Related: F-001 Catalog
