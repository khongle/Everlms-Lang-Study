---
page: manage-notifications-send
mode: as-built
owner_module: lms_notification
route_group: manage
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Manage Notifications — Broadcast Send

> Covers the admin broadcast announcement page at `/admin/lms/notifications/broadcast`. This is the admin-facing "send to all users" page. Per-user notification inbox (`/notifications`) and preferences (`/manage/settings/notifications`) are separate pages in the `app` route group.

**Canonical URL:** `/admin/lms/notifications/broadcast` *(per [`../../C07-sitemap.md`](../../C07-sitemap.md))*
**Route name:** `lms_notification.broadcast`
**Solution:** `BroadcastForm extends FormBase`; batch API sends `lms_notification` entities to all active users in batches of 50; no Twig template (Drupal default form + Claro admin theme).
**Owner module:** [`lms_notification`](../../C05-modules/lms_notification.md)
**Primary source:** Form input (title, message, link); creates `lms_notification` entities programmatically
**Editable source:** `lms_notification` entities (system-generated); form inputs are ephemeral
**Editor role:** Administrator, Manager (`administer lms_course`)
**Editor management UI:** This page
**Revision / moderation:** N/A
**Forms:** `Drupal\lms_notification\Form\BroadcastForm`
**Theme template:** Drupal default form rendering (Claro admin theme)
**Access:** Role-gated: `administer lms_course`
**Cache:** No-cache (admin form)
**Translation:** Monolingual EN
**Related entities:** [`lms_notification`](../../C04-entities/01-content-entities.md)
**Prototype reference:** No C10 prototype.
**Notes:**
- Only in-app channel is functional. Email and push channels are TODO stubs in `NotificationManager::dispatch()`.
- Batch processes 50 users at a time via Drupal Batch API — avoids PHP timeout on large user bases.
- Dispatched notifications are `field_type: announcement`.
- Cron deletes `lms_notification` entities older than 90 days that have been read (100 per cron run).

---

## Components (visual order, top-to-bottom)

> Global chrome defined in [`../_common.md`](../_common.md).

### 1. Page Title — *Tiêu đề trang*

- **Scope:** Page-specific
- **Implementation mechanism:** Route `_title: 'Broadcast Announcement'`; Drupal core title block
- **Plugin / Module:** Drupal core routing
- **Source content type / Entity:** Route definition (`lms_notification.routing.yml`)
- **Backed by fields / config keys:** Route `_title`
- **Editable source:** computed/read-only
- **Editor role / permission:** Developer
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** N/A
- **View mode / Projection:** H1 heading
- **Region / Placement:** `content` region, above form
- **Notes:** —
- **How to implement (EN):** Set in `lms_notification.routing.yml` `_title` key.
- **How to implement (VI):** Đặt trong `lms_notification.routing.yml` tại khoá `_title`.
- **Solution Architect Request:** —

---

### 2. Broadcast Form — *Form gửi thông báo hàng loạt*

- **Scope:** Page-specific (primary)
- **Implementation mechanism:** `BroadcastForm extends FormBase`; `_form` route key; submits to Drupal Batch API
- **Plugin / Module:** `lms_notification` → `src/Form/BroadcastForm.php`
- **Source content type / Entity:** Form input → generates `lms_notification` entities for all active users (uid > 1, `status=1`)
- **Backed by fields / config keys:**
  | Form field | `lms_notification` field | Notes |
  |---|---|---|
  | Title (textfield, required) | `field_title` | max_length 255 |
  | Message (textarea) | `field_message` | text_long |
  | Link (textfield optional) | `field_link` | max_length 512 |
  | — | `field_type` | hardcoded `announcement` |
  | — | `field_channel` | hardcoded `['in_app']` |
  | — | `field_read` | default FALSE |
- **Editable source:** Form is the editor surface; no per-notification edit UI after dispatch
- **Editor role / permission:** `administer lms_course`
- **Editor management UI:** This form page
- **Revision / moderation:** N/A (`lms_notification` entities not revisionable)
- **Translation source:** N/A
- **Edit smoke:** Fill title + message → submit → verify notification appears in test user's `/notifications` inbox
- **View mode / Projection:** Standard Drupal form (Claro admin theme)
- **Region / Placement:** `content` region
- **Notes:**
  - Batch processing: `processBatch()` static method iterates users 50 at a time; calls `lms_notification.manager::dispatch('announcement', $user, title, message, link)`.
  - **Email / push are stubs** — `NotificationManager::dispatch()` for `email` only logs; for `push` only logs. Only in-app notifications are delivered.
  - No undo/recall after dispatch — `lms_notification` entities are created immediately and cannot be recalled via UI.
  - Drush command `lms_notification:remind-inactive` (alias: `lms-remind`) separately targets users 7–14 days inactive with `reminder` type notifications.
- **How to implement (EN):** `BroadcastForm` in `web/modules/custom/lms_notification/src/Form/BroadcastForm.php`. `processBatch()` static method handles the Batch API iteration. Route: `lms_notification.broadcast` at `/admin/lms/notifications/broadcast`.
- **How to implement (VI):** Form tại `src/Form/BroadcastForm.php`. Phương thức `processBatch()` xử lý batch API. Route: `/admin/lms/notifications/broadcast`.
- **Solution Architect Request:** —

---

### 3. Batch Progress Indicator — *Thanh tiến trình batch*

- **Scope:** Page-specific (Drupal Batch API UI)
- **Implementation mechanism:** Drupal core Batch API renders a progress page automatically during batch processing
- **Plugin / Module:** Drupal core Batch API
- **Source content type / Entity:** Batch queue (session-based)
- **Backed by fields / config keys:** computed/read-only (Drupal core)
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** Interface translation (Drupal core)
- **Edit smoke:** Submit broadcast to large user list → observe Drupal batch progress bar
- **View mode / Projection:** Drupal core batch progress page
- **Region / Placement:** Full page (Drupal core batch redirect)
- **Notes:** On completion, redirects back to broadcast form or front page. Standard Drupal batch behavior.
- **How to implement (EN):** Drupal Batch API is invoked automatically by `BroadcastForm::submitForm()` via `batch_set()`.
- **How to implement (VI):** Drupal Batch API được gọi tự động qua `batch_set()` trong `submitForm()`.
- **Solution Architect Request:** —

---

### 4. Status Messages — *Thông báo kết quả*

- **Scope:** Page-specific (post-submit feedback)
- **Implementation mechanism:** Drupal `messenger()` service; set after batch completes
- **Plugin / Module:** Drupal core
- **Source content type / Entity:** computed/read-only (system messages)
- **Backed by fields / config keys:** computed/read-only
- **Editable source:** computed/read-only
- **Editor role / permission:** N/A
- **Editor management UI:** computed/read-only
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Submit form → observe success message after batch completes
- **View mode / Projection:** Drupal status messages block
- **Region / Placement:** `content` region, above form
- **Notes:** Batch finish callback shows count of notifications sent.
- **How to implement (EN):** Standard Drupal messenger; set in batch finish callback.
- **How to implement (VI):** Drupal messenger tiêu chuẩn; được đặt trong batch finish callback.
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Title | "Broadcast Announcement" | Route `_title` | Static | computed/read-only | Developer | N/A | Interface | N/A | N/A |
| Broadcast Form | Title field, Message textarea, Link field, Submit button | Form input → `lms_notification` entities | `field_title`, `field_message`, `field_link`, `field_type=announcement`, `field_channel=['in_app']` | This form page | `administer lms_course` | N/A | EN | no-cache | Submit → check user inbox at `/notifications` |
| Batch Progress | Processing progress bar, percentage, estimated time | Drupal Batch API (session) | computed/read-only | computed/read-only | N/A | N/A | Interface (Drupal core) | Session | Submit large broadcast → observe progress |
| Status Messages | Success/error count after batch | computed/read-only (system) | — | computed/read-only | N/A | N/A | Interface | no-cache | Submit → observe completion message |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Batch size (50 users per batch) | Hardcoded in `BroadcastForm::processBatch()` | D00 code convention | On performance tuning |
| `field_type = 'announcement'` | Hardcoded in batch processor | D00 code convention | On notification type expansion |
| `field_channel = ['in_app']` | Hardcoded in `NotificationManager::dispatch()` at call site | D00 code convention | When email/push channels are implemented |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Broadcast Form (in-app) | DONE | | Fully implemented; batch processing works |
| Email channel dispatch | NOT IMPLEMENTED | — | `NotificationManager::dispatch()` for `email`: only logs (TODO stub) |
| Push channel dispatch | NOT IMPLEMENTED | — | `NotificationManager::dispatch()` for `push`: only logs (stub) |
| Notification cleanup (cron) | DONE | — | `hook_cron()` deletes read+90-day-old notifications, 100 per run |
| Drush remind command | DONE | — | `lms_notification:remind-inactive` targets inactive users (7–14 days) |
