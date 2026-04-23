# Tutorial: Admin — Pre-Launch Checklist

> Từ fresh install đến production-ready. Bước qua config từng domain.
> Estimated time: **~2 hours** (không tính chờ DNS propagate / SMTP provisioning).

**Difficulty:** Advanced
**Prerequisites:** Access root / SSH production server. DDEV local đã verify OK.
**Features covered:** Infra + FEAT-07 (users) + payment gateway + SMTP + analytics + security.

## Goal

Sẵn sàng bật production cho real users — tất cả config đã set, security baseline đã achieve, monitoring ON.

## Context

Checklist này **không** substitute cho `F-Release/F02-release-checklist/` cho mỗi release cụ thể. Tutorial này là **first-time production launch**.

## Steps

### Phase 1: Infrastructure (30 min)

1. **Server provisioned** với PHP 8.3 + MariaDB 10.11 + Nginx + Redis.
2. **DNS** pointed: `lms.example.com` → server IP.
3. **TLS cert** (Let's Encrypt hoặc commercial) installed + auto-renew configured.
4. **Firewall**: inbound 80/443 only; SSH key-only, no password.
5. **Backup:** daily DB dump cron; files rsynced to object storage.

### Phase 2: Drupal Install (30 min)

1. `composer install --no-dev -o`.
2. `drush si standard -y` với settings.
3. Set `$settings['trusted_host_patterns']` trong settings.php.
4. Configure Redis cache backend:
   ```php
   $settings['cache']['default'] = 'cache.backend.redis';
   ```
5. Enable all `lms_*` + `open_switcher`:
   ```bash
   drush en -y lms_base lms_course lms_chapter lms_lesson lms_quiz lms_question \
     lms_progress lms_order lms_membership lms_payment lms_certificate lms_forum \
     lms_notification lms_rating lms_note lms_faq lms_badge lms_ai lms_analytics \
     lms_zoom lms_bundle lms_wishlist lms_blog lms_stripe lms_paypal \
     lms_content_tools open_switcher
   ```
6. `drush theme:enable lms_theme -y` + set default.

### Phase 3: Core Config (15 min)

1. **Site info** (`/admin/config/system/site-information`): tên, email admin, timezone.
2. **Cron**: config external cron (recommended) — `drush cron` mỗi 15 min qua system crontab.
3. **File system** (`/admin/config/media/file-system`): public/private paths, temp.
4. **Permissions**: `/admin/people/permissions` — review default role perms (rất quan trọng; RISK-001).

### Phase 4: Payment Gateway (20 min)

⚠️ **TRƯỚC KHI LAUNCH**: Verify TASK-002 + U-060 đã merge. Nếu chưa — không bật payment, chỉ cho free courses.

1. **Stripe**:
   - `/admin/config/lms/payment/stripe`:
     - `publishable_key` (LIVE, không test).
     - `secret_key` (LIVE, set qua env var).
     - `webhook_secret` (tạo webhook endpoint `/payment/stripe/webhook` ở Stripe dashboard; paste secret).
   - Test 1 real transaction small amount → verify enrollment trigger.

2. **PayPal** (⚠️ FIND-002 chưa resolve — **tạm disable**).

### Phase 5: SMTP (10 min)

1. `/admin/config/system/mailsystem` (nếu module mailsystem enabled).
2. Chọn provider: SendGrid / Postmark / AWS SES.
3. Set API key + from address.
4. **Test email**: `/admin/config/system/send-test-mail` hoặc trigger trigger password reset để verify.

### Phase 6: AI Provider (nếu dùng) (15 min)

1. `/admin/config/lms/ai`:
   - Chọn provider default (OpenAI recommended).
   - Set API key (qua env).
   - **Set `max_tokens = 2000`** ⚠️ (FIND-003 mitigation).
   - Daily quota per user: 10.
2. Feature flag `FF_FEAT_AI_BUILDER`: default OFF. Bật chỉ khi test xong.

### Phase 7: Zoom (nếu dùng) (5 min)

1. `/admin/config/lms/zoom`: JWT key + secret từ Zoom marketplace.
2. Test tạo 1 meeting giả.

### Phase 8: Taxonomy & Content Seeding (15 min)

1. `/admin/structure/taxonomy` — review 8 vocabularies (Category, Level, Topic, Skill, Language, Tag, Certificate Type, Badge Level).
2. Tạo initial terms cho mỗi vocab (tối thiểu 5 category).
3. Import 1-2 sample courses để content team có template.

### Phase 9: User Roles (10 min)

1. `/admin/people/roles` — verify 7 roles (Administrator, Manager, Instructor, Content Editor, Student, Authenticated, Anonymous).
2. `/admin/people/permissions` — audit permissions cho từng role. Tham chiếu `C-Design/C02-user-roles.md`.
3. Tạo admin thứ 2 (backup account).

### Phase 10: Analytics (5 min)

1. `/admin/config/lms/analytics` — bật Google Analytics 4 nếu dùng (Measurement ID).
2. Test event fire từ browser (GA4 DebugView).

### Phase 11: Security Baseline (15 min)

1. **Password policy:** `/admin/config/people/password-strength` → medium+.
2. **Session timeout:** 180 min max.
3. **HTTPS-only cookie:** `$settings['cookie_secure'] = TRUE;` trong settings.php.
4. **CSRF tokens:** verify form protection (Drupal core mặc định).
5. **Check RISK register:** `G-Governance/G02-risk-register.md` — tất cả Critical RISK phải mitigated hoặc accepted.
6. **Run `drush core:requirements --severity=2`** — không có Error/Warning security.

### Phase 12: Observability (10 min)

Xem `H-Operations/H07-observability-as-code/`:
1. **Dashboards**: deploy Grafana / Datadog dashboards.
2. **Alerts**: configure SLO burn-rate alert.
3. **Health checks**: `/health`, `/health/db` (nếu routes đã add — còn PROPOSED per A01).

### Phase 13: Smoke Test (15 min)

Checklist:
- [ ] Homepage load < 2s.
- [ ] `/courses` list, filter, sort OK.
- [ ] Register user → confirm email → login.
- [ ] Enroll free course → take lesson → complete → certificate.
- [ ] (Nếu payment bật) Buy paid course với real Stripe → webhook fires → enrollment active.
- [ ] Admin dashboard load < 3s.
- [ ] `drush ws --severity=3` không có fresh error.

### Phase 14: Go Live

1. Remove `maintenance mode` banner nếu đang bật.
2. DNS TTL → normal.
3. Announcement (Slack / email) cho team.
4. Monitor dashboard 2-4 giờ đầu.

## Verification

- [ ] Production URL accessible, HTTPS OK.
- [ ] Real user flow end-to-end OK (Phase 13 smoke test).
- [ ] Admin có thể reset password + assign role.
- [ ] Backup cron runs successfully (check log lần đầu).
- [ ] No Critical RISK unmitigated trong G02.
- [ ] Observability dashboards green.

## Next Steps

- Set up on-call rotation per `A06-oncall-protocol.md`.
- Schedule first maintenance window for updates.
- Create `F02-release-checklist/v1.0.0-launch.md` retro.
- Kick off first post-release monitoring per `M04-post-release-monitoring.md`.

## Troubleshooting

Xem [U05 — Troubleshooting](../U05-troubleshooting.md) cho các lỗi runtime.

For severity-level incident response: `A05-incident-response.md` + `H-Operations/H02-runbooks/`.
