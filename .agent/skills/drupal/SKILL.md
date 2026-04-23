---
name: drupal
description: Comprehensive skill set for Drupal development (Drupal 10 & 11).
---

# Drupal Development Skill

**Entry Point**: Select version based on project `composer.json`.

## 1. Version Selection

| Version | Path | Key Features |
| :--- | :--- | :--- |
| **Drupal 11+** | `v11/` | PHP 8.3+, Attributes, New APIs |
| **Drupal 10** | `v10/` | PHP 8.1+, Annotations/Attributes hybrid |

## 2. Core Rules (Applies to ALL)

> **Full Standards**: `v{10|11}/drupal-best-practices/references/coding-standards.md`

1. **Strict Types**: `declare(strict_types=1);` (MANDATORY).
2. **No `#markup`**: Use `html_tag` or `#theme`.
3. **DI**: Inject dependencies via Constructor Promotion.
4. **AJAX**: No `readonly` properties in AJAX form classes.
5. **Namespaces**: Always use `use` statements at the top of the file instead of fully qualified inline namespaces (e.g., use `Url` instead of `\Drupal\Core\Url`).
6. **Views Data**: Content Entity **PHẢI** có `'views_data' => EntityViewsData::class` trong handlers. Thiếu = entity không tạo được Views.

## 3. Sub-Skills

- **Setup**: `drupal-setup` (DDEV, Git).
- **Verify**: `drupal-verify` (Status checks).
- **Modules**:
    - **Controller**: `controller-guide.md`
    - **Form**: `form-guide.md`
    - **Service**: `service-guide.md`
    - **Entity**: `entity-guide.md`
