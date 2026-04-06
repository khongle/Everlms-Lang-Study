# Course Taxonomy Synchronization (Categories, Levels, Languages)

This document outlines the lifecycle of taxonomy terms (Categories, Levels, Languages) automatically generated for courses by the EverLMS AI Content System.

## Architecture

The AI seed generation system leverages three primary course taxonomy references:
- **Course Category** (`course_category`) -> `field_category`
- **Course Level** (`course_level`) -> `field_level`
- **Course Language** (`course_language`) -> `field_language`

## 1. Seed Input Form (`SeedInputForm.php`)

To support infinite flexibility without hardcoding categories, the Seed Input Form provides `#type => 'textfield'` inputs for `category`, `level`, and `language` in each Course card.
- Users can input any category name (e.g., "Healthcare", "Programming", "Culinary Arts").
- **Automatic Extraction:** During submission, `buildSeedData` automatically scans all courses, identifies unique categories, and injects a root-level `categories` array into `seed-input.json`.

## 2. AI Content Generation Step

During generation, the AI reads the `categories` array from the Seed Input and generates an independent `category/categories.json` file. This file contains rich category data including names, generated image filenames, and descriptions.

It also ensures that each `course-XXX.json` accurately reflects the category strings (e.g., `"category": "Healthcare"`). 

## 3. Drush Import (`ContentToolsCommands.php`)

### 3.1. Independent Category Import
When running `ddev drush everlms:import --type=category` (or when `category/categories.json` is first in the manifest):
1. The script reads the `categories.json` file.
2. It calls `importCategories()` which creates the `taxonomy_term` within the `course_category` vocabulary if missing.
3. It associates the corresponding generated image from `.agent/images/image_category/` to the term's `field_image`.

### 3.2. Course Mapping
When importing courses (`importCourse()`):
1. The script reads the raw taxonomy string (e.g. `Healthcare`) from the JSON structure for the specific course.
2. It locates the previously established `taxonomy_term`.
3. Finally, it links the Taxonomy Term ID to the course via the `field_category` (and similarly for `field_level` / `field_language`) reference field.

## 4. Drush Clean (`ContentToolsManager.php`)

To prevent taxonomy term buildup ("rác"), the `ContentToolsManager` groups `taxonomy_term:course_category`, `taxonomy_term:course_level`, and `taxonomy_term:course_language` within its `ENTITY_GROUPS` constant.
When `ddev drush everlms:clean` is executed, all of these taxonomy terms are wiped cleanly.
