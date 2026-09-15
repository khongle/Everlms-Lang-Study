# Plan: Bring Study Notes to Listening + Reading

## 1. The request

From the Active Listening page (`/learn/listening/97`), the user wants to be
able to jot down a note while studying — the same way a "My Notes" feature
apparently already exists for course video lessons. Quoted request:

> "tôi cần đem cái function note và trong đây thí dụ tôi học 1 bài và muốn
> note gì đó thì có thể note dc bạn lên plan trước hen khoan làm gì vội nha"
>
> (I need to bring the note function in here too — say I'm studying a lesson
> and want to jot something down, I should be able to note it. Plan first,
> don't rush into building it.)

**No implementation yet** — this document is the plan, for review before any
code changes.

## 2. Confirmed scope (via clarifying questions)

| Question | Decision |
|---|---|
| Which modules? | **Listening (`lms_listening`) and Reading (`lms_reading`) only.** Flashcard explicitly excluded ("flashcard thì khỏi đi"). |
| Notes per unit? | **One note per passage**, overwritten on edit — not a running log of many timestamped notes. |
| Where on the page? | **An inline textarea on the study page itself** (not a popup/sidebar), same as the screenshot's mental model. |
| Passive Listening too? | **Yes — both Active and Passive Listening pages get the note box**, not just Active. |
| Attachments (image/pdf/audio)? | **No — text only** for this pass. |
| A page to review all notes later? | **Yes — one unified "My Notes" page** across everything. |

So the final surface is 3 pages, each gets the same note widget:
- `lms-listening-passage-view.html.twig` (Active Listening)
- `lms-listening-passive-view.html.twig` (Passive Listening)
- `lms-reading-passage-view.html.twig` (Reading)

## 3. What already exists (found during research — reuse, don't rebuild)

There's already a working, tested "Student Note" system, built for course
video lessons:

- **`lms_note` module** — entity `Note` (`lms_note` content entity), fields:
  `field_course` (required, `lms_course` ref), `field_lesson` (required,
  `lms_lesson` ref), `field_body` (long text), `field_video_timestamp`,
  `field_attachments` (files, unlimited), `created`/`changed`.
- **`NoteController`** (`lms_note`) — `save()`, `delete()`, `getNote()`,
  `uploadAttachment()`, `removeAttachment()`, all keyed by
  `uid` + `field_lesson` (one note per user per lesson — "load if exists,
  else create" pattern). Plus `myNotes()`, powering `/my-notes`.
- **`/my-notes` page** already exists (`lms-my-notes.html.twig`), grouping
  notes by course → lesson, dark-card style, independent of the
  cream "academy" skin the study pages use.
- **The actual note widget UI/JS** lives in the **theme**, not in
  `lms_note`: `web/themes/custom/lms_theme/js/main.js` (~line 199–570),
  wired to a lesson-view template. Textarea + Save + Delete buttons +
  attachment list, explicit Save (not autosave-on-type), a status line
  ("Saved successfully!" / "Error saving" / etc.), disabled state while
  the initial `getNote` fetch is in flight.

**Decision: generalize this existing system instead of building a parallel
one.** The lesson-note code path keeps working completely unchanged;
Listening/Reading become two more "context types" the same entity and
controller understand.

## 4. Schema change — `lms_note` entity

Add two new base fields via an update hook (not touching `field_course`/
`field_lesson`, which stay as-is for lesson notes):

- `field_context_type` (string, e.g. `lesson` | `listening_passage` |
  `reading_passage`)
- `field_context_id` (integer — the id of the passage, or of the lesson for
  old rows)

Also relax `field_course` and `field_lesson` from `setRequired(TRUE)` to
optional, since a Listening/Reading note has neither.

**Backfill, learned from this session's own `Card.status` incident** (an
`installFieldStorageDefinition()` default does *not* populate existing
rows — has to be a real `UPDATE` in the same or a follow-up hook): the
update hook that adds these columns will also run
`UPDATE lms_note SET field_context_type = 'lesson', field_context_id =
field_lesson WHERE field_context_type IS NULL`, then verify via a row-count
query before/after, matching how the `status` backfill bug was caught and
fixed earlier in this session.

Uniqueness lookup in the controller moves from
`condition('field_lesson', $id)` to
`condition('field_context_type', $type)->condition('field_context_id', $id)`
— `'lesson'` is just one more context type after this change, so existing
lesson notes keep working through the exact same code path.

## 5. Controller changes — `NoteController`

Each existing method (`save`, `delete`, `getNote`, and the two attachment
endpoints — attachments stay lesson-only per §2, so those two are
untouched) accepts **either**:
- `lesson_id` + `course_id` (existing lesson-note callers — theme's
  `main.js`, unchanged, zero regression risk), **or**
- `context_type` + `context_id` (new — what Listening/Reading pages send).

Internally both resolve to the same `field_context_type`/`field_context_id`
pair (`lesson_id`/`course_id` is sugar for `context_type: 'lesson'`).
No new routes needed — same 5 endpoints, just accepting one more input
shape.

Validation added: `context_type` must be one of the known values
(`listening_passage`, `reading_passage`) and `context_id` must resolve to a
real entity of that type — otherwise `400`. No extra ownership/access gate
beyond "logged in": matches the existing lesson-note posture (any
authenticated user who can reach the study page can note it — the page's
own controller already gated visibility before the note widget ever
renders).

## 6. New shared client widget (avoid copy-pasting the JS 2 more times)

Extract the note-taking JS/CSS out of the theme's `main.js` into a small
reusable Drupal library owned by `lms_note` itself:
`lms_note/note_widget` — `js/note-widget.js` + `css/note-widget.css`.

Shape: `Drupal.behaviors.lmsNoteWidget` attaches to any
`[data-lms-note-widget]` container, reading `contextType`/`contextId` off
`data-*` attributes (or a small `drupalSettings.lmsNoteWidget` map keyed by
a DOM id, for pages — like Passive Listening — that swap content via JS
after the initial page load). Same textarea + Save + Delete + status-line
behavior as today's lesson widget, just parameterized instead of hardcoded
to `lesson_id`/`course_id`.

**The existing lesson-view template/theme JS is left alone** — safest path,
zero regression risk to a working feature. It can be migrated onto the
shared widget later as a separate, optional cleanup if wanted.

## 7. Per-page integration

- **Active Listening** (`lms-listening-passage-view.html.twig` /
  `ListeningController::listen()`): note box placed below the
  transcript/questions area (mirrors the screenshot). `contextType:
  'listening_passage'`, `contextId: <passage id>`. Styled with the same
  teal palette as the rest of that page's `listening-pages.css`.
- **Passive Listening** (`lms-listening-passive-view.html.twig` /
  `listening-passive.js`): trickier — the page swaps to a *new* passage's
  content client-side without a page reload as the loop advances. The note
  box needs to **save/reload per passage** as `renderPassage()` runs (same
  place the title/transcript/vocab already get swapped), not just once at
  page load. Session stays paused/still playable while typing — typing
  itself doesn't need to pause playback.
- **Reading** (`lms-reading-passage-view.html.twig` /
  `ReadingController`'s passage view): note box below the passage body,
  same treatment. `contextType: 'reading_passage'`, `contextId: <passage
  id>`.

## 8. "My Notes" page changes

`NoteController::myNotes()` currently only loads/groups lesson notes
(`condition('field_lesson', ...)`-shaped query, requires a resolvable
course+lesson or the note is silently dropped). Extend it to also query
`context_type IN ('listening_passage', 'reading_passage')`, resolve each to
its passage entity + a link back to that passage's study page, and group
results into **3 sections** instead of course-only grouping:
- **Course Lessons** (existing behavior, untouched)
- **Listening**
- **Reading**

Template (`lms-my-notes.html.twig`) gets 2 more group blocks matching the
existing dark-card visual style, each note linking to
`lms_listening.listen` / `lms_reading.read` with that passage's id.

## 9. Testing plan (same rigor as everything else this session)

- `php -l` every touched/new PHP file.
- `node --check` the new/updated JS.
- CSS brace-balance check (Python one-liner used throughout this session).
- `ddev drush cr` after every batch of changes.
- Direct SQL query to confirm the update hook's backfill actually ran
  (`field_context_type` non-null on every pre-existing row) — before
  trusting it, given the `Card.status` lesson from earlier.
- `curl` round-trip, logged in as uid 127, for: save → get → delete on a
  Listening passage note and a Reading passage note.
- `curl` regression check that the **existing lesson note flow still
  works unchanged** (save/get/delete via `lesson_id`/`course_id`) — this is
  the one that must not break.
- Load `/my-notes` via curl and confirm all 3 groups render.

## 10. Small implementation choices (flagging, not asking again)

Going with the lowest-risk option — reusing the lesson-note pattern
exactly — unless you say otherwise when reviewing this plan:
- Explicit **Save** button (not autosave-on-blur/on-type), same as lesson
  notes today.
- Textarea is plain text (the lesson widget doesn't expose a rich-text
  toolbar despite storing `basic_html` format under the hood) — Listening/
  Reading notes will do the same.
- A note is deleted with a confirm dialog (`window.confirm(...)`), same as
  today.

---

**Not implementing anything from this yet** — reviewing this plan with you
first, per your ask.
