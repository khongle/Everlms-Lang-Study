# Task: Outbound Webhook System (Zapier / Make / n8n Integration)

**ID:** TASK-187
**Unit:** — (new module: lms_webhook)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a generic outbound webhook system so org admins and power users can connect EverLMS events to automation platforms (Zapier, Make, n8n, custom endpoints). Admin configures one or more webhook subscriptions: target URL + list of events to send. On event fire, a signed JSON payload is delivered via Drupal Queue (async). Failed deliveries are retried with exponential back-off up to 5 attempts, reusing the idempotency pattern established in TASK-050. A new module `lms_webhook` owns all logic.

## Acceptance Criteria
- [ ] `lms_webhook` module scaffolded with `.info.yml`, services, schema, queue
- [ ] Admin UI: `/admin/config/lms/webhooks` — list, create, edit, delete webhook subscriptions
- [ ] Subscription config: target URL, secret (for HMAC-SHA256 signature), enabled events (multi-select), active toggle
- [ ] Supported events (v1): `CourseCompleted`, `EnrollmentCreated`, `OrderPaid`, `OrderRefunded`, `UserRegistered`, `QuizPassed`
- [ ] Each event fires by subscribing to corresponding Symfony event from relevant module
- [ ] Delivery: enqueue `WebhookDeliveryItem` to Drupal Queue; worker sends HTTP POST with `Content-Type: application/json`
- [ ] Payload: `{event, timestamp_utc, data: {...}}` — data shape per event documented in OpenAPI (extend TASK-074)
- [ ] Signature: `X-LMS-Signature: sha256={hmac}` header; secret set per subscription; allows Zapier/Make to verify
- [ ] Retry: up to 5 attempts, back-off 1 min → 5 min → 15 min → 60 min → 240 min; after 5th failure mark delivery `failed` in log
- [ ] Delivery log table `lms_webhook_delivery_log` (`id`, `subscription_id`, `event`, `status`, `attempts`, `last_attempt`, `response_code`)
- [ ] PHPUnit: queue worker sends correct payload + correct signature; retry counter increments on non-2xx

## Technical Notes
- New module: `web/modules/custom/lms_webhook/`
- `src/Service/WebhookDispatcher.php` — `dispatch(string $event, array $data)` → enqueues delivery item
- `src/Plugin/QueueWorker/WebhookDeliveryWorker.php` — processes queue, HTTP POST via `\Drupal::httpClient()`; stores delivery log; re-queues with delay on failure
- `src/EventSubscriber/LmsEventToWebhookSubscriber.php` — listens to all supported LMS events, calls `WebhookDispatcher::dispatch()`
- Config entity: `lms_webhook_subscription` — stores url, secret_hash, events[], active
- DB schema: `lms_webhook_delivery_log` — add via `hook_schema()` in `lms_webhook.install`
- HMAC: `hash_hmac('sha256', $rawBody, $secret)` — compute over raw JSON body before sending
- Idempotency: include `X-LMS-Delivery-Id` UUID header (persist in delivery log); reuse pattern from TASK-050
- HTTP client config: timeout 10s, connect timeout 5s; no redirect follow (security)

## Dependencies
- TASK-050 (idempotency pattern — reuse outbound delivery UUID approach)
- TASK-074 (OpenAPI spec — extend with webhook payload schemas)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: dispatcher + worker + signature; Behat: webhook fires on CourseCompleted event)
- [ ] Payload schemas documented in OpenAPI spec (update TASK-074 artifact)
- [ ] Rate limiting on delivery log query confirmed (index on `subscription_id + last_attempt`)
