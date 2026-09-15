# Quick Edit Study Card Plan

## Goal

Add a `Quick edit` action while studying flashcards. When the current card belongs to a deck created by the current user, the button opens a right-side edit panel for that exact card. The student can update learning content in-place, especially pronunciation/audio and image, then save and continue studying without leaving the session.

Official/shared decks remain study-only. A student must not be able to quick edit cards from content they did not create.

## Current Code Context

- Study page route: `/learn/flashcards/{deck}/study`
- Study page controller: `src/Controller/StudyController.php`
- Study API controller: `src/Controller/FlashcardApiController.php`
- Study UI template: `templates/lms-flashcard-study-session.html.twig`
- Study JS: `js/flashcard-study.js`
- Study CSS: `css/flashcard-study.css`
- Card entity fields already include:
  - `name`
  - `field_definition`
  - `field_part_of_speech`
  - `field_pronunciation`
  - `field_example_sentences`
  - `field_example_translations`
  - `field_collocations`
  - `field_collocation_translations`
  - `field_audio`
  - `field_image`
  - `field_difficulty`
- Card has no owner field. It inherits edit ownership from its deck owner via `FlashcardAccessControlHandler`.

## Access Rule (updated after review)

Quick edit should be available only when:

1. User is logged in.
2. Current card belongs to the current study deck.
3. Either:
   - Current deck owner id equals current user id (student editing their own deck), **or**
   - Current user has the `edit lms_course` permission (admin/staff override, decided in review — admins may quick-edit official/shared decks too, not just their own).

Do not rely only on hiding the button in JS. The save endpoint must enforce the same check server-side.

**Implementation simplification:** since this is exactly what `FlashcardAccessControlHandler::checkAccess()` already computes for the `update` operation (owner OR `edit lms_course`), prefer calling `$card->access('update', $account)` instead of writing a new bespoke owner-only check. This also means Card's existing access handler already gives the right answer — no new permission needed.

## UX Plan

### Button Placement

Add a compact `Quick edit` button near the study controls, ideally visually separate from answer/progress actions:

- Show after a card is loaded and `card.can_quick_edit === true`.
- Hide for shared/official cards.
- Disable while a save is in progress.
- Do not interfere with flip, quiz, spell, or pronunciation click handlers.

Suggested placement:

- Inside `data-flashcard-stage`, above or beside the card on desktop.
- On mobile, place it above the card or as a fixed small action below the progress header.

### Sidebar Behavior

Add a right-side drawer/sidebar:

- Opens when clicking `Quick edit`.
- Shows the current term in the header.
- Has close/cancel controls.
- Saves via AJAX.
- Shows success/error state inline.
- After save, updates the current `currentCard` object and visible card content immediately.
- **Decided in review:** on successful save, show a brief inline "Saved" confirmation, then auto-close the drawer after a short delay (e.g. ~800ms–1s) and return focus to the study card. Do not require a manual close click on success.
- Capture the target `cardId` at the moment the drawer opens and always save/patch against that captured id — if the study queue somehow advances while the drawer is open, guard against patching the wrong card (compare the response's card id to the captured id before applying it to `currentCard`).

Sidebar should not flip the flashcard when clicked. All sidebar clicks need to stop propagation from reaching the card.

### Editable Fields

Keep the first version focused but useful:

- Term
- Definition
- Part of speech
- Pronunciation / IPA
- Example sentence + translation rows
- Collocation + translation rows
- Pronunciation audio upload
- Image upload
- Difficulty

For audio/image, use `multipart/form-data` so file uploads work cleanly.

**Decided in review:** first version includes explicit "Remove current image" / "Remove current audio" controls, not just replace-on-upload. Each shows only when the field currently has a value, and clears the field (plus triggers server-side file cleanup, see Backend Plan) independently of uploading a new file.

### Image Display

Current API returns `image_url`, but the study card does not visibly render it yet. Add display support as part of implementation:

- Show card image on the front or back, depending on available space.
- Prefer a stable image area so card layout does not jump.
- After saving a new image, update the visible image without requiring page reload.

## Backend Plan

### 1. Add API Routes

Add routes to `lms_flashcard.routing.yml`:

- `GET /api/lms/flashcard/card/{lms_flashcard_card}/quick-edit`
- `POST /api/lms/flashcard/card/{lms_flashcard_card}/quick-edit`

Both require logged-in users.

### 2. Add Controller Methods

Add methods to `FlashcardApiController` or a new small controller if the file gets too large:

- `quickEditFormData(Card $card)`
- `quickEditSave(Card $card, Request $request)`

Recommended access helper:

```php
private function canQuickEditCard(Card $card, AccountInterface $account, ?int $deckId = NULL): bool
```

It should:

- Load the card's deck.
- Confirm deck exists.
- If `$deckId` is supplied, confirm the card belongs to the active deck.
- Return true when `$card->access('update', $account)` is true — i.e. deck owner OR `edit lms_course` permission (updated rule, see Access Rule section). This reuses `FlashcardAccessControlHandler` instead of re-implementing the owner check by hand.

### 3. Include Edit Capability In `nextCard()`

Add fields to the card JSON:

- `can_quick_edit`
- optionally `quick_edit_get_url`
- optionally `quick_edit_save_url`

This lets JS decide whether to show the button for the currently displayed card.

### 4. Save Payload

Use `multipart/form-data` fields:

- `deck_id`
- `term`
- `definition`
- `part_of_speech`
- `pronunciation`
- `difficulty`
- `examples_json`
- `collocations_json`
- `audio`
- `image`
- `remove_audio` (boolean flag — decided in review, see Editable Fields)
- `remove_image` (boolean flag — decided in review, see Editable Fields)

Validate:

- `deck_id` matches the card's deck.
- Required fields: term and definition.
- Allowed values for part of speech and difficulty match `Card::baseFieldDefinitions()`.
- Example/collocation rows should require both text and translation when one side is present.
- Audio extensions: mp3, wav, ogg, **and enforce the 5MB max already declared on `field_audio`** (don't rely on extension check alone).
- Image handling should use Drupal managed file APIs and respect image field validation where possible (dimensions/size limits from the field config).
- Validate uploaded file MIME type against the actual file content (e.g. via Drupal's file validators / `finfo`), not just the extension in the filename, to avoid disguised uploads.
- If `field_image`'s widget requires alt text, default alt to the card's `term` when not otherwise supplied, so saves don't fail validation silently.

### 5. File Cleanup (decided in review)

When a card's `field_audio` or `field_image` is replaced with a new upload, or explicitly removed via `remove_audio`/`remove_image`:

- Look up the previously referenced file entity (if any) before overwriting the field.
- Decrement/remove its usage via the `file.usage` service (`FileUsageInterface::delete()`) for this card's usage record.
- If usage drops to zero, delete the file entity (or mark temporary and let Drupal's temp-file GC handle it — prefer immediate delete here since this is user-initiated removal, not an abandoned draft).
- Apply the same cleanup whether the field's old value is being replaced by a new upload or cleared entirely.

### 6. Security Hardening Note (flagged in review, not yet resolved)

The existing `/api/lms/flashcard/*` POST routes (`submitReview`, `submitMastery`, etc.) rely only on `_user_is_logged_in` with no CSRF token, matching this module's established convention. The new quick-edit **save** route is higher-stakes (it mutates card content, not just study progress), so consider adding `_csrf_token: 'true'` to the route + sending `X-CSRF-Token` from `fetch()`, even though it deviates from the rest of the module. This is a suggestion to confirm before implementation, not a blocking decision.

## Frontend Plan

### 1. Template Markup

In `lms-flashcard-study-session.html.twig`, add:

- Quick edit button with `data-flashcard-quick-edit`.
- Drawer wrapper with `data-flashcard-edit-drawer`.
- Form fields with data attributes.
- Save/cancel buttons.
- Status/error region.
- Optional image preview and audio filename/current audio hint.

### 2. JS State

In `flashcard-study.js`, add state:

- `quickEditOpen`
- `quickEditSaving`
- references to drawer/form fields

Add functions:

- `toggleQuickEditButton(card)`
- `openQuickEdit(card)`
- `closeQuickEdit()`
- `populateQuickEditForm(card)`
- `collectQuickEditPayload()`
- `saveQuickEdit()`
- `applyQuickEditResponse(updatedCard)`

### 3. Re-render After Save

After successful save:

1. Merge returned card into `currentCard`.
2. Call `showCard(currentCard)`.
3. Keep drawer open with success message or close automatically after a short delay.

Important: `showCard()` currently resets flip state and may speak the card. If that feels disruptive, implementation can add a lighter `renderCurrentCard()` helper later. For first pass, reusing `showCard()` is acceptable but should be tested.

### 4. Existing Study Modes

Verify Quick Edit does not break:

- `style=flip`
- `style=spell`
- `style=quiz`
- `style=pronounce`
- `mode=practice`
- `direction=forward|reverse|mixed`

## CSS Plan

Update `flashcard-study.css`:

- Desktop: right-side drawer, fixed or sticky, with overlay/backdrop.
- Mobile: full-width bottom sheet or full-screen drawer.
- Button style should match current academic theme colors.
- Ensure drawer content scrolls internally.
- Ensure file inputs, textareas, and row controls fit mobile widths.
- Add stable image preview sizing.

## Testing Checklist

Manual checks:

1. Owner studying own deck sees `Quick edit`.
2. Plain (non-admin) user studying official/shared deck they don't own does not see `Quick edit`.
3. Admin/staff (`edit lms_course` permission) DOES see `Quick edit` on an official/shared deck they don't own (updated rule).
4. Tampered POST against a card the user has neither ownership nor `edit lms_course` access to returns 403.
5. Tampered POST with card from another deck returns 403 or 400.
6. Edit term/pronunciation and save; current card updates immediately; drawer shows "Saved" then auto-closes.
7. Upload image; image appears in study card after save.
8. Upload audio; speaker button uses new audio after save.
9. Remove existing image via the remove control; card shows no image after save, and the old file is deleted from the file system/usage table.
10. Remove existing audio via the remove control; speaker control reflects no audio after save, and the old file is deleted.
11. Replacing an existing image/audio with a new upload deletes the previously attached file (no orphaned file left behind).
12. Add/remove example pairs; saved pairs display on back face.
13. Add/remove collocation pairs; saved pairs display on back face.
14. Empty required term/definition shows validation error.
15. Sidebar works on mobile viewport without covering save button.
16. Flashcard keyboard/space behavior still works when drawer is closed.
17. Typing in drawer text fields does not trigger card keyboard shortcuts.
18. If the study queue advances while the drawer is open (edge case), the save response is not applied to the wrong card.

Automated/lightweight checks:

- `ddev drush cr`
- PHP lint changed PHP files.
- Browser smoke test on `/learn/flashcards/{deck}/study`.

## Implementation Order

1. Add server-side access helper and card-normalization helper.
2. Add quick edit GET/POST routes and controller methods.
3. Add `can_quick_edit` and URLs to `nextCard()` response.
4. Add study template button/drawer markup.
5. Add JS for open/populate/save/update.
6. Add CSS drawer and image display styles.
7. Run cache rebuild and manual owner/non-owner checks.

## Decisions (resolved in review, 2026-09-14)

- **Auto-close:** drawer shows a brief "Saved" confirmation, then closes automatically. No stay-open mode in v1.
- **Remove controls:** v1 includes explicit remove buttons for image and audio, not just replace-on-upload.
- **Admin visibility:** `Quick edit` is visible to admins/staff (`edit lms_course` permission) on official/shared decks too, not just their own — access rule is owner OR `edit lms_course`, matching the existing entity access handler.
- **Old file cleanup:** replacing or removing image/audio deletes the previously attached file immediately (via `file.usage`), rather than leaving it orphaned.

## Still Open (not yet decided)

- Whether to add `_csrf_token` protection to the new save route, deviating from the rest of the module's convention (see Backend Plan § Security Hardening Note).
