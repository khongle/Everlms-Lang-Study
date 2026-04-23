---
name: drupal-module
description: Generate custom Drupal 10 modules and components (Controllers, Forms, Services, Plugins) using Annotations.
---

# Drupal 10 Module Generation Skill (Annotations)

This skill routes you to specific guides for creating module components using **Annotations** (Legacy/Drupal 10 style).

> **⚠️ MANDATORY: DRUPAL API FIRST**
> Before generating ANY code, you MUST identify which Drupal API handles the task.
> If no Drupal API exists, you MUST ask the user for approval before writing custom code.
> See `../../rules/code-rules.md` Section 1 for the complete decision process.

## 0. Prerequisites

Before generating module code, check if `coding-standards.md` has been read in this conversation:
- **If NOT**: Read `../drupal-best-practices/references/coding-standards.md` first
- **If YES**: Proceed with code generation

## 1. Module Scaffolding

- **Info File**: `web/modules/custom/{module_name}/{module_name}.info.yml`
- **Standard**:
  ```yaml
  name: 'My Module'
  type: module
  description: 'Description.'
  package: Custom
  core_version_requirement: ^10 || ^10 || ^11
  ```

## 2. Component Guides (References)

**ALWAYS** read the specific reference file before generating code.

| Component | Reference File | Key Feature |
| :--- | :--- | :--- |
| **Entities** | `references/entity-guide.md` | `@ContentEntityType`, `@ConfigEntityType` |
| **Entity Updates** | `references/entity-updates.md` | `hook_update_N()`, Field Storage |
| **Blocks** | `references/block-guide.md` | `@Block` annotation |
| **Forms** | `references/form-guide.md` | `FormBase`, `ConfigFormBase` |
| **Controllers** | `references/controller-guide.md` | Routes, Responses |
| **Services** | `references/service-guide.md` | `services.yml`, DI |
| **Fields** | `references/field-guide.md` | `@FieldType`, `@FieldWidget`, `@FieldFormatter` |

## 3. Usage Pattern

1. **Check API First**: Before writing ANY code, identify which Drupal API handles the task (Form API, Entity API, AJAX API, etc.). If unsure, ask the user.
2. **Identify Component**: User asks for a "Block" or "Custom Entity".
3. **Read Reference**: `view_file` the corresponding guide in `references/`.
4. **Generate Code**: Use the patterns found in the guide (Annotations).
5. **No Custom Without Approval**: If Drupal has no built-in API for a task, STOP and ask the user before writing custom code.
