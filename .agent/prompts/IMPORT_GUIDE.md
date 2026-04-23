# EverLMS — Import Guide

## Quick Start

```bash
# 1. Validate generated files
drush everlms:import --dry-run

# 2. Import all content
drush everlms:import

# 3. Verify
drush everlms:seed-input --show
```

## Import Order

The import follows `manifest.json` order to respect entity dependencies:

1. **Users** — instructors first, then students
2. **Courses** — course → chapters → lessons (per file)
3. **Quizzes** — quiz → questions → answers/pairs
4. **Certificates** — templates
5. **Commerce** — membership plans, orders
6. **Community** — forums, ratings, badges

## Common Issues

| Issue | Solution |
|-------|---------|
| "Entity type not found" | Ensure all LMS modules are enabled: `drush pm:list --type=module --status=enabled \| grep lms` |
| "User already exists" | Duplicate email — entity is skipped automatically |
| "Course already exists" | Duplicate name — entity is skipped automatically |
| Import fails mid-way | Already-imported files are kept. Fix the error and re-run with `--file=path/to/failed.json` |

## Full Re-generate Flow

```bash
# Clean everything
drush everlms:clean

# Re-generate with AI (open Claude Code or Gemini)
# Ask: "Read seed input and generate all content"

# Import fresh
drush everlms:import
```

## Selective Import

```bash
# Import only courses
drush everlms:import --type=courses

# Import a single file
drush everlms:import --file=courses/course-001-react-fundamentals.json
```
