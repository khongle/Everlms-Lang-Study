---
description: Bootstrap dự án Drupal — DDEV + Drupal + Docker + PHPCS + PHPStan. Hỗ trợ cài selective hoặc full stack.
---

# /bootstrap - Khởi tạo & Cấu hình Dự án Drupal

$ARGUMENTS

// turbo-all

---

## Cú pháp

```
/bootstrap                              → Full stack: DDEV + Drupal 11 + Docker + phpMyAdmin + PHPCS + PHPStan
/bootstrap d11 ddev                     → Chỉ DDEV + Drupal 11 (không quality tools, không phpMyAdmin)
/bootstrap d10 ddev                     → Chỉ DDEV + Drupal 10
/bootstrap phpcs phpstan                → Chỉ cài quality tools (site đã chạy)
/bootstrap phpmyadmin                   → Chỉ cài DDEV phpMyAdmin plugin (site đã chạy)
/bootstrap ddev d11 phpcs phpstan       → Full stack (tương đương /bootstrap)
```

---

## Phân tích tham số

| Tham số | Ý nghĩa |
|---|---|
| `d11` (default) | Drupal 11 |
| `d10` | Drupal 10 |
| `ddev` | Setup DDEV + Docker + Drupal |
| `phpmyadmin` | Cài DDEV phpMyAdmin plugin |
| `phpcs` | Cài PHP CodeSniffer + Drupal coding standards |
| `phpstan` | Cài PHPStan + phpstan-drupal |
| *(không tham số)* | Full stack = `ddev` + `d11` + `phpmyadmin` + `phpcs` + `phpstan` |

---

## Quy trình

### Bước 0: Phân tích request

```
Nếu KHÔNG có tham số → flags = {ddev, d11, phpmyadmin, phpcs, phpstan, gitignore}
Nếu CÓ tham số → parse từng tham số thành flags
```

---

### Bước 1: Setup DDEV + Drupal (khi flag `ddev` ON)

#### 1a. Kiểm tra prerequisites

```bash
# Kiểm tra DDEV đã cài chưa
ddev version
```

> ⛔ **Nếu `ddev` không tìm thấy (command not found):** DỪNG NGAY. Thông báo user:
> *"DDEV chưa được cài đặt. Vui lòng cài đặt DDEV theo hướng dẫn tại https://ddev.readthedocs.io/en/stable/users/install/ rồi chạy lại `/bootstrap`."*
> **KHÔNG được tự động cài DDEV.**

```bash
# Kiểm tra Docker đang chạy
docker info
```

> ⛔ **Nếu Docker chưa cài hoặc chưa chạy:** DỪNG NGAY. Thông báo user:
> *"Docker chưa được cài đặt hoặc chưa chạy. Vui lòng cài Docker Desktop tại https://www.docker.com/products/docker-desktop/ và khởi động nó, rồi chạy lại `/bootstrap`."*
> **KHÔNG được tự động cài hoặc khởi động Docker.**

#### 1b. Cấu hình DDEV

**Xác định project root:**
- Nếu user không chỉ định folder → dùng thư mục hiện tại
- Nếu user muốn tạo mới → `mkdir [name] && cd [name]`

**Kiểm tra `composer.json` để phân loại:**

| Tình huống | Lệnh DDEV config |
|---|---|
| **Existing project** có `web/` | `ddev config --project-type=drupal11 --docroot=web --create-docroot=false --php-version=8.3 --webserver-type=apache-fpm` |
| **Existing project** có `docroot/` | `ddev config --project-type=drupal11 --docroot=docroot --create-docroot=false --php-version=8.3 --webserver-type=apache-fpm` |
| **No docroot found** | ⛔ STOP — hỏi user tên thư mục docroot |
| **New project** (không có `composer.json`) | `ddev config --project-type=drupal11 --docroot=web --create-docroot --php-version=8.3 --webserver-type=apache-fpm` |

> Drupal 10: thay `--project-type=drupal11` bằng `--project-type=drupal` và `--php-version=8.2`.

#### 1c. phpMyAdmin (khi flag `phpmyadmin` ON)

```bash
ddev get ddev/ddev-phpmyadmin
```

> Lệnh này phải chạy **trước** `ddev start` hoặc cần `ddev restart` sau khi cài.

#### 1d. Start DDEV

```bash
ddev start
```

#### 1e. Install Dependencies

| Tình huống | Lệnh |
|---|---|
| **Existing project** (có `composer.json`) | `ddev composer install` |
| **New project** (không có `composer.json`) | Xem quy trình bên dưới |

**Quy trình tạo project mới:**

1. Tạo Drupal project vào một thư mục tạm (`tmp_drupal`):
```bash
ddev composer create-project drupal/recommended-project tmp_drupal --no-interaction
```

2. Di chuyển các file từ thư mục tạm ra root (bao gồm cả file ẩn):
```bash
rsync -av tmp_drupal/ ./
```

3. Xoá thư mục tạm:
```bash
rm -rf tmp_drupal
```

#### 1f. Install Drush

```bash
ddev composer require drush/drush
```

#### 1g. Cài Contrib Modules mặc định (CHỈ cho new project)

Nếu đây là **new project** (không có `composer.json` ban đầu), cài thêm các contrib modules mặc định:

```bash
ddev composer require drupal/admin_toolbar drupal/pathauto
ddev drush en admin_toolbar admin_toolbar_tools pathauto -y
```

#### 1h. Database Setup

| Tình huống | Lệnh |
|---|---|
| **Fresh install** (DB rỗng) | `ddev drush site:install --account-name=admin --account-pass=admin -y` |
| **Import database** (user cung cấp file .sql/.sql.gz) | `ddev import-db --src /path/to/database.sql.gz` → `ddev drush cr` → `ddev drush uli` |

> **Lưu ý:** Với fresh install, chạy `ddev drush en admin_toolbar admin_toolbar_tools pathauto -y` **sau** `site:install`.

---

### Bước 2: Cài Quality Tools (khi flag `phpcs` hoặc `phpstan` ON)

#### 2a. Install Dependencies

```bash
ddev composer require --dev drupal/core-dev phpstan/phpstan mglaman/phpstan-drupal squizlabs/php_codesniffer --with-all-dependencies
```

#### 2b. Tạo `phpcs.xml` (project root)

```xml
<?xml version="1.0"?>
<ruleset name="Drupal Setup">
  <description>Drupal coding standards.</description>
  <file>web/modules/custom</file>
  <file>web/themes/custom</file>
  <arg name="extensions" value="php,module,inc,install,test,profile,theme,css,info,txt,md,yml"/>
  <config name="installed_paths" value="../../drupal/coder/coder_sniffer/Drupal,../../drupal/coder/coder_sniffer/DrupalPractice"/>
  <rule ref="Drupal"/>
  <rule ref="DrupalPractice"/>
</ruleset>
```

#### 2c. Tạo `phpstan.neon` (project root)

```yaml
parameters:
  level: 5
  paths:
    - web/modules/custom
    - web/themes/custom
  drupal:
    drupal_root: web
```

#### 2d. Thêm Composer Scripts vào `composer.json`

```json
"scripts": {
    "phpcs": "phpcs",
    "phpcbf": "phpcbf",
    "phpstan": "phpstan analyze"
}
```

#### 2e. Verify

```bash
ddev composer phpcs -- --version
ddev composer phpstan -- --version
```

---

### Bước 3: Tạo Technical Stack Doc (LUÔN LUÔN)

Sau khi bootstrap xong, **BẮT BUỘC** tạo file `.agent/docs/technical-stack.md` ghi lại:

```markdown
---
title: Technical Stack
description: Mô tả stack kỹ thuật và môi trường phát triển.
updated: [YYYY-MM-DD]
---

# Technical Stack

## Môi trường Development
- **Container:** DDEV [version] + Docker
- **CMS:** Drupal [version]
- **PHP:** [version]
- **Database:** MariaDB [version] (via DDEV)
- **URL:** https://[project].ddev.site

## Quality Tools
- **PHPCS:** Drupal + DrupalPractice standards
- **PHPStan:** Level 5 (phpstan-drupal extension)
- **Chạy:** `ddev composer phpcs` / `ddev composer phpstan`

## Môi trường Production
> ⚠️ Production KHÔNG sử dụng DDEV.
> Chi tiết deploy sẽ được cập nhật riêng.

## Ghi chú
- [Các ghi chú khác về stack, modules đặc biệt, etc.]
```

> **LƯU Ý:** Cập nhật versions thực tế từ output của `ddev describe` và `ddev drush status`.

---

### Bước 4: Tạo .gitignore (khi flag `gitignore` ON)

Nếu user chạy `/bootstrap` không có tham số (full stack), ngay sau khi hoàn tất các bước trên, hãy tự động sinh file `.gitignore` ở project root áp dụng nội dung chuẩn của Drupal và Agent (từ workflow `/git-ignore`).

---

## Ví dụ sử dụng

```
/bootstrap                    → Setup mọi thứ cho dev mới (full stack)
/bootstrap ddev d11           → Chỉ tạo DDEV + Drupal 11
/bootstrap phpcs phpstan      → Chỉ thêm quality tools vào site đang chạy
/bootstrap phpmyadmin         → Chỉ cài phpMyAdmin plugin cho DDEV đang chạy
/bootstrap d10 ddev phpcs     → Drupal 10 + DDEV + chỉ PHPCS (không PHPStan)
```
