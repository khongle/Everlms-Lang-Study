# EverLMS — Development Guide

## 1. Local Development Setup

### 1.1 Prerequisites
- Docker Desktop
- DDEV (latest version)
- Composer 2.x
- Node.js 20 LTS
- Git

### 1.2 Setup Steps
```bash
# 1. Clone repository
git clone <repo-url> everlms && cd everlms

# 2. Start DDEV
ddev start

# 3. Install Drupal dependencies
ddev composer install

# 4. Install Drupal
ddev drush site:install --account-name=admin --account-pass=admin -y

# 5. Install theme dependencies & build
ddev exec bash -c "cd web/themes/custom/lms_theme && npm install && npm run build"

# 6. Enable custom modules
ddev drush en lms_base lms_course lms_chapter lms_lesson -y
ddev drush cr
```

---

## 2. Coding Standards

### 2.1 PHP 8.3+ Rules
- `declare(strict_types=1);` mọi file PHP
- Sử dụng: enums, union types, constructor property promotion
- **CẨN THẬN** `readonly`: KHÔNG dùng cho properties trong FormBase (serialize issue)
- PSR-4 autoloading nghiêm ngặt

### 2.2 Drupal 11 Rules
- **Attributes** thay cho Annotations (Plugin, Controller, etc.)
- **DI ưu tiên** `__construct` injection. `\Drupal::service()` chỉ trong `.module`
- **Form API** cho tất cả forms
- **Render Arrays** + Twig cho output — **KHÔNG** `#markup`
- **Url::fromRoute()** — **KHÔNG** URL cứng
- **Controller chỉ prep data** → Twig xử lý HTML/CSS

### 2.3 Theme Rules
- **SCSS** cho tất cả styles (trong `scss/` directory)
- **Twig** xử lý toàn bộ HTML structure
- **jQuery + Drupal.behaviors + core/once** cho JS
- **KHÔNG** HTML/CSS trong JS — chỉ UX enhancement
- Build SCSS **trong DDEV**: `ddev exec bash -c "cd web/themes/custom/lms_theme && npm run build"`

### 2.4 Quality Checks
```bash
# PHPCS
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/

# PHPStan
ddev exec vendor/bin/phpstan analyze web/modules/custom/

# SCSS Build
ddev exec bash -c "cd web/themes/custom/lms_theme && npm run build"
```

---

## 3. Module Development Workflow

### 3.1 Tạo Module Mới
```
web/modules/custom/{module_name}/
├── {module_name}.info.yml
├── {module_name}.module
├── {module_name}.services.yml
├── {module_name}.routing.yml
├── {module_name}.permissions.yml
├── {module_name}.links.menu.yml
├── src/
│   ├── Entity/
│   ├── Form/
│   ├── Controller/
│   ├── Service/
│   └── Plugin/
└── templates/
```

### 3.2 Entity Development Pattern
1. Define entity class (`src/Entity/`)
2. Define entity type (`src/Entity/Type/`)
3. Create `.install` with schema/update hooks
4. Register services in `.services.yml`
5. Create routes in `.routing.yml`
6. Create controller (data only) → Twig template
7. Register theme hooks in `.module`

---

## 4. Git Workflow

```
main ← production
├── develop ← integration
│   ├── feature/lms-xxx-description
│   ├── bugfix/lms-xxx-description
│   └── hotfix/lms-xxx-description
```

### Branch Naming
| Type | Format | Ví dụ |
|------|--------|-------|
| Feature | `feature/lms-{id}-{desc}` | `feature/lms-001-course-entity` |
| Bugfix | `bugfix/lms-{id}-{desc}` | `bugfix/lms-042-quiz-timer` |
| Hotfix | `hotfix/lms-{id}-{desc}` | `hotfix/lms-099-payment-webhook` |

### Commit Message
```
[LMS-XXX] Type: Brief description

Types: feat, fix, refactor, docs, test, chore
```

---

## 5. Database Backup

```bash
# Backup
ddev drush sql:dump --gzip > databases/backup-$(date +%Y%m%d).sql.gz

# Restore
ddev drush sql:cli < databases/backup-20260308.sql
```
