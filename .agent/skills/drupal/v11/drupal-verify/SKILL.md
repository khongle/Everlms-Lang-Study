---
name: drupal-verify
description: Quick and efficient verification commands for Drupal components to avoid hanging and provide instant feedback.
---

# Drupal Verification Skill

This skill provides quick verification commands for Drupal components. Use these commands instead of waiting for slow or hanging commands.

## Why This Skill?

- **Avoid hanging commands**: Some Drush commands may hang without output
- **Fast feedback**: Get instant yes/no answers
- **Efficient debugging**: Quickly verify what's working and what's not

## Verification Commands

### 1. Check if Module is Enabled

**Fast way using grep** (returns immediately):
```bash
ddev drush pm:list --status=enabled --type=module | grep MODULE_NAME
```

**Example - Check Admin Toolbar**:
```bash
ddev drush pm:list --status=enabled --type=module | grep admin_toolbar
```

**Output interpretation**:
- If you see module name(s) → Module is **enabled**
- No output → Module is **not enabled** or **not installed**

**Alternative - JSON output** (for programmatic checking):
```bash
ddev drush pm:list --format=json --status=enabled --type=module | grep -i admin_toolbar
```

### 2. Check if Module is Installed (but may not be enabled)

```bash
ddev drush pm:list --type=module | grep MODULE_NAME
```

**Output will show**:
- Module name + "Enabled" → Installed and enabled
- Module name + "Disabled" → Installed but disabled
- No output → Not installed

### 3. Verify Drupal Site Status

**Quick check**:
```bash
ddev drush status --fields=bootstrap,db-status | head -5
```

**Output interpretation**:
- `bootstrap: Successful` → Site is working
- `db-status: Connected` → Database is connected

**Full status** (if needed):
```bash
ddev drush status
```

### 4. Check Database Connection

```bash
ddev drush sql:query "SELECT 1" 2>&1 | head -1
```

**Output**:
- `1` → Database is connected
- Error message → Database issue

### 5. Verify Cache is Working

**Clear cache and verify**:
```bash
ddev drush cr && echo "Cache cleared successfully"
```

**Check cache tables**:
```bash
ddev drush sql:query "SHOW TABLES LIKE 'cache%'" | head -10
```

### 6. Check if Site is Installed

```bash
ddev drush status --field=bootstrap
```

**Output**:
- `Successful` → Site is installed
- `No database connection` → Site not installed or DB issue

### 7. List All Enabled Modules (quick)

```bash
ddev drush pm:list --status=enabled --type=module --no-core | head -20
```

This shows only contrib/custom modules (excludes core).

### 8. Verify Drush is Available

```bash
ddev exec which drush 2>&1 | head -1
```

**Output**:
- Path to drush (e.g., `/var/www/html/vendor/bin/drush`) → Drush is installed
- `not found` or empty → Drush is not installed

### 9. Check Composer Packages

**Check if package is installed**:
```bash
ddev composer show drupal/PACKAGE_NAME 2>&1 | head -5
```

**Example**:
```bash
ddev composer show drupal/admin_toolbar 2>&1 | head -5
```

**Output**:
- Package details → Installed
- `Package drupal/admin_toolbar not found` → Not installed

### 10. Verify Web Server is Running

```bash
ddev describe --json-output 2>&1 | grep -o '"status":"[^"]*"' | head -1
```

**Or simply**:
```bash
ddev describe | head -10
```

## Best Practices for Verification

### DO ✅

1. **Use `| head` or `| grep` to limit output** - Prevents hanging on large outputs
2. **Use `2>&1` to capture errors** - See what went wrong
3. **Check specific fields with `--field=FIELD`** - Faster than full output
4. **Use `--format=json` for parsing** - Better for scripting
5. **Add timeout context in commands** - e.g., `timeout 5s ddev drush ...` if available

### DON'T ❌

1. **Don't run bare `drush pm:list` without filters** - Too much output
2. **Don't wait indefinitely for commands** - Use `| head` to limit
3. **Don't assume command succeeded without verification** - Always check output
4. **Don't enable module without checking if it's installed first**

## Example Workflow: Enable Admin Toolbar

```bash
# Step 1: Check if already enabled
ddev drush pm:list --status=enabled --type=module | grep admin_toolbar

# Step 2: If not enabled, check if installed
ddev composer show drupal/admin_toolbar 2>&1 | head -3

# Step 3: If not installed, install it
ddev composer require drupal/admin_toolbar

# Step 4: Enable the modules
ddev drush pm:enable admin_toolbar admin_toolbar_tools -y

# Step 5: Verify it's enabled
ddev drush pm:list --status=enabled --type=module | grep admin_toolbar
```

## Troubleshooting Hanging Commands

If a Drush command hangs:

1. **Terminate it**: Send terminate signal to the command
2. **Use alternative**: Try the grep/head limited version above
3. **Check container**: Run `ddev describe` to ensure containers are running
4. **Direct exec**: Use `ddev exec drush` instead of `ddev drush`

## Quick Reference Table

| What to Check | Command |
|---------------|---------|
| Module enabled? | `ddev drush pm:list --status=enabled --type=module \| grep MODULE` |
| Module installed? | `ddev composer show drupal/MODULE \| head -3` |
| Site working? | `ddev drush status --field=bootstrap` |
| DB connected? | `ddev drush sql:query "SELECT 1"` |
| Drush available? | `ddev exec which drush` |
| Clear cache | `ddev drush cr` |
| DDEV running? | `ddev describe \| head -10` |

## Integration with Setup

This skill complements the `drupal-setup` skill by providing verification after each step. Use these commands to confirm:

- ✅ Drupal installed: `ddev drush status --field=bootstrap`
- ✅ Drush available: `ddev exec which drush`
- ✅ Module enabled: `ddev drush pm:list --status=enabled | grep MODULE`
- ✅ Database connected: `ddev drush sql:query "SELECT 1"`
