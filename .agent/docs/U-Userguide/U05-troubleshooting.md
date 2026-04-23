# U05 — Troubleshooting

> Tra cứu lỗi phổ biến và cách fix. Nhóm theo khu vực.
> Cross-ref: `E-Quality/E02-issues/` (tracked issues), `E-Quality/E08-solutions/` (reusable patterns).

## Installation Issues

### DDEV không start được
**Symptom:** `ddev start` fail với error về port/docker.
**Cause:** Port 80/443/3306 đã bị bind; Docker daemon chưa chạy; WSL distro dead (Windows).
**Solution:**
1. `docker ps` — Docker chạy chưa?
2. `lsof -i :80` (Linux/Mac) / `netstat -ano | findstr :80` (Windows) — port ai chiếm?
3. `ddev config global --router-http-port=8080` để đổi port.
4. `ddev poweroff` → `ddev start` retry.

### `drush si` fail — DB connection
**Symptom:** `Drush command terminated abnormally.`
**Cause:** `settings.php` sai DB credential; DB service chưa ready.
**Solution:**
1. `ddev describe` — database URL đúng không.
2. `ddev exec mysql -e "SHOW DATABASES;"` — DB accessible?
3. Check `.ddev/settings.ddev.php` — credentials match.

### Composer install OOM
**Symptom:** `PHP Fatal error: Allowed memory size`.
**Solution:**
```bash
ddev exec php -d memory_limit=-1 /usr/local/bin/composer install
```

## Runtime Issues

### White screen / 500 sau change code
**Symptom:** Trang trắng hoặc HTTP 500.
**Solution:**
1. `ddev exec drush ws --severity=3` — xem error log.
2. `ddev exec tail -f /var/log/nginx/error.log` — nginx error.
3. `ddev exec drush cr` — clear cache (thường fix schema-level issue).

### Module enable fail với "dependency missing"
**Symptom:** Installer không enable module custom.
**Cause:** `{module}.info.yml` thiếu dependency hoặc version clash.
**Solution:**
1. Check `dependencies:` trong `.info.yml`.
2. Verify dep modules đã enabled: `drush pm:list --type=module --status=enabled`.
3. Enable từng cái theo thứ tự (lms_base trước).

### Lỗi schema: "Field storage does not exist"
**Symptom:** Exception khi save entity mới.
**Cause:** `drush updb` chưa chạy sau khi thêm field trong entity class.
**Solution:**
```bash
ddev exec drush updb -y && ddev exec drush cr
```

## User-facing (Learner)

### Video không phát
**Symptom:** Player loading hoài hoặc black.
**Causes & fixes:**
| Cause | Fix |
|-------|-----|
| Browser cũ | Upgrade Chrome/Firefox latest. Safari < 17 có bug HLS. |
| Ad-blocker chặn | Disable ad-blocker cho domain. |
| Video file missing | Contact admin — file có thể bị xóa khỏi S3/local. |
| Network slow | Test Speed > 3 Mbps. Chuyển quality thấp (player setting). |
| VTT subtitle broken | Disable subtitle trong player. |

### SCORM không load
**Symptom:** iframe trắng hoặc "Failed to initialize".
**Causes & fixes:**
1. Browser chặn third-party cookie → bật cho domain.
2. SCORM package corrupt → admin re-upload.
3. HTTPS mixed content — kiểm tra package references HTTP assets.
4. Check console (F12) cho error chi tiết.

### Progress không cập nhật
**Symptom:** Hoàn thành lesson mà % vẫn vậy.
**Cause:** Race condition (RISK-002); cache stale; AJAX fail silent.
**Solution:**
1. Hard refresh (Ctrl+F5).
2. Log out + log in lại.
3. Nếu kéo dài > 10 min: check `/my-learning` và open dev tool → Network tab → tìm lỗi POST `/progress/*`.
4. Report support với user ID, course ID, lesson ID, timestamp.

### Quiz submit rồi mà không có kết quả
**Symptom:** Redirect về loading, timeout.
**Solution:**
1. Mở dev tool → check `POST /quiz/{id}/submit` có 500 không.
2. Check lesson có tính duplicate submit — admin xem `QuizResult` table cho user.
3. Retry sau 1 min; nếu retry limit hit, contact admin reset.

### Essay chưa được chấm (pending) > 72h
**Solution:** Contact instructor (email hiển thị ở course detail). Escalate admin nếu instructor không responsive.

## Payment Issues

### Stripe — card declined
**Symptom:** Error "Your card was declined".
**Causes:** Invalid CVC, expired card, không đủ tiền, fraud flag từ issuer, country restriction.
**Solution:** Thử card khác; nếu Stripe Radar block — contact support cho whitelist.

### Stripe — webhook không kích hoạt enrollment
**Symptom:** Payment success nhưng không enroll.
**Cause:** ⚠️ **FIND-001** — webhook handler là stub. **Production chưa ready.**
**Solution:** Manual enroll qua admin (`/admin/lms/orders/{id}` → **Activate Enrollment**).

### PayPal — không redirect về
**Symptom:** Approve xong, trang trắng.
**Cause:** ⚠️ **FIND-002** — PayPal chưa implement. Tạm thời không dùng.

### Refund request
**Process:** Không self-service. Gửi email support với order ID + lý do. Admin sử dụng runbook `H-Operations/H02-runbooks/RUN-order-refund.md`.

## Certificate Issues

### PDF render sai font / layout
**Cause:** Font chưa upload vào dompdf font cache; template có char không support.
**Solution:**
1. Admin: `/admin/config/lms/certificate/templates/{id}` → **Regenerate font cache**.
2. Re-issue certificate sau fix.

### Verify link hiển thị "Not found"
**Cause:** Cert bị revoke; code typo; URL bị encode sai.
**Solution:**
1. Check đúng code (case-sensitive).
2. Search `/admin/lms/certificates` theo code → xem status.
3. Nếu revoked → page đã cho biết reason.

## Forum Issues

### Post không hiện ngay
**Cause:** Moderation pending (nếu policy require-approve).
**Solution:** Check **Pending** flag ở `/user/{uid}/forum-activity`.

### Mention không gửi notification
**Cause:** Username typo; user disable mention notify.
**Solution:** Dùng autocomplete (`@ ` rồi đợi dropdown). Nếu user không nhận — kiểm tra `/user/{uid}/edit/notifications`.

## AI Course Builder Issues

### Generate timeout
**Cause:** Provider slow (OpenAI busy hour) hoặc prompt quá dài.
**Solution:**
1. Retry sau 1 min.
2. Ngắn brief lại.
3. Đổi provider (Gemini thường nhanh hơn OpenAI).

### "Quota exceeded"
**Cause:** Daily limit 10 generations/user.
**Solution:** Wait 24h hoặc contact admin raise quota.

### Output rỗng / tiếng sai
**Cause:** Model chọn không phù hợp với ngôn ngữ.
**Solution:** Admin đổi default model (`gpt-4o` cho VN; `gemini-1.5-flash` cho EN).

## Error Code Reference (HTTP + App-level)

| Code | Meaning | Typical fix |
|------|---------|-------------|
| 401 | Not authenticated | Login lại |
| 403 | Forbidden | Role không đủ — contact admin |
| 404 | Not found | URL sai; content có thể bị xóa |
| 422 | Validation failed | Check input — error detail trong body |
| 429 | Rate limit | Đợi, không spam request |
| 500 | Server error | Report support + check `/admin/reports/dblog` |
| 503 | Maintenance | System đang maintenance — check `/maintenance` |

## Diagnostic Commands (Dev/Admin)

| Command | Purpose | Expected |
|---------|---------|----------|
| `ddev exec drush status` | Check Drupal runtime | Bootstrap "Successful", DB "Connected" |
| `ddev exec drush core:requirements --severity=2` | Warn/error list | Không có "Error" |
| `ddev exec drush ws --severity=3 --count=50` | Recent errors | Ideally empty |
| `ddev exec drush cache:rebuild` | Full CR | "Cache rebuild complete" |
| `ddev exec drush updb --simulate` | Preview DB updates | Không pending updates |
| `ddev exec drush sqlq "SELECT COUNT(*) FROM users_field_data"` | User count sanity | Expected number |

## Escalation

Nếu các bước trên không giải quyết:

1. **Collect diagnostic info**:
   - Screenshot error + URL.
   - Browser + OS version.
   - Steps to reproduce.
   - User ID (nếu logged in).
   - Timestamp + timezone.

2. **Contact channel**:
   - In-app **Help** button (user).
   - Email support@everlms.tld (admin config).
   - Admin internal: `#everlms-ops` Slack / GitHub issue.

3. **For production incidents**:
   - SEV1-SEV4 per `H-Operations/` / `A05-incident-response.md`.
   - Runbook index: `H-Operations/H02-runbooks/`.

---

*End of U05-troubleshooting.md*
