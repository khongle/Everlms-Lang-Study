# Task: Subscription History Page

**ID:** TASK-199
**Unit:** — (lms_membership UI)
**Group:** G-COMMERCE
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Provide users with a chronological history page of all subscription charges: status (paid / failed / refunded), amount, invoice date, billing period start/end, and a link to download the invoice PDF. Users can filter entries by year. Data is sourced from the local `lms_payment` charge table, synced via Stripe webhooks.

## Acceptance Criteria

- [ ] Route `/my/subscription/history` accessible to authenticated users with active or past membership
- [ ] Table columns: Date, Description (plan name + period), Amount, Status badge (Paid/Failed/Refunded), Invoice
- [ ] Status badges use colour coding: green = paid, red = failed, grey = refunded
- [ ] "Download Invoice" link per row that triggers PDF download (delegates to TASK-126 invoice service)
- [ ] Year filter: dropdown with available years; defaults to current year; URL param `?year=2025`
- [ ] Empty state message when no charges in selected year
- [ ] Pagination: 20 rows per page
- [ ] Data served from local DB; Stripe not queried at page-load time (avoids latency)
- [ ] Webhook `invoice.payment_succeeded`, `invoice.payment_failed`, `charge.refunded` keep local table current

## Technical Notes

- Module: `lms_membership` — new route in `lms_membership.routing.yml`, new `SubscriptionHistoryController`
- Query: `SELECT * FROM lms_payment_charges WHERE uid = :uid AND YEAR(created) = :year ORDER BY created DESC`
- `lms_payment_charges` table expected columns: id, uid, stripe_charge_id, amount, currency, status, period_start, period_end, invoice_id, created
- If table does not yet exist, add `hook_schema()` in `lms_payment.install` (coordinate with TASK-198)
- PDF download: call `InvoiceService::generatePdf($charge_id)` from TASK-126; stream as attachment
- Twig template: `lms_membership/templates/subscription-history.html.twig`

## Dependencies

- TASK-126 (invoice PDF generation)
- TASK-029 (Stripe webhooks — charge sync)
- TASK-001 (membership entity — plan name lookup)

## Definition of Done

- [ ] Code complete — controller, query, Twig template, year filter
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (mock charge rows; verify year filter + pagination)
- [ ] No new debt introduced
