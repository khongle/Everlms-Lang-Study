---
solution_id: SOL-004
name: Custom Bearer Token API via keyvalue.expirable + OTP
applies_to: "any Drupal module that must serve a mobile/native app with stateless bearer token auth when OAuth2/JWT infra is unavailable or over-engineered for the context"
derived_from_issue: «no E02 issue yet — pattern extracted from as-built lms_mobile_api code»
owning_story: lms_mobile_api
lesson: K04-keyvalue-bearer-token-for-mobile-api
status: ACTIVE
---

# E08 — Solution: Custom Bearer Token API

> One-line purpose: documents how `lms_mobile_api` implements its own stateless bearer token auth using Drupal's built-in `keyvalue.expirable` service and an OTP registration/password-reset flow — why OAuth2 was not used, what the X-Authorization header workaround covers, and what the real security gaps are.

---

## Problem

The Flutter mobile app (`app/` v1.0.10+33) needs stateless bearer token authentication. Drupal's default session/cookie auth does not work for native mobile clients.

Standard solutions involve `simple_oauth` (OAuth2/PKCE) or `jwt` contrib modules, but:
- Both require significant additional configuration and composer dependencies.
- `simple_oauth` requires client registration, scope configuration, and either Authorization Code or Client Credentials grant flows.
- The project is in early development; an in-house approach trades long-term correctness for immediate velocity.

Additionally, user registration must be OTP-verified (email code) and password reset must go through a separate OTP challenge — neither of which is standard Drupal behavior.

A further hosting constraint: the server's nginx/PHP-FPM configuration strips the `Authorization: Bearer ...` HTTP header before it reaches PHP (a common server-level Basic Auth gate). The header must be delivered under a non-standard name.

---

## Solution

`lms_mobile_api` implements its own three-layer token system entirely on top of Drupal's `keyvalue.expirable` service — no OAuth2 library, no JWT.

### Layer 1 — OTP session (registration + forgot-password)

```
keyvalue.expirable collection: lms_mobile_api.otp
TTL: 900 s (15 min) — configurable via lms_mobile_api.settings:otp_expiry_seconds
Key format: 64-char hex token (bin2hex(random_bytes(32)))
Value: ['uid' => int, 'otp_code' => string, 'type' => 'register'|'forgot_password']
```

Issued by `OtpService::storeOtpSession()`. Consumed and deleted by `MobileAuthController::verifyRegisterOtp()` or `verifyForgotPasswordOtp()`.

OTP code: 6-digit numeric string (`random_int(100000, 999999)`).

### Layer 2 — Reset session (password-reset)

```
keyvalue.expirable collection: lms_mobile_api.reset
TTL: 1800 s (30 min) — configurable via lms_mobile_api.settings:reset_token_expiry_seconds
Key format: 64-char hex token (bin2hex(random_bytes(32)))
Value: ['uid' => int]
```

Issued after OTP verification for `forgot_password` flow. Consumed and deleted by `resetPassword()`.

### Layer 3 — Bearer access token (API session)

```
keyvalue.expirable collection: lms_mobile_api.tokens
TTL: 604800 s (7 days) — configurable via lms_mobile_api.settings:token_expiry_seconds
Key format: 32-char hex token (bin2hex(random_bytes(16)))
Value: int $uid
```

Issued by `MobileAuthController::issueAccessToken(int $uid)` — called on:
- Successful OTP verify (registration)
- Successful password reset
- Successful `POST /api/v1/oauth/token` (credential login)

No refresh token mechanism exists. When the 7-day TTL expires, the user must re-authenticate.

### Token resolution (as-built — duplicated across 3 controllers)

```php
// Private method duplicated in MobileAuthController, MobileCourseController, MobileLearningController
private function getAuthenticatedUser(Request $request): UserInterface {
    // 1. Try X-Authorization header first (workaround for stripped Authorization header)
    $auth_header = $request->headers->get('X-Authorization')
                ?? $request->headers->get('Authorization');

    if (!$auth_header || !str_starts_with($auth_header, 'Bearer ')) {
        throw new AccessDeniedHttpException('Missing or invalid token.');
    }

    $token = substr($auth_header, 7);

    // 2. Look up UID from keyvalue store
    $store = \Drupal::keyValueExpirable('lms_mobile_api.tokens');
    $uid   = $store->get($token);

    if (!$uid) {
        throw new AccessDeniedHttpException('Invalid or expired token.');
    }

    // 3. Load and validate user
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if (!$user || !$user->isActive()) {
        throw new AccessDeniedHttpException('User account is inactive.');
    }

    return $user;
}
```

**Defect:** This method is **copied verbatim** into all three controllers (`MobileAuthController`, `MobileCourseController`, `MobileLearningController`). Any fix must be applied in three places. The correct refactor is to extract a `MobileAuthTrait` or a dedicated `TokenAuthService` injectable service.

### X-Authorization header workaround

The standard header name is `Authorization`. However, many nginx configurations (including DDEV's default) intercept `Authorization: Bearer ...` for HTTP Basic Auth processing before it reaches PHP's `$_SERVER`. The module reads `X-Authorization` first, then falls back to `Authorization`. Flutter clients must send:

```
X-Authorization: Bearer <token>
```

This is non-standard. It works but requires explicit documentation in the Flutter client (`app/`).

### OTP email delivery

Handled by `hook_mail` in `lms_mobile_api.module`:

```php
case 'otp_email':
    $message['subject'] = ($params['type'] === 'register')
        ? t('Your EverLMS verification code')
        : t('Your EverLMS password reset code');
    $message['body'][] = t('Your code is: @code (expires in 15 minutes).', ['@code' => $params['otp_code']]);
```

Dispatched by `OtpService::sendOtpEmail()` via Drupal's `plugin.manager.mail`.

### Complete auth flows

**Registration:**
```
POST /api/v1/user/register          → user created (status=0), OTP sent → {otp_token}
POST /api/v1/user/register/verify-otp → user activated, bearer token issued → {access_token}
POST /api/v1/user/register/resend-otp → new OTP code sent, new otp_token returned (old invalidated)
```

**Login:**
```
POST /api/v1/oauth/token            → password check → bearer token issued → {access_token}
```

**Password reset:**
```
POST /api/v1/user/forgot-password   → OTP sent (always 200 — no enumeration) → {otp_token}
POST /api/v1/user/forgot-password/verify-otp → {reset_token}
POST /api/v1/user/reset-password    → password updated, bearer token issued → {access_token}
```

### Config keys (`lms_mobile_api.settings.yml`)

| Key | Default | Used |
|-----|---------|------|
| `otp_expiry_seconds` | 900 | Yes |
| `reset_token_expiry_seconds` | 1800 | Yes |
| `token_expiry_seconds` | 604800 (read at runtime) | Yes |
| `oauth_client_id` | `''` | **NOT USED** — leftover artifact |
| `oauth_client_secret` | `''` | **NOT USED** — leftover artifact |

The `oauth_*` keys are config artifacts from an earlier design (OAuth2 was planned, then replaced with the keyvalue approach). They have no code path.

---

## When to apply

This pattern is appropriate when:

1. You need **stateless bearer tokens** for a mobile app on a Drupal backend.
2. **OAuth2/JWT contrib** modules are not available or deemed over-engineered for the project phase.
3. TTL-based expiry via `keyvalue.expirable` is acceptable (keys are auto-purged by Drupal cron — `KeyValueDatabaseExpirableFactory`).
4. The server hosting environment **strips the `Authorization` header** before PHP — requiring a non-standard header name.
5. Email OTP is the desired verification mechanism (not SMS or TOTP).

---

## When NOT to apply

| Situation | Use instead |
|-----------|-------------|
| Production app with external clients or B2B integrations | `simple_oauth` (OAuth2 with PKCE) — industry standard, auditable |
| Refresh tokens needed (seamless re-auth after 7 days) | JWT with refresh token, or OAuth2 |
| Token introspection or revocation lists required at scale | OAuth2 token server |
| High-security context (financial, healthcare) | OAuth2 + PKCE; this pattern has no PKCS, no token binding, no revocation list |
| Multiple client apps with different scopes | OAuth2 scopes |

---

## Trade-offs

- **Cost:** Every authenticated API request performs one `keyvalue.expirable` lookup (a DB `SELECT` on `key_value_expire` table). This adds ~1 ms per request at typical scale. At high traffic, add a cache layer (Redis `PhpRedis` as keyvalue backend).
- **Risk (security):** Tokens are 32-char hex (128-bit entropy) — adequate against brute force. However:
  - No HTTPS enforcement in the module itself (nginx must enforce TLS).
  - No token binding (tokens are bearer — anyone with the string can use it).
  - No revocation list (a stolen token is valid until TTL expires; logout only works if the client discards the token locally — no server-side invalidation endpoint is defined).
  - `oauth_client_id`/`oauth_client_secret` keys in committed config YAML are dead code but increase confusion; they should be removed.
- **Operational impact:** Token TTL is configurable in `lms_mobile_api.settings`. Changing the TTL does not retroactively affect already-issued tokens (they keep their original expiry stored in the keyvalue table). Monitor `key_value_expire` table size; purge runs on Drupal cron.
- **Alternatives considered:** `simple_oauth` was evaluated. It was deferred because it requires: composer install of `simple_oauth` + dependencies, client entity creation, scope configuration, and a non-trivial Flutter OAuth2 PKCE client. The keyvalue approach was chosen for speed of delivery. The intent is to migrate to `simple_oauth` when the auth layer is hardened.

---

## Known defects (as-built)

| Defect | Location | Impact |
|--------|----------|--------|
| `getAuthenticatedUser()` triplicated | All 3 controllers | Any auth fix must be applied in 3 places |
| `lms_quiz` not in `lms_mobile_api.info.yml` dependencies | `lms_mobile_api.info.yml` | Module could be installed without `lms_quiz`; quiz endpoints would fail |
| `lms_wishlist` referenced in `getCourseDetail()` but not in dependencies | `MobileCourseController` | Same risk |
| No server-side logout/token-revocation endpoint | None exists | Stolen tokens valid until 7-day TTL |
| No CORS headers defined | Module and `sites/default/services.yml` | Flutter clients may hit CORS issues in browser-based testing |
| `oauth_client_id`/`oauth_client_secret` config keys | `lms_mobile_api.settings.yml` | Dead code; misleading |

---

## References

- D03 Story: [`D-Development/D03-stories/lms_mobile_api.md`](../../D-Development/D03-stories/lms_mobile_api.md)
- C05 Module spec: [`C-Design/C05-modules/lms_mobile_api.md`](../../C-Design/C05-modules/lms_mobile_api.md)
- Source files:
  - `web/modules/custom/lms_mobile_api/lms_mobile_api.services.yml`
  - `web/modules/custom/lms_mobile_api/lms_mobile_api.module` — `hook_mail`
  - `web/modules/custom/lms_mobile_api/src/Service/OtpService.php`
  - `web/modules/custom/lms_mobile_api/src/Controller/MobileAuthController.php`
  - `web/modules/custom/lms_mobile_api/src/Controller/MobileCourseController.php`
  - `web/modules/custom/lms_mobile_api/src/Controller/MobileLearningController.php`
  - `web/modules/custom/lms_mobile_api/config/install/lms_mobile_api.settings.yml`
- Flutter client: `app/` (v1.0.10+33) — must send `X-Authorization: Bearer <token>`
