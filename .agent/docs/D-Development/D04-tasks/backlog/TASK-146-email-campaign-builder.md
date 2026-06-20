# Task: Email Campaign Builder

**ID:** TASK-146
**Unit:** — (new module `lms_email_campaign`)
**Group:** G-MARKETING
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build an email campaign builder for marketing emails. Admins define an audience filter, design the email using drag-and-drop content sections, schedule the send, optionally run an A/B subject line test, and track open rates and click-through rates. Distinct from the automated weekly digest (TASK-139); this is for one-off or scheduled marketing blasts.

## Context
- `lms_notification` handles transactional email via Drupal mail; campaign builder needs its own sending layer capable of bulk throughput.
- Audience filters draw on User, Enrollment, and User last_login data.
- Tracking requires unique pixel (open) and redirect link (click) per recipient; no third-party ESP required for MVP but must be replaceable.
- A/B test: 50/50 split on subject line; winner not auto-picked in MVP (admin reviews stats and resends if desired).

## Acceptance Criteria
- [ ] Campaign list at `/admin/lms/email-campaigns` (draft, scheduled, sent, paused states)
- [ ] Create/edit campaign wizard: (1) Audience → (2) Design → (3) Schedule → (4) Review
- [ ] Audience filters: by role (Student/Instructor), by course enrolled, by course completed, by last login (within N days / never), by membership plan; combinable with AND
- [ ] Template designer: predefined sections (header image, heading, text block, button CTA, divider, footer); drag-and-drop reorder; per-section inline edit
- [ ] A/B subject test: enter Subject A and Subject B; system splits audience 50/50
- [ ] Schedule: send now OR at a specific datetime (stored in campaign, processed by cron queue)
- [ ] Sending via Drupal Queue API (batch 200/queue item); respects TASK-140 `marketing_email` preference
- [ ] Open tracking: 1×1 pixel PNG route `/email/track/open/{token}` sets `opened_at` on recipient record
- [ ] Click tracking: redirect route `/email/track/click/{token}?url=...` sets `clicked_at`, then 302 to destination
- [ ] Analytics per campaign: recipient count, delivered, open rate %, click rate %, unsubscribes triggered
- [ ] Unsubscribe link in every email footer (GDPR; sets `marketing_email = FALSE` via TASK-140 prefs)

## Technical Notes
- New module: `web/modules/custom/lms_email_campaign/`
- Entities: `EmailCampaign` (name, status, audience_filter_json, template_json, subject_a, subject_b, scheduled_at, sent_at), `EmailCampaignRecipient` (campaign_id, uid, email, subject_variant, open_token, click_token, delivered_at, opened_at, clicked_at)
- Template JSON: array of `{type, props}` blocks; rendered via `CampaignTemplateRenderer::render(json): string` → inline-CSS HTML
- Drag-and-drop UI: Sortable.js (lightweight, no external CDN) for section reordering
- Queue worker: `EmailCampaignSendWorker` — resolves audience, creates recipient rows, sends via `\Drupal::service('plugin.manager.mail')`
- Tracking pixel/redirect: `EmailTrackingController` (no auth required; token is UUID)
- Config: `lms_email_campaign.settings` — `batch_size` (default 200), `from_name`, `from_email`
- SCSS: `_email-campaign-builder.scss`

## Dependencies
- TASK-139 (Weekly digest — shares unsubscribe pattern and GDPR preference flag)
- TASK-140 (Notification preferences — check `marketing_email` before sending)
- TASK-011 (U-044 Email integration — Drupal mail backend)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `CampaignTemplateRendererTest`, audience filter query test, tracking controller test (open/click token lookup)
- [ ] Full campaign lifecycle verified: draft → schedule → sent → analytics populated
- [ ] Unsubscribe sets preference and is excluded from future sends
- [ ] No new debt introduced
