# C11 blocks - Block Spec Index

10 canonical custom blocks. Core/theme block placements live in `block.block.*` (53 total in config). Each block spec carries its Drupal primitive, region, cache, and editing rule.

| Block ID | Label | Primitive | Module | Region | Spec |
| -------- | ----- | --------- | ------ | ------ | ---- |
| course_card | Course card | Views row / SDC | lms_course | content | [course_card.md](course_card.md) |
| course_curriculum | Curriculum outline | block plugin | lms_course | content/sidebar | [course_curriculum.md](course_curriculum.md) |
| enroll_cta | Enroll / buy CTA | block plugin | lms_progress/lms_order | sidebar_second | [enroll_cta.md](enroll_cta.md) |
| progress_bar | Course progress | block plugin | lms_progress | content_top | [progress_bar.md](progress_bar.md) |
| quiz_player | Quiz player | block plugin + JS | lms_quiz | content | [quiz_player.md](quiz_player.md) |
| certificate_download | Certificate download | block plugin | lms_certificate | content | [certificate_download.md](certificate_download.md) |
| notification_bell | Notification bell | block plugin + AJAX | lms_notification | header | [notification_bell.md](notification_bell.md) |
| rating_widget | Rating & reviews | block plugin | lms_rating | content | [rating_widget.md](rating_widget.md) |
| homepage_slideshow | Homepage slideshow | content block | lms_theme | hero | [homepage_slideshow.md](homepage_slideshow.md) |
| dashboard_kpis | Dashboard KPIs | block plugin | lms_analytics | content_top | [dashboard_kpis.md](dashboard_kpis.md) |
