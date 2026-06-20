# RUN-001 — Drupal Cache Rebuild

| Field | Value |
|-------|-------|
| **ID** | RUN-001 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV3 / SEV4 |

---

## Khi nào dùng

- Site trả về nội dung cũ sau khi config/code thay đổi
- Sau `drush cim` hoặc `drush updb`
- Template/theme thay đổi không có hiệu lực
- Route mới không nhận được
- Block / View không cập nhật
- Sau khi chỉnh sửa `.module` hoặc service definition
- Nghi ngờ stale cache gây lỗi khó giải thích

---

## Prerequisites

- [ ] DDEV đang chạy: `ddev status` → active
- [ ] Có quyền chạy drush trong container

---

## Steps

### 1. Rebuild toàn bộ cache (lệnh chuẩn)

```bash
# Trong DDEV local
ddev drush cr

# Hoặc vào container rồi chạy
ddev ssh
drush cr
```

### 2. Xác nhận không có lỗi

```bash
ddev drush status
# Mong đợi: Drupal bootstrap: Successful
```

### 3. Nếu `drush cr` thất bại — rebuild thủ công theo bước

```bash
# Xóa cache từ DB
ddev drush php-eval "drupal_flush_all_caches();"

# Nếu vẫn fail — xóa cache directory thủ công
ddev exec rm -rf /tmp/cache /var/www/html/web/sites/default/files/php/twig
ddev drush cr
```

### 4. Nếu Twig cache bị stale

```bash
# Twig cache thường ở files/php/twig
ddev exec find /var/www/html/web/sites/default/files/php -name '*.php' -delete
ddev drush cr
```

### 5. Xóa Redis cache (nếu đang dùng Redis)

```bash
# PROPOSED — Redis chưa triển khai
# ddev exec redis-cli FLUSHALL
# ddev drush cr
```

---

## Verification

- [ ] `ddev drush status` → `Drupal bootstrap: Successful`
- [ ] Truy cập trang bất kỳ → không có lỗi 500
- [ ] Config/template thay đổi đã có hiệu lực
- [ ] Không có warning trong Recent Log (`ddev drush watchdog:show --count=20`)

---

## Rollback / Escape

Cache rebuild không thể rollback — nó chỉ xóa cache, không thay đổi data.
Nếu sau rebuild có lỗi mới xuất hiện:
1. Xem log: `ddev drush watchdog:show --count=50 --severity=error`
2. Nguyên nhân thường là code lỗi bị cache che giấu → mở E02 issue

---

## Tham chiếu

- H01: Tạo incident nếu rebuild không giải quyết được vấn đề
- E02: Mở issue nếu phát hiện bug thực sự sau rebuild
