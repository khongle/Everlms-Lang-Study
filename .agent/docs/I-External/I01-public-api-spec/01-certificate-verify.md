# Certificate Verify Endpoint

**Status: PROPOSED** — JSON endpoint chưa được implement. Hiện tại chỉ có HTML form tại `/certificate/verify`.

**Spec format:** REST / JSON
**Owner:** hainn (lms_certificate module)
**Stability:** Alpha (proposed)
**Lifecycle:** Not yet introduced

---

## Summary

Endpoint cho phép bất kỳ ai (unauthenticated) xác minh tính hợp lệ của một certificate được cấp bởi EverLMS.
Certificate code được in ở cuối mỗi certificate PDF (field `field_certificate_number`).

---

## Endpoint

```
GET /api/v1/certificate/verify/{code}
```

| Parameter | In | Type | Required | Description |
|-----------|----|------|----------|-------------|
| `code` | path | string | Yes | Certificate code, e.g. `A1B2C3D4E5` |

**Authentication:** None (public endpoint).

**Rate limit:** 60 requests/minute per IP (anonymous tier). See I06.

---

## Success Response — 200 OK

```json
{
  "valid": true,
  "revoked": false,
  "certificate": {
    "code": "A1B2C3D4E5",
    "issued_at": "2025-11-01T08:00:00Z",
    "course": {
      "id": 42,
      "title": "Advanced JavaScript"
    },
    "recipient": {
      "display_name": "Nguyen Van A"
    }
  }
}
```

## Revoked Certificate — 200 OK

```json
{
  "valid": false,
  "revoked": true,
  "certificate": {
    "code": "A1B2C3D4E5",
    "issued_at": "2025-11-01T08:00:00Z",
    "revoked_at": "2025-12-15T10:00:00Z",
    "course": {
      "id": 42,
      "title": "Advanced JavaScript"
    },
    "recipient": {
      "display_name": "Nguyen Van A"
    }
  }
}
```

## Not Found — 404

```json
{
  "valid": false,
  "revoked": false,
  "error": {
    "code": "CERT-001",
    "message": "No certificate found with this code."
  }
}
```

---

## Error Codes

| Code | HTTP | Meaning | Retry? |
|------|------|---------|--------|
| `CERT-001` | 404 | Certificate code not found | No |
| `CERT-400` | 400 | Invalid code format | No |
| `CERT-429` | 429 | Rate limited | Yes, with backoff |
| `CERT-500` | 500 | Internal error | Yes, once |

---

## Rate Limit Recommendation

- **Anonymous:** 60 requests/minute per IP.
- Response headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`.
- Exceed limit → HTTP 429 with `Retry-After` header.

---

## Breaking-Change Policy

- New optional fields in response → allowed without version bump.
- Removal of any field or change of type → requires `/api/v2/` + 90-day deprecation notice in I05.
