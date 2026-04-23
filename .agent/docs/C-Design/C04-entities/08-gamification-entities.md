# Gamification & Payment Entities

> Badge system + Payment transaction logging.

---

## Badge (`lms_badge`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên badge |
| `field_description` | text_long | Mô tả cách đạt badge |
| `field_icon` | image | Icon/image badge |
| `field_criteria_type` | list_string | `course_completion` / `quiz_master` / `streak` / `community` / `points_threshold` |
| `field_criteria_value` | integer | Giá trị tiêu chí (ví dụ: 5 courses, 10 quizzes, 7 days streak) |
| `field_points_reward` | integer | XP points thưởng khi đạt badge |
| `field_badge_type` | entity_reference (taxonomy: `badge_type`) | Phân loại badge |
| `field_rarity` | list_string | `common` / `rare` / `epic` / `legendary` |
| `status` | boolean | Active / Inactive |

### Badge Criteria Examples
| Type | Criteria | Example |
|------|----------|---------|
| `course_completion` | Complete N courses | Complete 5 courses → "Scholar" badge |
| `quiz_master` | Pass N quizzes with ≥ 90% | Pass 10 quizzes → "Quiz Master" badge |
| `streak` | N consecutive learning days | 7-day streak → "Dedicated Learner" badge |
| `community` | Post N forum replies | 20 replies → "Community Helper" badge |
| `points_threshold` | Reach N total XP | 1000 XP → "Rising Star" badge |

---

## User Badge (`lms_user_badge`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_user` | entity_reference (user) | User nhận badge |
| `field_badge` | entity_reference | Badge |
| `field_awarded_date` | timestamp | Ngày nhận |
| `field_displayed` | boolean | Hiển thị trên profile |

### Business Rules
- Auto-award khi user đạt criteria (event-driven)
- 1 badge chỉ award 1 lần per user — unique constraint `(field_user, field_badge)`
- User chọn badges nào hiện trên public profile (max 5)

---

## Payment Transaction (`lms_payment_transaction`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_order` | entity_reference | Order liên quan |
| `field_membership` | entity_reference | Membership liên quan |
| `field_user` | entity_reference (user) | User thanh toán |
| `field_gateway` | string | `stripe` / `paypal` |
| `field_gateway_transaction_id` | string | Transaction ID từ gateway |
| `field_type` | list_string | `charge` / `refund` / `subscription_renewal` / `subscription_cancel` |
| `field_amount` | decimal | Số tiền |
| `field_currency` | string | Đơn vị tiền tệ |
| `field_status` | list_string | `pending` / `success` / `failed` / `refunded` |
| `field_gateway_response` | text_long | Raw JSON response từ gateway (for debugging) |
| `field_ip_address` | string | IP address của user |
| `created` | timestamp | Thời điểm giao dịch |

### Business Rules
- Mỗi order có thể có nhiều transactions (charge + refund)
- `field_gateway_response` chỉ admin mới xem (sensitive data)
- `field_type = refund` → phải link đến transaction `charge` gốc
- Transaction log immutable — không cho phép edit/delete
