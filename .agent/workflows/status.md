---
description: Hiển thị trạng thái dự án Drupal — DDEV status, modules, theme, database info.
---

# /status - Trạng thái Dự án

$ARGUMENTS

---

## Mục đích

Hiển thị tổng quan trạng thái dự án Drupal hiện tại: DDEV, modules, theme, database.

---

## Quy trình

Khi `/status` được gọi, thu thập và hiển thị thông tin sau:

// turbo-all

### 1. DDEV Status

```bash
ddev describe
```

### 2. Drupal Core Info

```bash
ddev drush status --fields=drupal-version,php-version,db-driver,db-name
```

### 3. Custom Modules

```bash
ddev drush pm:list --type=module --status=enabled --filter='custom'
```

### 4. Theme Info

```bash
ddev drush theme:list --status=enabled
```

### 5. Recent Watchdog (nếu cần)

```bash
ddev drush watchdog:show --count=5
```

---

## Output Format

```markdown
## 📊 Project Status — [Project Name]

### 🐳 DDEV
- **URL:** https://[project].ddev.site
- **PHP:** [version]
- **Database:** MariaDB [version]
- **Status:** Running / Stopped

### 🔧 Drupal
- **Version:** [drupal-version]
- **PHP:** [php-version]
- **Database:** [db-driver] — [db-name]

### 📦 Custom Modules (Enabled)
- `linne_core` — Core utilities
- `linne_product` — Product management
- [...]

### 🎨 Theme
- **Default:** [theme_name]
- **Admin:** [admin_theme]

### ⚠️ Recent Logs (nếu có lỗi)
- [timestamp] [severity] [message]
```

---

## Ví dụ sử dụng

```
/status
/status modules
/status logs
```
