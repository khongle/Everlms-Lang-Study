# S04 Data Flow - WF-007 User registration & onboarding

```text
Visitor (web) / Mobile client
   │ web: POST /user/register
   ▼
Drupal core user module: create user active + lms_base_user_insert() role=student ──▶ db-mariadb
   │ mobile: POST /api/v1/user/register
   ▼
lms_mobile_api: create user blocked + OTP in expirable keyvalue store ──▶ db + keyvalue
   │ POST /api/v1/user/register/verify-otp
   ▼
activate user + issue bearer token ──▶ db update; token returned to client
```
Source: [C12 WF-007](../../C-Design/C12-workflows/WF-007-user-registration-and-onboarding.md). External: none (OTP is an in-process expirable keyvalue, not an SMS/email gateway yet).
