---
name: drupal-best-practices
description: Best practices and coding standards for Drupal 11 development.
---

# Drupal 11 Best Practices Skill

This skill outlines the core coding standards and architectural patterns for Drupal 11.

## Core Concepts

### 1. Dependency Injection (DI)

Drupal uses a dependency injection container (Symfony based). **Always** use DI instead of global functions (`\Drupal::service()`).

- **Controllers/Forms**: Implement `ContainerInjectionInterface` (usually via extending `ControllerBase` or `FormBase`) and use `create()` method.
- **Services**: Define arguments in `services.yml`.
- **Plugins**: Implement `ContainerFactoryPluginInterface`.

### 2. Entity API

- **Loading**: Use `EntityTypeManager` service.
- **Deep Dive**: **READ** `references/entity-guide.md` for details on Content vs Config entities, Fields, and Handlers.

### 3. Caching

- **Cache Tags**: invalidates content when dependent objects change (e.g., `node:1`).
- **Cache Contexts**: varies content based on request context (e.g., `user`, `url.query_args`).
- **Render Arrays**: Always include `#cache` metadata.

### 4. Security

- **Sanitization**: Use `User input` -> **Validate** -> **Sanitize** -> **Output**.
- **Database**: Always use placeholders in queries (DB API).
- **Rich Text**: Use `Xss::filter()` for user content.

## Coding Standards

**READ** `references/coding-standards.md` for comprehensive rules on:

- **PHP**: Indentation, Strict Types, Naming.
- **JS**: Airbnb style, Drupal.behaviors.
- **CSS**: SMACSS, BEM.
- **SQL, Twig, YAML, Spelling**.

**Key Rules needed EVERY time:**

1.  **Indentation**: 2 spaces.
2.  **No Globals**: Forbidden `\Drupal::service()` in classes.
3.  **Strict Types**: `declare(strict_types=1);` in all PHP files.

## References

- [Drupal Coding Standards Home](https://project.pages.drupalcode.org/coding_standards/)
- [Drupal API Overview](https://api.drupal.org/api/drupal)
- [Entity API Docs](https://www.drupal.org/docs/drupal-apis/entity-api)
- [Services & Dependency Injection](https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection)
