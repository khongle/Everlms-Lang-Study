# I04 — Developer Portal

**Status: PROPOSED** — Developer portal chưa được build tính đến 2026-04-16.

**Audience:** External developers muốn tích hợp với EverLMS (verify certificates, embed course info, receive fulfillment events).
**Intended URL:** `https://developers.everlms.vn` (chưa có domain, chưa có hosting).

---

## Current State

EverLMS không có developer portal. Tất cả API documentation hiện nằm trong internal `.agent/docs/` folder (không public).

---

## Intended Content When Built

### Getting Started
1. Tạo tài khoản và generate API key.
2. Xác thực bằng `Authorization: Bearer {key}` header.
3. Gọi endpoint đầu tiên:
   ```bash
   curl https://api.everlms.vn/api/v1/certificate/verify/A1B2C3D4E5
   ```

### Authentication
- API key via Bearer token (proposed).
- OAuth 2.0 cho user-consented access (long-term, not yet planned).

### Key Sections
- **API Reference:** Certificate verify, course catalog, enrollment (từ I01).
- **Webhook Guide:** Nhận events từ EverLMS khi implemented (từ I03).
- **SDK Downloads:** JavaScript, Python (từ I02).
- **Sandbox:** Test environment với data isolation (không yet planned).

---

## Links to Existing Docs (Internal)

| Section | Location |
|---------|----------|
| Public API Spec | `I01-public-api-spec/` |
| SDK Plans | `I02-sdk-docs/` |
| Webhook Contracts | `I03-webhook-contracts/` |
| Rate Limits | `I06-rate-limits-quotas.md` |
| API Changelog | `I05-api-changelog.md` |

---

## Prerequisites Before Building Portal

1. Public API v1 shipped and stable (I01).
2. Authentication mechanism implemented.
3. Domain và hosting provisioned.
4. Static site generator hoặc hosted docs platform chọn (e.g., Mintlify, Docusaurus, ReadTheDocs).

---

*Portal sẽ là public mirror của I01–I06 docs, với interactive API playground.*
