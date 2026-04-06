# Feature: User Management

## 1. Tổng quan

Quản lý user đa vai trò, authentication, profile, và preferences. Hỗ trợ social login và self-registration.

---

## 2. Authentication & Registration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| A-01 | Email registration | Đăng ký bằng email + password |
| A-02 | Social login | Login qua Google, Facebook, LinkedIn |
| A-03 | Email verification | Xác thực email sau đăng ký |
| A-04 | Password reset | Forgot password flow |
| A-05 | Two-factor auth (2FA) | Tuỳ chọn enable 2FA (TOTP/SMS) |
| A-06 | Remember me | Persistent login session |
| A-07 | Login history | Admin xem lịch sử đăng nhập |
| A-08 | Account lockout | Khoá tài khoản sau N lần sai mật khẩu |

---

## 3. User Profile

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| P-01 | Profile fields | Avatar, display name, bio, phone, timezone |
| P-02 | Profile edit | User tự chỉnh sửa profile |
| P-03 | Public profile | Trang profile public (courses enrolled, badges, certificates) |
| P-04 | Instructor profile | Bio, expertise, courses taught, rating |
| P-05 | Privacy settings | User control visibility (public/private) |

---

## 4. User Administration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| UA-01 | User listing | Filter: role, status, registration date |
| UA-02 | User CRUD | Tạo/sửa/khoá/xoá user |
| UA-03 | Bulk operations | Assign role, activate/deactivate, send email |
| UA-04 | User export | Export user list CSV |
| UA-05 | User import | Import users từ CSV (bulk enrollment) |


---

## 5. Roles & Permissions

| # | Role | Mô tả | Key Permissions |
|---|------|-------|----------------|
| R-01 | **Administrator** | Full access | Toàn quyền hệ thống |
| R-02 | **Manager** | Quản lý nội dung & users | CRUD courses, manage users, view reports |
| R-03 | **Instructor / Teacher** | Tạo & quản lý courses | CRUD own courses, grade quizzes, view student progress |
| R-04 | **Student / Learner** | Học viên | Enroll, learn, take quiz, view certificates |
| R-05 | **Content Editor** | Biên tập nội dung | Edit courses/lessons, manage media |
| R-06 | **Authenticated User** | Đã đăng nhập | View profile, browse courses |
| R-07 | **Anonymous** | Chưa đăng nhập | Browse public courses, view landing pages |

> [!IMPORTANT]
> **Permission Matrix chi tiết** (Single Source of Truth): xem [roles-permissions.md](../by-roles/roles-permissions.md)

