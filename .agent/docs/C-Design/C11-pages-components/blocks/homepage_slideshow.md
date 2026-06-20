---
block: Homepage slideshow
drupal_concept: content block (slideshow)
owner_module: lms_theme
region: hero
block_type: content_block
adequacy: PASS
---

# C11 Block - Homepage slideshow

## Purpose
Editor-managed hero slideshow on the homepage (slides with heading/subheading/image/link).

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | homepage_slideshow |
| Drupal primitive | content block (`block_content` slideshow type) / slideshow node |
| Owner module/theme | lms_theme |
| Placed on | P001 (hero region) |
| Cache | tags: block content |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Fields | `field_slide_heading`, `field_slide_subheading`, `field_slide_image`, `field_slide_link`, `field_slide_position` |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | carousel markup |
| Access | public |
| Responsive | yes |
| Editing | **editor-managed** content block — no hardcoded slides |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | homepage hero |

## Tests / Checks
- Editing a slide changes the homepage (edit-smoke); image alt present (a11y).
