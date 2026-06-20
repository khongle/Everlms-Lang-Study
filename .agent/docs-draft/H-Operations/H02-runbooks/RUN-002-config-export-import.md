# RUN-002 — Config Export / Import

| Field | Value |
|-------|-------|
| **ID** | RUN-002 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV3 |

---

## Khi nào dùng

- **Export (cex):** Sau khi chỉnh config trong UI → đưa vào Git
- **Import (cim):** Deploy config từ Git lên môi trường mới / sau pull
- **Conflict resolution:** Khi `cim` báo config mismatch
- Sync config từ staging → production (khi production có)

---

## Prerequisites

- [ ] Git working tree sạch (không có uncommitted code changes)
- [ ] DDEV đang chạy: `ddev status` → active
- [ ] Biết rõ config nào đang thay đổi (tránh import nhầm)

---

## Part A — Config Export

### 1. Export config ra files

```bash
ddev drush cex --yes
# Files được ghi vào: web/sites/default/config/sync/
```

### 2. Kiểm tra diff

```bash
git diff web/sites/default/config/sync/
# Review kỹ các file .yml thay đổi
```

### 3. Commit vào Git

```bash
git add web/sites/default/config/sync/
git commit -m "config: export after [mô tả thay đổi]"
```

---

## Part B — Config Import

### 1. Kiểm tra trước khi import

```bash
# Xem diff giữa DB và files
ddev drush config:status
# Mong đợi: danh sách "Only in sync dir" hoặc "Different"
```

### 2. Dry run (xem sẽ thay đổi gì)

```bash
ddev drush config:import --preview
```

### 3. Thực hiện import

```bash
ddev drush cim --yes
```

### 4. Rebuild cache sau import

```bash
ddev drush cr
# Xem RUN-001 nếu cần chi tiết
```

---

## Part C — Conflict Resolution

### Tình huống: `cim` báo lỗi hoặc rollback

```bash
# Xem chi tiết conflict
ddev drush config:status --format=table

# Nếu muốn force override từ file (nguy hiểm — mất thay đổi trong DB):
ddev drush cim --yes

# Nếu muốn giữ DB và discard files (không khuyến nghị):
ddev drush cex --yes
git checkout web/sites/default/config/sync/
```

### Tình huống: UUID không khớp

```bash
# Xem UUID của site
ddev drush config:get system.site uuid

# Nếu môi trường mới (fresh install):
ddev drush config:set system.site uuid "UUID_TỪ_FILE_YML" --yes
ddev drush cim --yes
```

### Tình huống: Thiếu dependencies (module chưa enable)

```bash
# Enable module thiếu trước
ddev drush en {module_name} --yes
ddev drush cim --yes
```

---

## Verification

- [ ] `ddev drush config:status` → `No differences` hoặc danh sách rỗng
- [ ] `ddev drush status` → Drupal bootstrap: Successful
- [ ] Chức năng liên quan config đó hoạt động bình thường

---

## Rollback

Nếu import làm hỏng site:
```bash
# Restore config từ Git commit trước
git checkout HEAD~1 -- web/sites/default/config/sync/
ddev drush cim --yes
ddev drush cr
```

---

## Tham chiếu

- RUN-001: Cache rebuild sau import
- H01: Log incident nếu import gây SEV2+
- G04: Tạo change-request nếu đây là config thay đổi lớn
