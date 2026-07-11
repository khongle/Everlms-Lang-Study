# C16 Sheet - 11 templates-ui

Giải pháp cho template/component. Nguồn: [C09](../C09-themes/_index.md), [C11 blocks](../C11-pages-components/blocks/_index.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Theme | lms_theme | Drupal theme (16 regions, 21 libs) | lms_theme | Twig + SCSS + 15 skins | C09 | skin smoke |
| Component | course_card | Views row / SDC | lms_course | reusable card | C11 blocks | render |
| Component | curriculum/progress/quiz_player | block plugins | lms_course/lms_progress/lms_quiz | placed blocks | C11 blocks | render |
| Component | notification_bell | block + AJAX | lms_notification | header badge | C11 blocks | unread count |
| Content block | homepage_slideshow | content block (slideshow) | lms_theme | editor-managed slides | C11 blocks | edit-smoke |
| PDF template | certificate | mPDF + template entity | lms_certificate | editor layout → PDF | C11 P040 | render |
| Status tokens | status colors | tokens + text/icon | lms_theme | color + redundant cue (a11y) | C09 | a11y |
