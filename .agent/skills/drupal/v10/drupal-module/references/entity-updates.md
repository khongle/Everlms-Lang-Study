# Entity Updates Guide (hook_update_N)

This guide documents the **mandatory best practices** for adding or modifying Entity Fields (e.g., `commerce_order`, `user`, custom entities) on a live/production Drupal site.

## 1. Core Principle (The Drupal Way)
**NEVER** use standalone PHP scripts (via `drush scr`) to create or modify fields on a standard environment.
All Database, Schema, or Entity Field updates **MUST** be placed in a `hook_update_N()` function inside the `[module_name].install` file.

### Why?
- **Tracking**: Drupal stores the update version (`schema_version`) in the `key_value` table. Deployments simply run `drush updb` to securely sync the database.
- **Maintenance/Audit**: It's easy to audit when a field was created and from which ticket/feature. Loose scripts cause database deviance across servers.

## 2. Implementation Guide (Adding Fields Programmatically)

Here is the standard procedure to add a new custom field via Code:

### Step 1: Write hook_update_N in the `.install` file

```php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Add field_customer_name to commerce_order entity.
 */
function mymodule_update_10001(): void {
  $field_name = 'field_customer_name';
  $entity_type = 'commerce_order';
  $bundle = 'default';

  // 1. Create Field Storage (Database Table)
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'string',
      'cardinality' => 1,
      'settings' => [
        'max_length' => 255,
      ],
    ])->save();
  }

  // 2. Create Field Instance (Attach to Bundle)
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => 'Customer Name',
      'required' => FALSE,
    ])->save();
  }
}
```

### Step 2: Ensure Proper Execution

When applying updates via DDEV or Drush:
1. Ensure the `N` in `hook_update_N()` is strictly greater than the current schema version of the module. If a hook doesn't run, check if its number is too low.
2. Run `ddev drush updb -y` (use `-n` or `--no-interaction` if running via Agent scripts to prevent terminal hangs).
3. Clear caches: `ddev drush cr` so the Entity Field Manager recognizes the new field.

## 3. Agent Execution Rules for Updates
- **Agent Action**: If requested to add a field to an existing project entity, **ALWAYS** generate the code inside the `.install` file.
- **Terminal Hangs**: If `drush updb` hangs without output (due to interactive prompts), terminate the tool and try `ddev drush updb -y --no-interaction`.
- **Workaround (Extreme Cases)**: If `drush updb` fundamentally fails to run in the container pipeline but the field is urgently needed, you may evaluate the update function explicitly:
  ```bash
  ddev drush php-eval "\Drupal::moduleHandler()->loadInclude('mymodule', 'install'); mymodule_update_10001();"
  ```
- **Do NOT**: Do not delete the `hook_update_N()` code and move it to a temporary script. The `hook_update_N()` must remain in the repository history.
