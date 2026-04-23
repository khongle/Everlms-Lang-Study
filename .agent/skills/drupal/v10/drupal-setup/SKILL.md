---
name: drupal-setup
description: automated setup for Drupal 10 projects using DDEV, including environment configuration, testing tools, and gitignore.
---

# Drupal 10 Setup Skill

This skill automates the setup of a Drupal 10 development environment using DDEV.

## Capabilities

### 1. Environment Initialization

Automates the creation of a DDEV configuration for Drupal 10.

**Instructions for the Agent:**

0.  **Load Coding Standards (if not already read)**: Check conversation context. If `coding-standards.md` has NOT been read, read `../drupal-best-practices/references/coding-standards.md`.
1.  **Determine Project Root**:
    -   **Current Directory (Default)**: If the user does not specify a folder, assume the current working directory is the project root.
    -   **New Directory**: If the user requests to create the project in a new folder (e.g., "create a new project named my-site"), run: `mkdir [name] && cd [name]`.
2.  **Check for DDEV**: Verify `ddev` is installed and valid.
3.  **Check for Docker**: Run `docker info` to ensure Docker is running. If not, instruct the user to start Docker.
4.  **Configure DDEV**:
    -   **Check for `composer.json`**:
        -   **Existing Project**:
            -   **Detect Docroot**: Check if `web/` or `docroot/` exists.
                -   If `web/` exists: Run `ddev config --project-type=drupal10 --docroot=web --create-docroot=false --php-version=8.3 --webserver-type=apache-fpm`.
                -   If `docroot/` exists: Run `ddev config --project-type=drupal10 --docroot=docroot --create-docroot=false --php-version=8.3 --webserver-type=apache-fpm`.
                -   If neither exists: **STOP and ASK** the user for the docroot directory name.
        -   **New Project**: If `composer.json` does NOT exist, run `ddev config --project-type=drupal10 --docroot=web --create-docroot --php-version=8.3 --webserver-type=apache-fpm`.
    -   _Note_: Use defaults (PHP 8.3, Apache) unless the user specifies otherwise.
5.  **Install PHPMyAdmin (Optional)**:
    -   If the user explicitly asks for PHPMyAdmin, run: `ddev get ddev/ddev-phpmyadmin`.
    -   _Note_: This must be done before starting the project or requires a restart (`ddev restart`).
6.  **Start Project**: Run `ddev start`.
7.  **Install/Update Dependencies**:
    -   **Existing Project**: If `composer.json` exists, run: `ddev composer install`.
    -   **New Project**: If `composer.json` is missing (and you are creating a new site), run: `ddev composer create drupal/recommended-project`.
8.  **Database Setup (New or Existing)**:
    -   **Fresh Install**: If `web/core` exists but database is empty and NO database dump is provided, offer to install via Drush: `ddev drush site:install --account-name=admin --account-pass=admin -y`.
    -   **Import Database**: If the user provides a database file (e.g., `.sql`, `.sql.gz`), run:
        1.  `ddev import-db --src /path/to/database.sql.gz`
        2.  `ddev drush cr` (Cache Rebuild)
        3.  `ddev drush uli` (Generate Login Link)

### 2. Testing Tools Setup

Installs and configures standard Drupal testing tools.

**Instructions for the Agent:**

1.  **Install Packages via Composer**:
    Run inside DDEV:
    ```bash
    ddev composer require --dev drupal/core-dev phpstan/phpstan mglaman/phpstan-drupal squizlabs/php_codesniffer
    ```
2.  **Configure PHPCS**:
    - Create `phpcs.xml` in the project root if it doesn't exist.
    - Content should reference Drupal coding standards.
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
3.  **Configure PHPStan**:
    - Create `phpstan.neon` in the project root.
    ```yaml
    parameters:
    level: 5
    paths:
      - web/modules/custom
      - web/themes/custom
    drupal:
      drupal_root: web
    ```

### 3. Git Ignore Setup

**Usage**: "Create gitignore", "Fix gitignore", or as part of setup.

**Instructions for the Agent:**

1.  **Read Workflow**: View `.agent/workflows/git-ignore.md`.
2.  **Execute**: Follow the instructions for the `--drupal` option to create/update the `.gitignore`.


## Usage Examples

- **User**: "Initialize a new Drupal 10 project."
  - **Action**: Run DDEV config, start, and composer create.
- **User**: "Setup testing tools."
  - **Action**: Install phpcs/phpstan and write config files.
- **User**: "Fix my gitignore."
  - **Action**: Write the `.gitignore` file.
