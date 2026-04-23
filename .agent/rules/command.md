---
trigger: always_on
description: Quy tắc chạy command line — tách lệnh, log output, command safety. Hỗ trợ đa nền tảng (macOS, Linux, Windows).
---

## 1. KHÔNG CHẠY GỘP QUÁ NHIỀU LỆNH

- **Hạn chế** `&&` hoặc `;` nối nhiều lệnh, đặc biệt lệnh dài hoặc rủi ro cao.
- **TÁCH BIỆT** các tác vụ khác nhau (install, configure, test) thành các bước riêng.
- Ví dụ sai: `composer install && drush en module && drush cr && npm install && npm run build`
- Ví dụ đúng: Tách thành các bước riêng biệt, chờ kết quả từng bước.

## 2. LOG OUTPUT THAY VÌ CHỜ TRỰC TIẾP

> **⚠️ TUYỆT ĐỐI KHÔNG dùng `command_status` để chờ output từ command (đặc biệt lệnh dài hoặc DDEV).**

**BẮT BUỘC** redirect output vào file log rồi đọc trực tiếp bằng `view_file`:

```bash
# Pattern bắt buộc (cross-platform):
ddev drush pm:list --filter=affhub > /tmp/cmd_output.log 2>&1
# Sau đó đọc:
view_file /tmp/cmd_output.log
```

### Xác định temp path theo OS:
| OS | Temp path |
|---|---|
| macOS / Linux | `/tmp/cmd_output.log` |
| Windows (PowerShell) | `$env:TEMP\cmd_output.log` |
| Windows (CMD) | `%TEMP%\cmd_output.log` |

> **Quy ước:** Luôn dùng `/tmp/cmd_output.log` làm mặc định. Nếu phát hiện OS là Windows, tự động chuyển sang `$env:TEMP\cmd_output.log` (PowerShell) hoặc `%TEMP%\cmd_output.log` (CMD).

### Quy tắc chi tiết:
- Redirect output bằng `> <temp_path> 2>&1` cho MỌI lệnh cần kiểm tra kết quả.
- Đặt `WaitMsBeforeAsync` đủ lớn (5000–10000ms) để lệnh kịp hoàn thành.
- Sau khi lệnh chạy xong, dùng `view_file` đọc file log thay vì `command_status`.
- Với lệnh chạy dài (composer, drush updb...), tăng wait hoặc dùng `command_status` với `WaitDurationSeconds` lớn + đọc log file sau.

### Ví dụ đúng:
```bash
# Bước 1: Chạy lệnh, log output
ddev drush en affhub_video -y > /tmp/cmd_output.log 2>&1

# Bước 2: Đọc kết quả bằng view_file
# → view_file /tmp/cmd_output.log
```

### Ví dụ sai:
```bash
# ❌ Chạy lệnh rồi dùng command_status chờ
ddev drush en affhub_video -y
# → command_status (chờ mãi không có output)
```

## 3. TẠO SCRIPT KHI LỆNH PHỨC TẠP

- Khi `run_command` fail quá **2 lần** liên tiếp → tạo script file.
- Khi lệnh quá dài hoặc chứa nhiều logic điều kiện.
- Lưu tại `.agent/docs/scripts/`.

### Cross-platform script:
| OS | File type | Cấp quyền |
|---|---|---|
| macOS / Linux | `.sh` (Bash) | `chmod +x script.sh` |
| Windows | `.ps1` (PowerShell) hoặc `.bat` (CMD) | Không cần chmod |

> **Quy ước:** Mặc định tạo `.sh`. Nếu OS là Windows, tạo `.ps1` thay thế.
