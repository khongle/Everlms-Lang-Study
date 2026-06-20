# RUN-005 — AI Provider Key Rotation

| Field | Value |
|-------|-------|
| **ID** | RUN-005 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV2 (nếu key bị lộ) / SEV3 (rotation định kỳ) |

---

## Khi nào dùng

- API key bị lộ (commit nhầm vào Git, log leak)
- Rotation định kỳ theo policy bảo mật (ví dụ: 90 ngày)
- Nhận thông báo key sắp hết hạn từ provider
- Chuyển sang provider khác (OpenAI → Gemini hoặc ngược lại)
- Sau khi nhân viên/collaborator có key rời dự án

---

## Providers được hỗ trợ

| Provider | Plugin class | Setting path |
|----------|-------------|-------------|
| OpenAI | `OpenAiProvider` | `lms_ai.settings` → `openai_api_key` |
| Google Gemini | `GeminiProvider` | `lms_ai.settings` → `gemini_api_key` |
| Pollinations | `PollinationsProvider` | Không cần key (free API) |

> Module: `web/modules/custom/lms_ai/`
> Settings form: `/manage/settings/ai` (UI)

---

## Prerequisites

- [ ] Có key mới đã tạo sẵn trên provider dashboard
- [ ] KHÔNG xóa key cũ ngay — chỉ xóa sau khi xác nhận key mới hoạt động
- [ ] Nếu key bị lộ: revoke key cũ **ngay lập tức** trước khi làm bước nào

---

## Part A — Key Bị Lộ (Emergency)

```
1. Revoke key cũ ngay trên provider dashboard:
   - OpenAI: https://platform.openai.com/api-keys → Revoke
   - Gemini: https://aistudio.google.com/app/apikey → Delete

2. Tạo key mới ngay

3. Thực hiện Part B (rotation bình thường)

4. Audit: tìm trong Git history xem key có bị commit không:
   git log -p --all | grep -i "sk-" | head -20
   (nếu có → rewrite history + notify về RISK-001)

5. Log SEV2 incident trong H01
```

---

## Part B — Rotation Bình Thường

### 1. Tạo key mới trên provider dashboard

- OpenAI: https://platform.openai.com/api-keys → Create new secret key
- Gemini: https://aistudio.google.com/app/apikey → Create API key

### 2. Cập nhật qua Admin UI

```
Truy cập: /manage/settings/ai
→ Chọn provider (OpenAI / Gemini)
→ Dán key mới vào field tương ứng
→ Save
```

### 3. Cập nhật qua Drush (alternative)

```bash
# OpenAI
ddev drush config:set lms_ai.settings openai_api_key "sk-new-key-here" --yes

# Gemini
ddev drush config:set lms_ai.settings gemini_api_key "AIza-new-key-here" --yes

# Rebuild cache
ddev drush cr
```

### 4. Cập nhật .env / secrets (nếu dùng environment variable)

```bash
# Nếu key được inject qua environment
# Chỉnh trong .ddev/.env hoặc server environment
OPENAI_API_KEY=sk-new-key-here

# Restart service để nhận env mới
ddev restart
```

---

## Part C — Verification

### Kiểm tra provider hoạt động

```bash
# Test qua Drush (gọi service trực tiếp)
ddev drush php-eval "
  \$manager = \Drupal::service('plugin.manager.lms_ai_provider');
  \$provider = \$manager->createInstance('openai');
  \$result = \$provider->generateText('Test: Say hello in one word');
  echo \$result;
"
```

### Kiểm tra qua UI

```
/manage/ai/preview
→ Chọn provider
→ Nhập prompt test
→ Xác nhận response nhận được
```

### Xác nhận không còn lỗi

```bash
ddev drush watchdog:show --type=lms_ai --count=20
# Không nên có: "Invalid API key" hoặc "401 Unauthorized"
```

---

## Part D — Sau Rotation

- [ ] Revoke key cũ trên provider dashboard (sau khi xác nhận key mới hoạt động)
- [ ] Cập nhật password manager nếu đang dùng
- [ ] Nếu key bị lộ: ghi nhận trong H01 + review RISK-001 trong A10
- [ ] Không commit key vào `.env` file trong Git

---

## Tham chiếu

- lms_ai module: `web/modules/custom/lms_ai/src/Form/AiSettingsForm.php`
- H01: Log incident nếu key rotation do security breach
- H04: Tech debt TD-xxx về key management strategy
