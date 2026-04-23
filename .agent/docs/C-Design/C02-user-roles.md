# C02 — User Roles

> 7 roles, 9 permission sections, strict RBAC.

---

## Role Registry

| Role ID | Machine Name | Scope | Description |
|---------|-------------|-------|-------------|
| R-01 | `administrator` | Full system access | Quản trị toàn quyền: settings, users, content, commerce, reports |
| R-02 | `manager` | Content + User management | Quản lý nội dung, users, orders, reports. Không system settings |
| R-03 | `instructor` | Own courses + students | Tạo/quản lý courses của mình, chấm bài, xem progress students |
| R-04 | `content_editor` | Content editing | Biên tập nội dung courses/lessons, quản lý media. Không tạo courses mới |
| R-05 | `student` | Learning + profile | Enroll, learn, take quizzes, view certificates, participate forums |
| R-06 | `authenticated` | Basic access | Drupal default, browse courses, view profile |
| R-07 | `anonymous` | Public access | Browse public pages, view course listings, register |

## Role Hierarchy

```
Administrator
  └── Manager
        ├── Instructor
        └── Content Editor
              └── Student
                    └── Authenticated
                          └── Anonymous
```

## Permissions Matrix

### Content Management

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Create course | ✅ | ✅ | ✅ | ❌ | ❌ |
| Edit any course | ✅ | ✅ | ❌ | ✅ | ❌ |
| Edit own course | ✅ | ✅ | ✅ | ✅ | ❌ |
| Delete course | ✅ | ✅ | ❌ | ❌ | ❌ |
| Publish course | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create chapter | ✅ | ✅ | ✅ | ❌ | ❌ |
| Create lesson | ✅ | ✅ | ✅ | ✅ | ❌ |
| Manage media | ✅ | ✅ | ✅ | ✅ | ❌ |
| Manage categories | ✅ | ✅ | ❌ | ❌ | ❌ |

### Assessment

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Create quiz | ✅ | ✅ | ✅ | ❌ | ❌ |
| Manage questions | ✅ | ✅ | ✅ | ❌ | ❌ |
| Take quiz | ✅ | ✅ | ✅ | ❌ | ✅ |
| Grade quiz | ✅ | ✅ | ✅ (own) | ❌ | ❌ |
| View all results | ✅ | ✅ | ❌ | ❌ | ❌ |
| View own results | ✅ | ✅ | ✅ | ❌ | ✅ |
| Override score | ✅ | ✅ | ❌ | ❌ | ❌ |

### Commerce

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| View all orders | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage orders | ✅ | ✅ | ❌ | ❌ | ❌ |
| Process refunds | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage memberships | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage coupons | ✅ | ✅ | ❌ | ❌ | ❌ |
| Purchase course | ✅ | ✅ | ✅ | ❌ | ✅ |
| View own orders | ✅ | ✅ | ✅ | ❌ | ✅ |

### Users & Reports

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Manage users | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assign roles | ✅ | ❌ | ❌ | ❌ | ❌ |
| View reports | ✅ | ✅ | ✅ (own) | ❌ | ❌ |
| Export data | ✅ | ✅ | ❌ | ❌ | ❌ |
| System settings | ✅ | ❌ | ❌ | ❌ | ❌ |

### Certificate

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Manage templates | ✅ | ✅ | ❌ | ❌ | ❌ |
| Issue certificate | ✅ | ✅ | ✅ (own) | ❌ | ❌ |
| View all certificates | ✅ | ✅ | ✅ (own) | ❌ | ❌ |
| View own certificates | ✅ | ✅ | ✅ | ❌ | ✅ |
| Revoke certificate | ✅ | ✅ | ❌ | ❌ | ❌ |

### Communication

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Manage forum | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create forum topic | ✅ | ✅ | ✅ | ❌ | ✅ |
| Reply forum | ✅ | ✅ | ✅ | ❌ | ✅ |
| Moderate comments | ✅ | ✅ | ✅ (own course) | ❌ | ❌ |
| Rate course | ✅ | ✅ | ✅ | ❌ | ✅ |
| Send notifications | ✅ | ✅ | ✅ (own students) | ❌ | ❌ |

### Gamification

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Manage badges | ✅ | ✅ | ❌ | ❌ | ❌ |
| View badges | ✅ | ✅ | ✅ | ✅ | ✅ |
| Award badge manually | ✅ | ✅ | ❌ | ❌ | ❌ |

### AI & Blog

| Permission | Admin | Manager | Instructor | Editor | Student |
|-----------|:-----:|:-------:|:---------:|:------:|:-------:|
| Admin AI/Blog settings | ✅ | ❌ | ❌ | ❌ | ❌ |
| Build course via AI | ✅ | ✅ | ✅ | ❌ | ❌ |
| Generate blog with AI | ✅ | ✅ | ✅ | ✅ | ❌ |
| Publish blog post | ✅ | ✅ | ❌ | ❌ | ❌ |

---

*End of C02-user-roles.md*
