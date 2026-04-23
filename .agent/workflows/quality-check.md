---
description: Run PHPCS and PHPStan to check code quality and report issues.
---

# Code Quality Check

This workflow runs PHP CodeSniffer and PHPStan to identify code quality issues in custom modules and themes.

## 1. Run PHP CodeSniffer

Check for Drupal coding standard violations.

// turbo
```bash
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom web/themes/custom
```

## 2. Run PHPStan

Check for static analysis errors (undefined methods, type mismatches, etc.).

// turbo
```bash
ddev exec vendor/bin/phpstan analyze web/modules/custom web/themes/custom
```

## 3. Auto-fix (Optional)

Attempt to automatically fix coding standard violations.

```bash
ddev exec vendor/bin/phpcbf --standard=Drupal,DrupalPractice web/modules/custom web/themes/custom
```
