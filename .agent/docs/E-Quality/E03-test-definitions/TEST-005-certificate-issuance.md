# TEST-005 — Certificate Issuance

| Field | Value |
|-------|-------|
| ID | TEST-005 |
| Type | Integration |
| Priority | P0 — Must |
| Feature | Certificate / F-CERT |
| Units | U-035 (UserCertificate entity), U-036 (Certificate issuance service), U-037 (PDF generation), U-038 (Public verification) |
| Traceability | E2E-005, E2E-010 (E01), KT-09 (test-plan.md) |
| Owner | Unassigned |

## Scope

Verify auto-issuance trigger, uniqueness constraint, PDF generation, và public verification link. Một số TC sẽ fail cho đến khi U-036–U-038 được implement.

## Preconditions

- Course có `certificate_enabled = true`, `passing_threshold` configured (ví dụ: 80%)
- User đã complete course (`user_course.status = completed`, `progress_percentage = 100%`)
- `CertificateTemplate` tồn tại và published

## Test Cases

### TC-005-A: Auto-issuance on course complete

**Given:** User complete course (progress = 100%)  
**When:** `CertificateIssuerService::issueCertificate(uid, course_id)` được gọi  
**Then:**
- `user_certificate` record tạo ra với unique `verify_code`
- `issued_at` = current timestamp
- `status = active`

---

### TC-005-B: No duplicate issuance

**Given:** `user_certificate` đã tồn tại cho user + course  
**When:** `issueCertificate()` được gọi lần 2  
**Then:**
- Không tạo record mới
- Trả về existing certificate (idempotent)

---

### TC-005-C: Eligibility check — course not completed

**Given:** User có `user_course.progress_percentage = 90%` (chưa 100%)  
**When:** `issueCertificate()` được gọi  
**Then:**
- Throws `CertificateNotEligibleException` hoặc trả về `null`
- Không tạo `user_certificate` record

---

### TC-005-D: PDF generation

**Given:** `user_certificate` active tồn tại  
**When:** User truy cập `/certificate/{verify_code}/download`  
**Then:**
- Response Content-Type = `application/pdf`
- PDF chứa student name, course name, issued date
- Thời gian generate < 5s (NFR)

---

### TC-005-E: Public verification endpoint

**Given:** `verify_code` valid  
**When:** GET `/certificate/verify/{verify_code}` (public, no auth)  
**Then:**
- HTTP 200, trả về certificate info (student name, course, date)
- `verify_code` không valid → HTTP 404

| verify_code | Expected |
|-------------|---------|
| Valid, active | 200 + info |
| Valid, revoked | 200 + "Certificate revoked" |
| Not exist | 404 |
| Tampered | 404 |

## Công cụ

- PHPUnit KernelTestBase (entity + service layer)
- PDF test: assert Content-Type + file size > 0
- Verification endpoint: Functional (BrowserTestBase) hoặc E2E Playwright
