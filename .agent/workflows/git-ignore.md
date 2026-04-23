---
description: Create or update .gitignore with options (e.g. --drupal, --agent)
tags: [git, ignore, configuration]
---

# `git-ignore` Workflow

This workflow manages the `.gitignore` file. It supports specific strict presets based on flags.

## Usage

- `git-ignore (Default)`: Adds standard Drupal rules to project root.
- `git-ignore --drupal`: Strictly checks for Drupal specific ignores.
- `git-ignore --path <dir>`: Creates/Updates `.gitignore` in the specified directory.
- `git-ignore --allow <folders>`: Whitelists specific folders (requires `*` ignore pattern if asking to ignore everything else).

## Instructions for Agent

1.  **Analyze Request**:
    -   **Path**: Did the user specify a location? (e.g., "in skills folder" or `--path .agent/skills`). If yes, target that directory. Default is project root.
    -   **Allow-list**: Did the user say "ignore everything except X and Y"? If so, use the "Ignore All" pattern:
        ```gitignore
        # Ignore everything
        *
        # Exception (Allow)
        !*/
        !.gitignore
        !X/
        !Y/
        ```
    -   **Standard Flags**: Did the user provide `--drupal` or `--node`? Apply standard templates accordingly.

2.  **Standard Template**:
    -   Ensure these patterns exist in `.gitignore`. **Append** if missing. **Do not overwrite** existing custom rules unless the file is empty.

    ```gitignore
    # DDEV
    .ddev/

    # IDEs
    .idea/
    .vscode/
    *.swp
    *.swo
    .DS_Store

    # Drupal & Composer
    /vendor/
    /web/core/
    /web/modules/contrib/
    /web/themes/contrib/
    /web/profiles/contrib/
    /web/libraries/

    # Sites / Config
    /web/sites/*/files/
    /web/sites/*/private/
    /web/sites/*/tmp/
    /web/sites/*/cache/
    /web/sites/*/*settings*.php
    /web/sites/*/*services*.yml
    /web/sites/development.services.yml
    /web/sites/example.settings.local.php
    /web/sites/example.sites.php

    # Root Files (Composer Managed)
    /web/autoload.php
    /web/index.php
    /web/update.php
    /web/robots.txt
    /web/web.config

    # Environment
    .env
    .env.local
    /private/
    /config/
    /drush/contrib/

    # Node
    node_modules/

    # Node
    node_modules/
    ```

3.  **Verification**:
    -   Run `cat .gitignore` to show the result to the user.
