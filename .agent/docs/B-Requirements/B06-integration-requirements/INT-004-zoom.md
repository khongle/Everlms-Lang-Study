# INT-004 - Zoom

## Requirement
Tạo và quản lý Zoom meeting trực tiếp gắn với course (F-022): sinh meeting + join URL, hiển thị cho learner đã enroll.

## Participating modules
[lms_zoom](../../C-Design/C05-modules/lms_zoom.md)

## Contract expectations
1. **Auth/authz:** Zoom API credential (OAuth/JWT) — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** khi Zoom API lỗi, lỗi schedule được surface; không có session link bị lỗi/rỗng nào hiển thị cho learner.
3. **CI test fixture/stub:** Zoom client được mock trả về meeting payload dựng sẵn; không gọi Zoom thật trong CI.
4. **Operational owner + alerting:** Instructor-experience owner; alert khi Zoom API lỗi lặp lại. ⚠️ As-built depth chỉ partial.

## Design link
[C06 INT-004](../../C-Design/C06-apis/INT-004-zoom.md)
