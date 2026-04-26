# Task: Slack / Discord Channel Notifications

**ID:** TASK-188
**Unit:** — (extends TASK-187 webhook system)
**Group:** G-INTEGRATION
**Priority:** P3
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Allow org admins to connect a Slack or Discord channel to EverLMS events using incoming webhook URLs. When a configured event fires, a rich formatted message (course thumbnail + title + CTA link) is posted to the configured channel. This feature extends the TASK-187 webhook system: Slack and Discord are treated as built-in webhook presets with dedicated payload formatters rather than arbitrary HTTP endpoints.

## Acceptance Criteria
- [ ] Admin UI: `/admin/config/lms/webhooks/channel-notifications` — separate section for Slack/Discord (built on top of TASK-187 subscription config)
- [ ] Slack preset: target URL stored as incoming webhook URL; payload formatted as Slack Block Kit message
- [ ] Discord preset: target URL stored as Discord webhook URL; payload formatted as Discord Embed JSON
- [ ] Supported events (v1): `CourseCompleted`, `EnrollmentCreated`, `OrderPaid`, `UserRegistered`, `NewCoursePublished`
- [ ] Slack Block Kit message includes: course thumbnail image, course title, student name (for per-student events), CTA button linking to course
- [ ] Discord embed includes: `title`, `description`, `thumbnail.url`, `color` (brand color), `url` (course link)
- [ ] Test button in admin UI sends a sample payload to confirm connectivity
- [ ] Works for all orgs independently (each org configures their own channels)
- [ ] Unit test: Slack formatter produces correct Block Kit JSON structure for each event type

## Technical Notes
- Extend: `web/modules/custom/lms_webhook/`
- `src/Formatter/SlackMessageFormatter.php` — `format(string $event, array $data): array` → Slack Block Kit array
- `src/Formatter/DiscordMessageFormatter.php` — `format(string $event, array $data): array` → Discord Embed array
- `src/Plugin/WebhookPreset/SlackPreset.php` — plugin defining default URL pattern, formatter class, supported events
- `src/Plugin/WebhookPreset/DiscordPreset.php` — same for Discord
- Integration with TASK-187 `WebhookDeliveryWorker`: before HTTP POST, check if subscription has a `preset`; if so, transform payload via preset's formatter
- Test button: `POST /admin/config/lms/webhooks/{id}/test` → enqueues test delivery with sample data
- Slack Block Kit docs reference: `https://api.slack.com/block-kit` (implementation only; no external calls at build time)
- Discord embed color: map event type to brand color constant in `LmsWebhookColors` class

## Dependencies
- TASK-187 (outbound webhook system — queue, delivery worker, subscription config entities must exist)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: SlackMessageFormatter + DiscordMessageFormatter output structure)
- [ ] Test button confirmed working against real Slack/Discord webhook URLs in staging
