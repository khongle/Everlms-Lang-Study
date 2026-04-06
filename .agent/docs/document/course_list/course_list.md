# Course Catalog (Course List) Logic

## Overview
The EverLMS Course Catalog (`/courses`) serves as the central hub for learning materials. It implements dynamic filtering, pagination, and fallback states to ensure a high-end browsing experience. This document outlines the backend and frontend architecture for rendering this list.

## Core Architecture

### 1. Routing & Controllers
- **Route**: `lms_course.catalog` (`/courses`) and `lms_course.category_detail` (`/categories/{taxonomy_term}`)
- **Controller**: `\Drupal\lms_course\Controller\CoursePublicController`
- **Method**: `catalog()` and `categoryDetail()`

### 2. State & Database Fetching
When loading the catalog, an `EntityQuery` is executed against the `lms_course` entity type.
```php
$ids = $this->entityTypeManager->getStorage('lms_course')
  ->getQuery()
  ->condition('status', TRUE)
  ->sort('created', 'DESC')
  ->pager(12) // Restricts the query to 12 courses per view and signals Drupal's paginator.
  ->accessCheck(FALSE)
  ->execute();
```

### 3. Fallback Image Generation (Thumbnailing)
Many courses—especially those auto-generated via AI modules—may lack an attached physical cover image (`field_thumbnail` is empty or invalid). The controller's `buildCourseCards()` method intercepts this scenario:
1. It attempts to load the URI of `field_thumbnail`.
2. If invalid or empty, it checks a hardcoded dictionary map for AI-generated banners.
3. If no match is found, it injects a generic placeholder variable (e.g., `card.has_thumbnail = false`), instructing the frontend Twig template to render a stylized graduation cap icon layout (`.card-lms__image--placeholder`) instead of a broken `<img>` element.

### 4. Frontend Rendering (Twig)
- **Container Template**: `lms-catalog.html.twig` structures the search components (Left Sidebar) and the Grid View (Right Content Box).
- **Navigation (Pagination)**: The static DOM `<nav class="lms-pagination">` is overridden by Drupal's dynamic `{{ pager() }}` subsystem. 
- **Card Template**: `lms-course-card.html.twig` loops over the payload, conditionally rendering either a standard `<img src="url">` image tag or the `.card-lms__image--placeholder` block depending on the boolean flag computed by the Controller.
