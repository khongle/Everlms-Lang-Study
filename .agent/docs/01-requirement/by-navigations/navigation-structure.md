# EverLMS — Navigation Structure

## 1. Navigation Architecture

Hệ thống sử dụng **3-tier navigation**: Public (Anonymous/Guest), User Portal (Authenticated), Management (Admin/Manager/Instructor).

---

## 2. Public Navigation (Anonymous / Guest)

### 2.1 Header Navigation
```
┌──────────────────────────────────────────────────────────────────────┐
│  [Logo]  Home | Courses | Bundles | Categories | Pricing | Blog     │
│                                                      [Login] [Register] │
└──────────────────────────────────────────────────────────────────────┘
```

| # | Menu Item | Route | Mô tả |
|---|-----------|-------|-------|
| N-01 | Home | `/` | Landing page |
| N-02 | Courses | `/courses` | Course listing + search |
| N-02b | Bundles | `/bundles` | Course bundles with savings |
| N-03 | Categories | `/categories` | Category listing |
| N-04 | Pricing | `/pricing` | Membership plans comparison |
| N-05 | Blog | `/blog` | Blog articles (optional) |
| N-06 | Contact | `/contact` | Contact form (Webform) |
| N-07 | Login | `/user/login` | Login form |
| N-08 | Register | `/user/register` | Registration form |

### 2.2 Footer Navigation
| # | Section | Links |
|---|---------|-------|
| FN-01 | About | About Us, FAQ, Terms, Privacy Policy |
| FN-02 | Courses | Popular, Latest, Free Courses |
| FN-03 | Support | Contact, Help Center |
| FN-04 | Social | Facebook, Twitter, LinkedIn, YouTube |

---

## 3. User Portal Navigation (Student / Authenticated)

### 3.1 Header (after login)
```
┌──────────────────────────────────────────────────────────────────────┐
│  [Logo]  Home | Courses | My Learning | Learning Paths | Community  │
│                              [🏆 Leaderboard] [🔔 Notifications] [👤 Profile ▼] │
└──────────────────────────────────────────────────────────────────────┘
```

### 3.2 Profile Dropdown
| # | Menu Item | Route |
|---|-----------|-------|
| UP-01 | My Profile | `/user/{uid}` |
| UP-02 | My Courses | `/my-courses` |
| UP-03 | My Certificates | `/my-certificates` |
| UP-04 | My Orders | `/my-orders` |
| UP-05 | My Membership | `/my-membership` |
| UP-06 | Settings | `/user/{uid}/edit` |
| UP-07 | Logout | `/user/logout` |

### 3.3 My Learning Sub-navigation
| # | Menu Item | Route |
|---|-----------|-------|
| ML-01 | All Courses | `/my-courses` |
| ML-02 | In Progress | `/my-courses?status=in_progress` |
| ML-03 | Completed | `/my-courses?status=completed` |
| ML-04 | Wishlist | `/my-wishlist` |

### 3.4 Additional Navigation
| # | Menu Item | Route | Mô tả |
|---|-----------|-------|-------|
| AN-01 | Learning Paths | `/learning-paths` | Browse structured learning paths |
| AN-02 | Leaderboard | `/leaderboard` | XP rankings, top learners, badge showcase |

---

## 4. Management Navigation (Admin / Manager / Instructor)

> [!IMPORTANT]
> **KHÔNG dùng left sidebar navigation.** Tất cả management pages phải dùng **top horizontal navbar + mega-menu dropdown** (kiểu OpenCourt). Mega-menu hiển thị khi hover/click vào menu item chính, mở ra multi-column dropdown chứa các sub-items được nhóm theo category. Pattern này tối ưu chiều ngang cho data tables và content area.

### 4.1 Mega Menu / Admin Header
```
┌──────────────────────────────────────────────────────────────────────┐
│  [Logo]  Dashboard | Courses ▼ | Assessments ▼ | Users ▼ |        │
│          Commerce ▼ | Reports ▼ | Settings ▼                       │
│                                        [🔔] [👤 Admin ▼] [← View Site] │
└──────────────────────────────────────────────────────────────────────┘
```

### 4.2 Dashboard
| # | Route | Mô tả |
|---|-------|-------|
| MD-01 | `/manage` | Admin/Manager dashboard |
| MD-02 | `/manage/instructor` | Instructor dashboard |

### 4.3 Courses Menu
| # | Menu Item | Route |
|---|-----------|-------|
| MC-01 | All Courses | `/manage/courses` |
| MC-02 | Create Course | `/manage/courses/create` |
| MC-03 | Categories | `/manage/categories` |
| MC-04 | Tags | `/manage/tags` |

### 4.4 Assessments Menu
| # | Menu Item | Route |
|---|-----------|-------|
| MA-01 | All Quizzes | `/manage/quizzes` |
| MA-02 | Create Quiz | `/manage/quizzes/create` |
| MA-03 | Question Bank | `/manage/questions` |
| MA-04 | Quiz Results | `/manage/quiz-results` |
| MA-05 | Grading Queue | `/manage/grading` |

### 4.5 Users Menu
| # | Menu Item | Route |
|---|-----------|-------|
| MU-01 | All Users | `/manage/users` |
| MU-02 | Create User | `/manage/users/create` |
| MU-03 | Roles | `/manage/roles` |
| MU-04 | Enrollments | `/manage/enrollments` |

### 4.6 Commerce Menu
| # | Menu Item | Route |
|---|-----------|-------|
| MO-01 | Orders | `/manage/orders` |
| MO-02 | Memberships | `/manage/memberships` |
| MO-03 | Membership Plans | `/manage/membership-plans` |
| MO-04 | Coupons | `/manage/coupons` |
| MO-05 | Payments | `/manage/payments` |
| MO-06 | Bundles | `/manage/bundles` |
| MO-07 | Cart Recovery | `/manage/cart-recovery` |

### 4.7 Reports Menu
| # | Menu Item | Route |
|---|-----------|-------|
| MR-01 | Overview | `/manage/reports` |
| MR-02 | Course Reports | `/manage/reports/courses` |
| MR-03 | User Reports | `/manage/reports/users` |
| MR-04 | Revenue Reports | `/manage/reports/revenue` |
| MR-05 | Quiz Reports | `/manage/reports/quizzes` |

### 4.8 Settings Menu (Admin only)
| # | Menu Item | Route |
|---|-----------|-------|
| MS-01 | General | `/manage/settings` |
| MS-02 | Payment | `/manage/settings/payment` |
| MS-03 | Email Templates | `/manage/settings/emails` |
| MS-04 | Notifications | `/manage/settings/notifications` |
| MS-05 | Integrations | `/manage/settings/integrations` |
| MS-06 | Certificates | `/manage/certificates` |
| MS-07 | Certificate Templates | `/manage/certificate-templates` |
| MS-08 | Zoom Settings | `/manage/settings/zoom` |
| MS-09 | Email Campaigns | `/manage/email-campaigns` |

---

## 5. Course Learning Navigation (In-Course)

### 5.1 Course Sidebar (while learning)
```
┌────────────────────┐
│ Course: PHP Basics  │
│ Progress: ████ 65% │
├────────────────────┤
│ ▼ Chapter 1        │
│   ✅ Lesson 1.1    │
│   ✅ Lesson 1.2    │
│   ▶ Lesson 1.3     │ ← Current
│   ○ Lesson 1.4     │
│ ▼ Chapter 2        │
│   ○ Lesson 2.1     │
│   🔒 Lesson 2.2   │ ← Locked
│ ▸ Chapter 3        │
│ ▸ Final Quiz       │
└────────────────────┘
```

| Icon | Meaning |
|------|---------|
| ✅ | Completed |
| ▶ | Current / In Progress |
| ○ | Not Started |
| 🔒 | Locked (prerequisite) |
