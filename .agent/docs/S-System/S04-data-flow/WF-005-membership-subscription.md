# S04 Data Flow - WF-005 Membership subscription

```text
Buyer (web)
   │ subscribe plan (order paid, WF-004)
   ▼
app-drupal: create/renew/reactivate lms_user_membership ──▶ db-mariadb
   │ read on content-gate check (F-004)
   ▼
MembershipManager::hasActiveMembership() ──▶ grants course access, no per-course row
   │ cron sweep past field_end_date
   ▼
mark expired/archived ──▶ db; access revoked
```
Source: [C12 WF-005](../../C-Design/C12-workflows/WF-005-membership-subscription.md). External: none beyond the WF-004 payment gateway (see WF-004).
