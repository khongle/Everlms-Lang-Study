---
name: drupal-theme
description: Generate custom Drupal 10 themes, including file structure, libraries, and templates.
---

# Drupal 10 Theme Generation Skill

This skill provides instructions for generating custom themes.

## 1. Theme Scaffolding

Create the basic file structure for a theme.

- **File**: `web/themes/custom/{theme_name}/{theme_name}.info.yml`
- **Content**:
  ```yaml
  name: 'Theme Name'
  type: theme
  description: 'Description of the theme.'
  core_version_requirement: ^10 || ^11
  # base theme: stable9 or classy (deprecated) or false
  base theme: false
  package: Custom
  libraries:
    - {theme_name}/global-styling
  regions:
    header: Header
    content: Content
    sidebar_first: 'Sidebar First'
    footer: Footer
  ```

## 2. Libraries (CSS/JS)

- **File**: `web/themes/custom/{theme_name}/{theme_name}.libraries.yml`
  ```yaml
  global-styling:
    version: 1.x
    css:
      theme:
        css/style.css: {}
    js:
      js/script.js: {}
  ```
- **Structure**:
  - `css/style.css` (Create empty file)
  - `js/script.js` (Create empty file)

## 3. Templates & Setup

- **Folder**: `templates/`
  - Place `html.html.twig`, `page.html.twig`, `node.html.twig` here.
- **Hook File**: `{theme_name}.theme`
  - Use for `hook_preprocess_HOOK()`.
  - Example:

    ```php
    <?php

    /**
     * Implements hook_preprocess_html().
     */
    function {theme_name}_preprocess_html(&$variables) {
      $variables['attributes']['class'][] = 'my-custom-class';
    }
    ```

## 4. Logo and Screenshot

- `logo.svg` (Optional placeholder)
- `screenshot.png` (Optional placeholder)

## Usage Instructions

When asked to create a theme:

1.  **Ask for Theme Name** (machine name).
2.  Create **folder** `web/themes/custom/{machine_name}`.
3.  Create **info.yml** and **libraries.yml**.
4.  Create `css/` and `js/` directories with placeholder files.
5.  Create `templates/` directory.
6.  Create `{machine_name}.theme`.
