---
id: WF-008-003
workflow: WF-008
title: Forum authoring UI has raw form, editor chrome, and unreadable message placeholder
severity: medium
category: ui
status: done
roles: student
source: visual QA
found: 2026-06-30
---

# WF-008-003 - Forum authoring UI rough edges

## Problem
The passing WF-008 run exposed several UI defects:

- Direct `/forum/new` renders a raw entity form instead of the intended
  `lms_forum_new_topic_page` wrapper with heading, card, and back link.
- The reply form exposes authoring chrome such as `Rich Text Editor`,
  `About text formats`, and `Basic HTML`.
- The topic-created status message uses Drupal's `<em class="placeholder">`
  for the title, but Bootstrap styles `.placeholder` as a loading skeleton,
  making the title appear as a solid green block.

## Evidence
- `005-student-author-new-topic-before-fill.png` shows the raw form page.
- `007-student-author-topic-created-detail.png` shows the unreadable status
  message placeholder and exposed reply editor chrome.

## Impact
The forum flow works functionally, but the authoring pages look unfinished and
confusing. The most important confirmation text can be partially unreadable.

## Expected
Forum authoring should use the product page wrapper, learner-facing reply forms
should hide text-format implementation details, and status messages should show
substituted labels as normal readable text.

## Fix Plan
- Override the entity add route to use `LmsForumController::addTopic()`.
- Hide text format controls in `ForumReplyForm`.
- Add scoped message CSS so `.lms-messages-wrapper .placeholder` renders as
  readable inline text.
- Retest WF-008 screenshots 005, 007, and reply states.

## Resolution
- The entity add route for `/forum/new` now uses
  `LmsForumController::addTopic()` and the product page wrapper.
- `ForumReplyForm` hides text format controls from learner-facing reply forms.
- Message CSS now restores readable inline rendering for Drupal
  `.placeholder` substitutions inside `.lms-messages-wrapper`.
- Forum form-page contrast rules keep dark card headers readable.

## Verification
- Final WF-008 run passed with topic `43`.
- Screenshot `005-student-author-new-topic-before-fill.png` shows the wrapped
  authoring page with readable card header.
- Screenshot `007-student-author-topic-created-detail.png` shows readable status
  title text and a simplified reply form.
