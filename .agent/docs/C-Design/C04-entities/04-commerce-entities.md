---
doc_id: C04-04
title: Commerce Entities
layer: C-Design / C04-entities
updated: 2026-06-20
entities:
  - lms_order
  - lms_order_item
  - lms_membership_plan
  - lms_user_membership
  - lms_wishlist
  - lms_course_bundle
---

# C04-04 — Commerce Entities

Covers the six entities that back purchasing, subscriptions, wishlists, and course bundles. Owner modules: [`lms_order`](../C05-modules/lms_order.md), [`lms_membership`](../C05-modules/lms_membership.md), [`lms_wishlist`](../C05-modules/lms_wishlist.md), [`lms_bundle`](../C05-modules/lms_bundle.md).

Payment gateway architecture note: the `lms_payment` plugin system (`PaymentGatewayManager` + `@PaymentGateway` annotation) is the runtime dispatch layer. Stripe (`lms_stripe`) is real; PayPal (plugin in `lms_payment`) is stub; ZaloPay is stub; `lms_paypal` module is dead code (wrong interface, no annotation). No webhook endpoints exist. See [`lms_payment`](../C05-modules/lms_payment.md) for full detail.

---

## Entity: lms_order (Order)

---
```
entity: lms_order
mode: as-built
owner_module: lms_order
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_order` (single flat table, InnoDB) + `lms_order__field_items` junction table
**Description:** A purchase record for one or more courses or a membership plan; drives enrollment on completion.
**Owner module:** [`lms_order`](../C05-modules/lms_order.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** `/manage/orders` (OrderListBuilder) — view/edit/approve
**Create / Edit / Delete forms:** `entity.lms_order.edit_form` (admin); programmatic create via `CheckoutController`
**Display modes:** N/A (rendered via `lms-checkout.html.twig`, `lms-bank-transfer.html.twig`)
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Buyer | entity_reference | 1 | — | — | No | → `user` | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_order_number` | Order Number | string (32) | 1 | Yes | — | No | — | Base field | Human-readable; generated at checkout |
| 5 | `field_items` | Items | entity_reference | unlimited | — | — | No | → `lms_order_item` | Junction: `lms_order__field_items` | |
| 6 | `field_subtotal` | Subtotal | decimal (10,2) | 1 | — | 0 | No | — | Base field | Pre-discount total |
| 7 | `field_discount` | Discount | decimal (10,2) | 1 | — | 0 | No | — | Base field | Coupon or plan discount amount |
| 8 | `field_total` | Total | decimal (10,2) | 1 | — | 0 | No | — | Base field | Final amount charged |
| 9 | `field_currency` | Currency | string (3) | 1 | — | `USD` | No | ISO 4217 | Base field | |
| 10 | `field_status` | Status | list_string | 1 | — | `pending` | No | `pending`, `processing`, `completed`, `cancelled`, `refunded` | Base field | Enrollment triggered on `completed` |
| 11 | `field_payment_method` | Payment Method | string (32) | 1 | — | — | No | — | Base field | e.g. `stripe`, `bank_transfer`, `paypal` |
| 12 | `field_payment_id` | Payment ID | string (128) | 1 | — | — | No | — | Base field | Gateway transaction / PaymentIntent ID |
| 13 | `field_coupon_code` | Coupon Code | string (32) | 1 | — | — | No | — | Base field | Applied coupon string (not an entity ref) |
| 14 | `field_reminder_sent` | Reminder Sent | boolean | 1 | — | FALSE | No | — | Base field | Pending payment reminder flag |
| 15 | `field_payment_receipt` | Payment Receipt | image | 1 | — | — | No | → `file_managed` | Base field | Added by `lms_order_update_10001`; bank transfer receipt upload |
| 16 | `created` | Created | created (Unix int) | 1 | — | auto | No | — | Base field | |
| 17 | `changed` | Changed | changed (Unix int) | 1 | — | auto | No | — | Base field | |

#### Physical table schema (`lms_order`, AUTO_INCREMENT ~77)

```
id                                  int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                                varchar(128) UNIQUE
uid                                 int unsigned   FK → users_field_data.uid
field_order_number                  varchar(32)
field_subtotal                      decimal(10,2)
field_discount                      decimal(10,2)
field_total                         decimal(10,2)
field_currency                      varchar(3)
field_status                        varchar(255)   pending/processing/completed/cancelled/refunded
field_payment_method                varchar(32)
field_payment_id                    varchar(128)
field_coupon_code                   varchar(32)
field_reminder_sent                 tinyint
created                             int
changed                             int
field_payment_receipt__target_id    int unsigned   FK → file_managed
field_payment_receipt__alt          varchar(512)
field_payment_receipt__title        varchar(1024)
field_payment_receipt__width        int
field_payment_receipt__height       int
```

**lms_order__field_items** (junction):
```
bundle, deleted, entity_id (FK→lms_order.id), revision_id, langcode, delta
field_items_target_id   int unsigned   FK → lms_order_item.id
```

**Data presence:** ~76 orders. Status column contains `pending/paid/failed/refunded` values in DB — note the DB values differ slightly from the PHP allowed-values definition (`pending/processing/completed/cancelled/refunded`); `paid` and `failed` appear in actual data.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |
| — | `uid` | (FK pattern) | Orders by user |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_order_item` | 1:M | `lms_order__field_items.field_items_target_id` | Delete order does not cascade-delete items (Drupal default) |

### State machine

```
pending → processing → completed
pending → cancelled
completed → refunded
```

- Enrollment trigger fires on `completed` via `hook_entity_insert` and `hook_entity_update` in `lms_order.module`.
- The actual status values written to the DB at runtime include `paid` (set by some gateway paths) — mapping to the `completed` trigger is done by checking status == `completed` in the hook helper.

### Business rules

- When `field_status` transitions to `completed`, `_lms_order_process_completed_order()` runs: iterates `field_items`; for course items creates `lms_user_course` (enrollment); for membership items creates `lms_user_membership`; sends confirmation email via `hook_mail` key `order_completed`; creates in-app notification.
- Draft described `field_coupon` as an entity reference to an `lms_coupon` entity. **No `lms_coupon` entity exists**; the field is `field_coupon_code` (plain string). No coupon validation logic is implemented.
- Draft described auto-revoke enrollment on `refunded`. **This is NOT implemented** — there is no code that revokes `lms_user_course` on refund.
- Draft described invoice PDF generation. **Not implemented** — no PDF generation on order completion.
- **SECURITY RISK:** Stripe `PaymentIntent::create()` is called in `CheckoutForm::buildForm()` (on every page load, not just on submit) — one PaymentIntent is created per page load, not per submission.
- `field_payment_receipt` stores a bank transfer payment receipt image uploaded by the customer via `BankTransferForm`.

### Editorial usability

| Surface | Requirement |
|---|---|
| Admin list | `/manage/orders` — `OrderListBuilder`; columns: ID, Order Number, Customer, Total, Payment Method, Status, Created |
| Admin view/edit | `/manage/orders/{lms_order}` |
| Permission | `administer lms_order` (admin), `view own lms_order` (student), `create lms_order` |
| Cache invalidation | No explicit cache tags for order entities in current code |

---

## Entity: lms_order_item (Order Item)

---
```
entity: lms_order_item
mode: as-built
owner_module: lms_order
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_order_item` (single flat table, InnoDB)
**Description:** A line item inside an order — either a course purchase or a membership plan purchase.
**Owner module:** [`lms_order`](../C05-modules/lms_order.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** Accessed only via parent order; no standalone admin UI
**Create / Edit / Delete forms:** Programmatic only (via `CheckoutController`)
**Display modes:** N/A
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `field_course` | Course | entity_reference | 1 | — | — | No | → `lms_course` | Base field | NULL when item is membership type |
| 4 | `field_membership_plan` | Membership Plan | entity_reference | 1 | — | — | No | → `lms_membership_plan` | Base field | NULL when item is course type |
| 5 | `field_price` | Price | decimal (10,2) | 1 | — | 0 | No | — | Base field | Price at time of purchase (snapshot) |
| 6 | `field_type` | Type | list_string | 1 | — | `course` | No | `course`, `bundle`, `membership` | Base field | `bundle` value allowed but no bundle checkout implemented |

#### Physical table schema (`lms_order_item`, AUTO_INCREMENT ~91)

```
id                      int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                    varchar(128) UNIQUE
field_course            int unsigned   FK → lms_course.id (nullable)
field_price             decimal(10,2)
field_type              varchar(255)   course / membership / bundle
field_membership_plan   int unsigned   FK → lms_membership_plan.id (nullable)
```

**Data presence:** ~90 rows. No `field_quantity` column — draft described this but it was never implemented; each item is a single unit.

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_order` | M:1 | `lms_order__field_items` junction | — |
| `lms_course` | M:1 | `field_course` | — |
| `lms_membership_plan` | M:1 | `field_membership_plan` | — |

### Business rules

- Either `field_course` or `field_membership_plan` is set; the other is NULL. The `field_type` discriminator determines which: `course` → `field_course` populated; `membership` → `field_membership_plan` populated.
- `field_price` is a snapshot of the price at purchase time — not recalculated from the current course price.
- `bundle` is a valid `field_type` value but no code currently creates bundle-type order items (the `lms_course_bundle` entity is schema-only; see below).
- Draft field `field_quantity` does not exist in the DB or entity definition.

---

## Entity: lms_membership_plan (Membership Plan)

---
```
entity: lms_membership_plan
mode: as-built
owner_module: lms_membership
content_kind: editorial
editor_owned: true
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_membership_plan` (flat table) + `lms_membership_plan__field_features` junction
**Description:** A subscription tier offering time-bounded access to courses; configured by administrators.
**Owner module:** [`lms_membership`](../C05-modules/lms_membership.md)
**Mode:** as-built
**Content kind:** editorial
**Editor owned:** true
**Management UI:** `/workspace/pricing` (MembershipAdminController) and auto-generated `/manage/memberships`
**Create / Edit / Delete forms:** `/workspace/pricing/add`, `/workspace/pricing/{id}/edit`; also `entity.lms_membership_plan.add_form`, `edit_form`, `delete_form`
**Display modes:** `lms-admin-pricing.html.twig` (admin list), `lms-my-membership.html.twig` (student portal)
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `name` | Name | string (255) | 1 | Yes | — | No | — | Base field | Label key; plan display name |
| 4 | `field_description` | Description | text_long | 1 | — | — | No | — | Base field | |
| 5 | `field_price` | Price | decimal (10,2) | 1 | — | 0 | No | — | Base field | Monthly/cycle price |
| 6 | `field_duration_days` | Duration (Days) | integer | 1 | — | 30 | No | — | Base field | Access period length |
| 7 | `field_billing_cycle` | Billing Cycle | list_string | 1 | — | `monthly` | No | `monthly`, `quarterly`, `yearly`, `lifetime` | Base field | |
| 8 | `field_features` | Features | string (255) | unlimited | — | — | No | — | Junction: `lms_membership_plan__field_features` | Bullet-point feature strings |
| 9 | `field_course_access` | Course Access | list_string | 1 | — | `all` | No | `all`, `selected` | Base field | Access scope; `selected` is not fully implemented |
| 10 | `field_trial_days` | Trial Days | integer | 1 | — | 0 | No | — | Base field | |
| 11 | `field_recommended` | Recommended | boolean | 1 | — | FALSE | No | — | Base field | UI highlight flag on pricing page |
| 12 | `status` | Active | boolean | 1 | — | TRUE | No | — | Base field | Active/inactive toggle |

#### Physical table schema (`lms_membership_plan`, AUTO_INCREMENT ~10)

```
id                          int unsigned NOT NULL AUTO_INCREMENT  PK
uuid                        varchar(128) UNIQUE
name                        varchar(255)
field_description__value    longtext
field_description__format   varchar(255)
field_price                 decimal(10,2)
field_duration_days         int
field_billing_cycle         varchar(255)   monthly/quarterly/yearly/lifetime
field_course_access         varchar(255)   all/selected
status                      tinyint
field_trial_days            int
field_recommended           tinyint
```

**lms_membership_plan__field_features** (junction):
```
field_features_value varchar(255)   feature text string (one row per bullet)
```

**Data presence:** ~9 plans. Features junction has data.

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_user_membership` | 1:M | `lms_user_membership.field_plan` | — |
| `lms_order_item` | 1:M | `lms_order_item.field_membership_plan` | — |

### Business rules

- `status = FALSE` hides the plan from the pricing page (enforced by controller query, not by access control).
- `field_course_access = all` grants access to all published courses. `field_course_access = selected` is a valid value but **no course-selection mechanism is implemented** — there is no field to list specific courses on the plan.
- `field_trial_days > 0` enables trial subscriptions; trial `lms_user_membership` rows have `field_status = trial`.
- Draft listed `field_tier` (basic/premium/enterprise) and `weight`. Neither exists in the actual entity or DB.
- Draft listed `field_courses` (multi-value entity reference). This does not exist. Course selection is via `field_course_access = selected` but with no associated course list field.

### Editorial usability

| Surface | Requirement |
|---|---|
| Admin list | `/workspace/pricing` |
| Add | `/workspace/pricing/add` |
| Edit | `/workspace/pricing/{lms_membership_plan}/edit` |
| Toggle active | `/workspace/pricing/{lms_membership_plan}/toggle` |
| Permission | `administer lms_course` |

---

## Entity: lms_user_membership (User Membership)

---
```
entity: lms_user_membership
mode: as-built
owner_module: lms_membership
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_user_membership` (single flat table, InnoDB)
**Description:** A student's active subscription to a membership plan; created when an order completes or when manually assigned.
**Owner module:** [`lms_membership`](../C05-modules/lms_membership.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** `/workspace/user-memberships` (MembershipAdminController)
**Create / Edit / Delete forms:** `/workspace/user-memberships/add`, `/workspace/user-memberships/{id}/edit` (uses `manager` form mode); also auto-generated Drupal admin forms
**Display modes:** `lms-my-membership.html.twig` (student portal at `/my-membership`)
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Student | entity_reference | 1 | — | — | No | → `user` (autocomplete widget) | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_plan` | Plan | entity_reference | 1 | Yes | — | No | → `lms_membership_plan` | Base field | |
| 5 | `field_start_date` | Start Date | timestamp (Unix int) | 1 | — | — | No | — | Base field | |
| 6 | `field_end_date` | End Date | timestamp (Unix int) | 1 | — | — | No | — | Base field | Expiry checked by cron |
| 7 | `field_status` | Status | list_string | 1 | — | `active` | No | `active`, `trial`, `expired`, `cancelled` | Base field | |
| 8 | `field_auto_renew` | Auto Renew | boolean | 1 | — | TRUE | No | — | Base field | If FALSE: expires without renewal attempt |
| 9 | `created` | Created | created (Unix int) | 1 | — | auto | No | — | Base field | |

#### Physical table schema (`lms_user_membership`, AUTO_INCREMENT ~48)

```
id              int unsigned NOT NULL AUTO_INCREMENT  PK
uuid            varchar(128) UNIQUE
uid             int unsigned   FK → users_field_data.uid
field_plan      int unsigned   FK → lms_membership_plan.id
field_start_date int
field_end_date  int
field_status    varchar(255)   active / expired / cancelled
field_auto_renew tinyint
created         int
```

**Data presence:** ~47 rows. DB `field_status` values are `active/expired/cancelled` — the `trial` value is defined in PHP but may not appear in current data.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_membership_plan` | M:1 | `field_plan` | — |

### State machine

```
active ──→ expired    (cron: field_end_date <= now AND field_auto_renew = FALSE)
trial  ──→ expired    (same cron condition)
active ──→ cancelled  (student: POST /my-membership/{id}/cancel sets field_auto_renew=FALSE)
```

- Cancellation in the current implementation sets `field_auto_renew = FALSE`, not `field_status = cancelled` immediately. The membership remains `active` until cron sets it to `expired`.
- Auto-renewal on expiry is **not implemented**: `hook_cron` logs a message and defers to "payment gateway webhook" which has no webhook endpoint.

### Business rules

- `MembershipManager::hasActiveMembership()` is the canonical access-check: returns TRUE if user has a `lms_user_membership` with `field_status` in `[active, trial]` AND `field_end_date > now()`, OR if user has `administer lms_course` permission.
- Expiry cron (`hook_cron` in `lms_membership.module`): queries memberships where `field_end_date <= now()` and status in `[active, trial]`. If `field_auto_renew = FALSE`: sets `field_status = expired`, sends `membership_expired` email. If `field_auto_renew = TRUE`: logs but does NOT renew (webhook-based renewal is a TODO).
- Draft described `field_subscription_id` (Stripe/PayPal subscription ID). **This field does not exist** — no recurring payment subscription is implemented; the system charges on checkout only.
- Draft described 3-day grace period on payment failure. **Not implemented.**

### Editorial usability

| Surface | Requirement |
|---|---|
| Student portal | `GET /my-membership` → MembershipController::myMembership |
| Student cancel | `POST /my-membership/{id}/cancel` → sets auto_renew=FALSE |
| Admin list | `/workspace/user-memberships` |
| Admin add | `/workspace/user-memberships/add` |
| Admin edit | `/workspace/user-memberships/{id}/edit` (manager form mode) |
| Permission | `administer lms_course` for admin; `_user_is_logged_in: TRUE` for student routes |
| Cache invalidation | No explicit tags in current code |

---

## Entity: lms_wishlist (Wishlist Item)

---
```
entity: lms_wishlist
mode: as-built
owner_module: lms_wishlist
content_kind: transactional
editor_owned: false
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_wishlist` (single flat table, InnoDB)
**Description:** A saved course on a student's wishlist; one row per student-course pair.
**Owner module:** [`lms_wishlist`](../C05-modules/lms_wishlist.md)
**Mode:** as-built
**Content kind:** transactional
**Editor owned:** false
**Management UI:** No admin UI; student-facing only
**Create / Edit / Delete forms:** N/A (API: `POST /api/lms/wishlist/toggle`)
**Display modes:** `lms-my-wishlist.html.twig` at `/my-wishlist`
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `uid` | Student | entity_reference | 1 | — | — | No | → `user` | Base field | Owner via `EntityOwnerTrait` |
| 4 | `field_course` | Course | entity_reference | 1 | Yes | — | No | → `lms_course` | Base field | |
| 5 | `created` | Added At | created (Unix int) | 1 | — | auto | No | — | Base field | Label is "Added At" |

#### Physical table schema (`lms_wishlist`, AUTO_INCREMENT ~85)

```
id              int unsigned NOT NULL AUTO_INCREMENT  PK
uuid            varchar(128) UNIQUE
uid             int unsigned   FK → users_field_data.uid
field_course    int unsigned   FK → lms_course.id
created         int
```

**Data presence:** ~84 rows.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | PK |
| — | `uuid` | UNIQUE | Entity UUID lookup |

No composite unique index on `(uid, field_course)` — de-duplication enforced by `WishlistController::toggle` (queries before insert).

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` | M:1 | `uid` | — |
| `lms_course` | M:1 | `field_course` | — |

### Business rules

- Toggle semantics: `POST /api/lms/wishlist/toggle` with JSON `{course_id}` — if a row exists for `(uid, course_id)`, it is deleted (returns `status=removed`); otherwise created (returns `status=added`).
- `GET /api/lms/wishlist/status?course_ids=1,2,3` returns a map of `{id: bool}` and total wishlist count.
- Cache tag `lms_wishlist:{uid}` is invalidated on toggle; `/my-wishlist` page caches per user with this tag.
- Draft described a price-drop notification. **Not implemented** — no price-change tracking or notification exists.

### Editorial usability

| Surface | Requirement |
|---|---|
| Student wishlist page | `GET /my-wishlist` → WishlistController::myWishlist |
| Toggle | `POST /api/lms/wishlist/toggle` (JSON body) |
| Status check | `GET /api/lms/wishlist/status?course_ids=` |
| Permission | `_user_is_logged_in: TRUE` on all routes |
| Cache | Per-user cache with tag `lms_wishlist:{uid}` |

---

## Entity: lms_course_bundle (Course Bundle)

---
```
entity: lms_course_bundle
mode: as-built
owner_module: lms_bundle
content_kind: editorial
editor_owned: true
revisionable: false
translatable: false
moderation_workflow: null
```

**Storage:** `lms_course_bundle` (flat table, InnoDB) + `lms_course_bundle__field_courses` junction
**Description:** A named package of courses sold at a bundled price. **Schema-only — no checkout integration or data rows exist.**
**Owner module:** [`lms_bundle`](../C05-modules/lms_bundle.md)
**Mode:** as-built
**Content kind:** editorial
**Editor owned:** true
**Management UI:** Auto-generated Drupal admin routes at `/manage/bundles` (via `AdminHtmlRouteProvider`)
**Create / Edit / Delete forms:** `entity.lms_course_bundle.add_form`, `edit_form`, `delete_form` (auto-generated)
**Display modes:** N/A (no custom templates; entity views not configured)
**Revision / moderation:** No revisions
**Translation:** N/A
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer | 1 | Yes | auto | No | — | Base table PK | |
| 2 | `uuid` | UUID | uuid | 1 | — | auto | No | — | Base field | |
| 3 | `name` | Name | string (255) | 1 | Yes | — | No | — | Base field | Label key |
| 4 | `field_description` | Description | text_long | 1 | — | — | No | — | Base field | |
| 5 | `field_courses` | Courses | entity_reference | unlimited | — | — | No | → `lms_course` | Junction: `lms_course_bundle__field_courses` | |
| 6 | `field_price` | Price | decimal (10,2) | 1 | — | 0 | No | — | Base field | Bundle price |
| 7 | `field_discount_percent` | Discount % | integer | 1 | — | 0 | No | — | Base field | Advertised discount |
| 8 | `field_thumbnail` | Thumbnail | image | 1 | — | — | No | → `file_managed` | Base field | `file_directory: bundles/thumbnails` |
| 9 | `status` | Active | boolean | 1 | — | TRUE | No | — | Base field | |

#### Physical table schema (`lms_course_bundle` — NO AUTO_INCREMENT, NO DATA)

```
id                          int unsigned NOT NULL  PK   (no AUTO_INCREMENT set)
uuid                        varchar(128) UNIQUE
name                        varchar(255)
field_description__value    longtext
field_description__format   varchar(255)
field_price                 decimal(10,2)
field_discount_percent      int
field_thumbnail__target_id  int unsigned   FK → file_managed
field_thumbnail__alt        varchar(512)
field_thumbnail__title      varchar(1024)
field_thumbnail__width      int
field_thumbnail__height     int
status                      tinyint
```

**lms_course_bundle__field_courses** (junction — currently EMPTY):
```
field_courses_target_id int unsigned (FK → lms_course.id)
```

**Data presence:** NONE. No AUTO_INCREMENT value in DB dump — tables were created but never populated.

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_course` | M:M | `lms_course_bundle__field_courses` | — |

### Business rules

- Entity and DB schema exist; CRUD forms are auto-generated by Drupal.
- **No checkout flow for bundles** — `CheckoutController` has no `checkoutBundle` method; `lms_order_item.field_type = bundle` is an allowed value but no code path creates such items.
- **No enrollment logic for bundle purchase** — `_lms_order_process_completed_order` handles `course` and `membership` item types only.
- Draft described `field_original_price` (auto-calculated from courses), `field_savings_percent`, `field_featured`, and `weight`. None of these exist in the entity or DB.
- This entity is effectively a data scaffold awaiting future integration. Do not reference as functional.

### Implementation status

| Feature | Status | Notes |
|---|---|---|
| Entity definition | DONE | Schema exists |
| DB tables | DONE (empty) | Tables created; no data |
| Drupal admin CRUD | DONE | Auto-generated routes `/manage/bundles` |
| Checkout integration | NOT IMPLEMENTED | No controller, no checkout form |
| Bundle enrollment on purchase | NOT IMPLEMENTED | `_lms_order_process_completed_order` does not handle bundle items |
| Frontend listing / display | NOT IMPLEMENTED | No templates, no routes, no views |

---

## Commerce Layer Summary

| Entity | Table | Rows (approx.) | Status |
|---|---|---|---|
| `lms_order` | `lms_order` | ~76 | REAL — fully implemented checkout (Stripe real, PayPal/ZaloPay stubs) |
| `lms_order_item` | `lms_order_item` | ~90 | REAL — course and membership items; bundle type defined but unused |
| `lms_membership_plan` | `lms_membership_plan` | ~9 | REAL — admin CRUD, cron expiry |
| `lms_user_membership` | `lms_user_membership` | ~47 | REAL — enrollment, expiry; auto-renewal stub |
| `lms_wishlist` | `lms_wishlist` | ~84 | REAL — toggle API, listing, cache invalidation |
| `lms_course_bundle` | `lms_course_bundle` | 0 | SCHEMA ONLY — no data, no checkout, no enrollment |

### Known risks (document in E02-issues)

| # | Risk | Entity | Severity |
|---|---|---|---|
| 1 | Stripe `PaymentIntent::create()` called in `buildForm()` — one intent per page load, not per submission | `lms_order` | High |
| 2 | No webhook endpoints for any gateway — async payment confirmation impossible | `lms_order` | High |
| 3 | No enrollment revocation on order `refunded` status | `lms_order` | Medium |
| 4 | `lms_paypal` module `PayPalGateway` implements wrong interface, has no `@PaymentGateway` annotation — never discovered by plugin manager (dead code) | payment layer | Medium |
| 5 | All gateway secrets in committed config YAML / `.env` — «secret in config — see security risk» | payment settings | Critical |
| 6 | `lms_user_membership` cancellation only sets `field_auto_renew=FALSE`; membership stays active until cron | `lms_user_membership` | Low |
| 7 | `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice | `lms_order` | Low |
