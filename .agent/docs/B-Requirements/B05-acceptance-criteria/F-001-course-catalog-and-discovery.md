# F-001 Acceptance Criteria - Course catalog & discovery

## Core criteria
1. **Given** đã có course được publish, **when** một anonymous user mở catalog, **then** họ thấy các course card (title, thumbnail, price, rating) mà không cần đăng nhập.
2. **Given** catalog, **when** user lọc theo category/level/language/tag, **then** chỉ những course đã publish khớp điều kiện mới được liệt kê.
3. **Given** một course card, **when** user mở course, **then** trang detail hiển thị summary, instructor, curriculum outline, price, và aggregate rating.
4. **Given** một course chưa publish, **when** bất kỳ user không có quyền nào duyệt, **then** course đó không xuất hiện trong catalog.

## Evidence expected
- Module: [C05 lms_course](../../C-Design/C05-modules/lms_course.md); các page trong [C11 pages](../../C-Design/C11-pages-components/pages/_index.md).
- Test: catalog-listing functional test (lọc theo từng vocabulary); access test cho course chưa publish (E03).
- Data: 40 course đã seed trải khắp các category (live DB).
