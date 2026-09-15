# Passive Listening Mode — Plan

## Goal

Add a second listening mode, "Passive Listening" ("nghe thụ động"), alongside the
existing comprehension-question mode ("nghe chủ động" in the user's own
wording, referred to below as **Active Listening** to avoid confusion with
Drupal's "active"/"published" terminology).

Passive Listening:

- Plays passages back to back, grouped **Easy** then **Normal** then
  **Challenge** (fixed order), each chosen **randomly** from the deck's
  passages at that level. How many per level is student-adjustable (see
  Decision 3 below; originally fixed at 1 each / 3 total, revised after
  trying it).
- For each passage: plays its transcript exactly like today's Listen step
  (sentence-by-sentence TTS), then reads aloud the **English term** of every
  flashcard linked to that passage (`field_cards`), then **automatically
  advances** to the next passage — no waiting for a click.
- **No comprehension questions, no scoring, no `ListeningAttempt` row.** Pure
  absorption ("thẩm thấu"): the student presses one "Start" button and then
  does nothing until the 3 passages are done.

Active Listening (today's Listen → Questions → Review flow) is **completely
unchanged** — this plan only adds a new option next to it.

## Decisions (confirmed with the user, 2026-09-15)

0. **Looping** (added 2026-09-15, third revision): the session repeats
   indefinitely — once the last passage in the queue finishes, it wraps
   back to the first one and keeps going, forever, until the student
   presses Stop. Implemented as a `$loop` flag on `ListeningQueueService::
   nextAfter()` (default FALSE, so Active mode's own "Next passage" is
   completely unaffected): when TRUE and the current passage was the
   queue's last item, it returns the first item again instead of ending
   and clearing the queue. `ListeningApiController::passiveNext()` always
   passes `loop: TRUE`, so `{status: 'done'}` is now only a defensive
   fallback for a lost/expired tempstore queue, never the normal way a
   session ends. The front end (js/listening-passive.js) tracks a lap
   count (detects the wrap by comparing the incoming passage id to the
   very first one) and shows "Passage N · Lap L" once it loops past 1.

1. **Unlock gate**: Passive Listening sits behind the exact same gate as
   today's Listening/Reading — `deck.practice_unlocked` (100% Study Now
   mastery). No new gating logic needed: it lives inside the same
   already-gated modal (see Decision 4), so this falls out for free.
2. **Vocabulary read-aloud content**: English term only (`Card::label()`).
   No translation, no pronunciation, no definition — keep it quick.
3. **Session composition** (revised 2026-09-15 twice, after trying the
   original "always exactly 3" version): Passive mode now shares Active
   mode's full picker — level checkboxes (which level(s) to include) and
   "Passages per level" (how many, random pick within each) — instead of
   being fixed to all 3 levels at 1 each. On top of that, a "choose
   specific passages" section (grouped by level, one checkbox per passage
   title) lets the student pick exact passages instead of a random
   per-level count; checking any box there overrides the level/per-level
   controls entirely for that session (ListeningQueueService::
   buildQueueFromIds(), which re-sorts whatever was picked back into
   Easy → Normal → Challenge order regardless of click order).
4. **Entry point**: No new button on the deck card. The existing "Listening"
   button/popup (`listening-start-modal.js`, and its no-JS fallback
   `ListeningStartForm`) gains a mode toggle at the top: **"Active" (default,
   today's checkboxes/count UI, unchanged) vs. "Passive"** (hides the
   level/count UI, shows one "Start passive listening" button).

## Current Code Context (from research — see also `LISTENING_TRAINING_PLAN.txt` at the repo root, the original design doc for the existing feature)

- `ListeningPassage` entity: `field_deck`, `field_cards` (the linked
  vocabulary — **this is the "notable words" source**, no new field needed),
  `field_level` (easy/normal/challenge), `field_format`
  (sentence/narrative/dialogue), `field_body`, `field_dialogue_speakers`/
  `field_dialogue_lines`, `field_hints`, `status`.
- No audio file field anywhere in this module — 100% browser
  `SpeechSynthesisUtterance`, language from `field_deck.entity.field_language`.
  Passive mode reuses this exact mechanism, no new audio pipeline.
- `ListeningQueueService` (tempstore `lms_listening_study`, key
  `queue:{uid}:{deckId}`): `buildFromLevels()` walks `LEVEL_ORDER = [easy,
  normal, challenge]` calling `pickForLevel()` per level (unattempted-first,
  newest-created, falls back to shuffled attempted); `setQueue()`/
  `nextAfter()` persist and walk the ordered id list.
- `ListeningController::listen()` builds the on-screen transcript (splits
  `field_body` into sentences, or zips dialogue speakers/lines into turns)
  and calls `recordPassageSeen()` (bumps `ListeningPassageStat`).
- `ListeningApiController::start()` is the JSON counterpart to
  `ListeningStartForm`: `{deck_id, levels[], per_level}` →
  `buildFromLevels()` + `setQueue()` → `{redirect_url}` pointing at
  `lms_listening.listen` for the first passage.
- `listening-start-modal.js`: progressive-enhancement popup over the plain
  `<a href="/learn/listening-start?deck=ID">` link, reads
  `data-level-counts` (JSON per-level counts) off that link, POSTs to
  `/api/lms/listening/start`, follows `redirect_url`.
- `ListeningVisibilityService::isPassageVisibleTo()` — owner-or-published-
  by-staff, and the passage's own deck must also be visible. Unchanged by
  this plan.

## Backend Plan

### 1. `ListeningQueueService` — two small, additive changes

- **Namespace the tempstore key by mode**, so an Active session and a
  Passive session for the same deck don't stomp on each other's queue:
  change the private key-builder (wherever `"queue:{$uid}:{$deckId}"` is
  assembled) to accept a `$mode` parameter (default `'active'`, so every
  existing call site is unaffected), producing
  `"queue:{$mode}:{$uid}:{$deckId}"` for passive calls. `buildFromLevels()`,
  `setQueue()`, `nextAfter()` all take the same new optional `$mode`
  parameter, defaulted so **every current caller keeps working with zero
  changes**.
- **Add `pickRandomForLevel(int $deckId, string $level, int $count):
  array`** — a new, separate method (does NOT touch `pickForLevel()`):
  loads all visible published passages in that deck+level, shuffles,
  takes `$count`. No unattempted/attempted split — passive mode doesn't
  track "already seen this one" the way scored review does, and the user
  explicitly asked for random, not "prefer fresh."
- **Add `buildPassiveQueue(int $uid, int $deckId): array`** — walks
  `LEVEL_ORDER` calling `pickRandomForLevel($deckId, $level, 1)` per level
  (skipping a level with 0 available passages rather than erroring — a
  deck missing e.g. Challenge passages just yields a 2-passage session).

### 2. `ListeningApiController::start()` — one new branch, existing behavior untouched

- Accept an optional `mode` field in the POST body (`'active'` default,
  `'passive'` new). When `mode !== 'passive'`, **the method's existing code
  path runs exactly as today** (this is the regression-safety net — no
  existing call can accidentally hit new code).
- When `mode === 'passive'`: ignore any `levels`/`per_level` in the body,
  call `buildPassiveQueue()`, `setQueue(..., mode: 'passive')`, and return
  `redirect_url` pointing at a **new** route (§4) for the first passage
  instead of `lms_listening.listen`. If the queue comes back empty (deck
  has zero listening passages at any level), return the existing
  "nothing matched" warning shape unchanged.

### 3. New JSON endpoint: `ListeningApiController::passiveNext()`

- Route `lms_listening.api_passive_next` = `POST /api/lms/listening/passive-next`.
- Body: `{deck_id, current_passage_id}`.
- Calls `ListeningQueueService::nextAfter($uid, $deckId, $currentPassageId,
  mode: 'passive')`. If nothing left: `{status: 'done'}`. Otherwise loads
  the next passage, calls `recordPassageSeen()` (same stat bump as Active
  mode — a passage "seen" passively still counts), and returns
  `{status: 'ok', passage: {...}}` in the same shape §4's page endpoint
  uses (see below) so the front end can reuse one render function for both
  the initial page load and every subsequent in-place update.

### 4. New page route + controller method: the passive session shell

- Route `lms_listening.listen_passive` =
  `GET /learn/listening-passive/{lms_listening_passage}` (upcast to
  entity, same access pattern as `.listen`).
- `ListeningController::listenPassive(ListeningPassage $lms_listening_passage)`:
  - Same visibility check as `listen()` (delegate to the same private
    helper if one doesn't already exist — worth factoring out during
    implementation rather than copy-pasting the check).
  - **Factor `listen()`'s transcript-building logic** (sentence-splitting /
    dialogue-zipping) into a shared private method, e.g.
    `buildTranscriptUnits(ListeningPassage $passage): array`, called from
    both `listen()` and `listenPassive()` — avoids two copies of that logic
    drifting apart. This is the one non-trivial refactor this plan asks
    for in existing code; everything else is additive.
  - Builds `vocabTerms` from `$passage->get('field_cards')->referencedEntities()`
    → each `Card::label()`.
  - Calls `recordPassageSeen()` (first passage of the session).
  - Attaches `drupalSettings.lmsListeningPassive = {deckId, passage: {id,
    title, level, transcriptUnits, vocabTerms}, nextUrl:
    '/api/lms/listening/passive-next', language}`.
  - Renders a new template (§ Frontend, "Passive session shell").

### 5. Access / visibility

No changes — `ListeningVisibilityService` and `ListeningAccessControlHandler`
are reused as-is; the new route's access requirement is the same
`_user_is_logged_in: 'TRUE'` + controller-side visibility check pattern
already used by every other `lms_listening` route.

## Frontend Plan

### 1. Start modal (`listening-start-modal.js` + `listening-start-modal.css`)

- Add a 2-way toggle at the top of the popup: **Active** (selected by
  default — today's exact markup: level checkboxes with "(N available)",
  per-level count input, "Start practicing" button) vs. **Passive** (hides
  only the level checkboxes — the "Passages per level" count input stays,
  shared with Active mode — plus a short one-line explainer: "Passages
  play back to back: Easy → Normal → Challenge, picked at random within
  each. Just listen, no questions." — and a "Start passive listening"
  button).
- Disable/hide the Passive option (with a note) if the deck's
  `data-level-counts` sums to 0 across all levels — no listening passages
  exist for this deck at all yet.
- On "Start passive listening" click: POST `{deck_id, mode: 'passive'}` to
  the same `/api/lms/listening/start` endpoint, follow `redirect_url`
  exactly like today's Active path already does — no new JS request
  plumbing needed here, only the new toggle UI and the `mode` flag.
- **No-JS fallback** (`ListeningStartForm`): add the same mode toggle
  (radios) there too, for parity with the "works without JS" philosophy
  this module already follows for Active mode. Flag clearly during
  implementation that a no-JS Passive session is inherently degraded — the
  entire "hands-free, auto-advance" premise needs JS; without it, the
  no-JS path can still land the student on passage 1's transcript (readable
  by eye, or via the OS's own screen-reader/TTS), but there is no
  auto-advance or in-browser read-aloud. Acceptable as a degraded fallback,
  not a blocker.

### 2. New template: `templates/lms-listening-passive-view.html.twig`

- Minimal, single-screen shell: passage title, level badge, a "Now
  playing…" status line, a big Stop/Exit control, and a small always-visible
  "Passage X of 3" progress indicator. No transcript reveal-gating like
  Active mode's locked hint bar — the whole point is hands-free, so nothing
  needs to be manually unlocked; the transcript can simply not be shown as
  text at all (audio-only), or shown read-only/highlighted-as-spoken for
  students who want to follow along visually — **implementation detail to
  settle when building, not a blocker to this plan**.

### 3. New JS file: `js/listening-passive.js`

- `Drupal.behaviors.lmsListeningPassive`, attached once on the passive
  session shell.
- On load: render passage 1 (already in `drupalSettings`) and show a single
  "▶ Start" button — **one explicit user gesture, required so
  `speechSynthesis.speak()` is reliably allowed to run at all** (browsers
  vary in how strictly they gate the Web Speech API behind user activation;
  requiring one tap up front, then chaining every subsequent utterance from
  `utterance.onend` callbacks within that same page (never a fresh
  navigation), is the safest way to keep the whole 3-passage sequence
  playing without hitting that wall again per passage).
- Playback chain per passage, entirely in-page (no navigation between
  passages):
  1. Speak each transcript unit in sequence (reuse the same
     sentence/dialogue-turn chaining approach `listening-passage.js`
     already uses for Active mode's Listen step — same voice/language
     selection, same "restart from scratch on rate/voice change" Windows
     SAPI workaround it already documents).
  2. Speak each `vocabTerms` entry in sequence (a short pause between
     terms).
  3. `fetch(nextUrl, {method: 'POST', body: {deck_id, current_passage_id}})`.
     - `{status: 'ok', passage}` → update the on-screen title/level/
       progress-indicator, replace the in-memory transcript/vocab data,
       go back to step 1 for the new passage.
     - `{status: 'done'}` → show a "Session complete" state (e.g. "Nice —
       you listened to 3 passages" + a link back to `/learn/flashcards` or
       to start another passive session) and stop.
- Stop/Exit button: `speechSynthesis.cancel()` + navigate back to
  `/learn/flashcards` (or wherever is least surprising — settle during
  implementation).
- `beforeunload`/`pagehide`: best-effort `speechSynthesis.cancel()` so
  navigating away mid-sentence doesn't leave the browser talking after the
  page is gone.

### 4. New library entry

`lms_listening.libraries.yml`: `listening_passive` (CSS + JS +
`core/drupalSettings`), attached only from `ListeningController::
listenPassive()`.

## Edge Cases

- **Deck has passages in only some levels** (e.g. no Challenge passages
  yet): passive session is shorter than 3 (skips the empty level) rather
  than erroring — same graceful-degradation spirit as `pickForLevel()`
  already has for Active mode.
- **Passage has zero linked cards** (`field_cards` empty): skip the
  vocabulary-reading step for that passage only, go straight to
  auto-advance — don't block the session on it.
- **Deck has zero listening passages at all**: Passive option is
  hidden/disabled in the modal (see Frontend §1); the API also handles this
  gracefully if reached directly (same "nothing matched" response Active
  mode already returns for an empty queue).
- **User closes the tab / navigates away mid-session**: nothing to clean
  up server-side beyond the existing tempstore queue, which is harmless to
  leave stale (same as an abandoned Active-mode queue today).
- **Two passive sessions started back to back**: the mode-namespaced
  tempstore key (Backend §1) means starting a new Passive session simply
  overwrites the old passive queue for that (uid, deck) — same "starting
  over replaces the old queue" behavior Active mode already has.

## Testing Checklist

1. Active Listening (existing): checkboxes, per-level counts, "Start
   practicing", Listen → Questions → Review, "Next passage" — all
   byte-for-byte unchanged. This is the most important regression check
   given how much this plan touches the shared queue service.
2. Passive Listening: deck with passages in all 3 levels → session plays
   exactly 3, in Easy → Normal → Challenge order, each visibly a different
   passage on repeat runs (confirms randomness, not always the same pick).
3. Passive Listening on a deck missing one level's passages → session
   plays the remaining passages, doesn't error.
4. Passive Listening on a deck with zero listening passages → option
   hidden/disabled in the modal; hitting the API directly returns the
   existing empty-queue response shape.
5. Vocabulary read-aloud: a passage with `field_cards` set reads each
   term; a passage with none skips straight to auto-advance.
6. Auto-advance never requires a click; Stop/Exit works mid-passage and
   mid-vocab-list; refreshing the passive page manually still works
   (re-fetches passage 1's data cleanly, doesn't crash on missing state).
7. No `ListeningAttempt` row is created by a passive session (verify via
   direct entity query after a full run) — confirms it's truly unscored.
8. `ListeningPassageStat.field_times_seen` still increments per passage
   seen passively — confirms the "Seen N times" counter stays consistent
   whichever mode is used.
9. Gate check: a deck below 100% Study Now mastery shows the whole
   Listening entry point locked, exactly as today (no separate passive
   gate to verify — it inherits the existing one).
10. No-JS fallback (`ListeningStartForm`) shows the mode toggle and can
    at least reach passage 1's page with JS disabled, even though
    auto-advance won't function.

## Implementation Order

1. `ListeningQueueService`: mode-namespaced key parameter (backward
   compatible default) + `pickRandomForLevel()` + `buildPassiveQueue()`.
2. `ListeningApiController::start()`: `mode` branch; new `passiveNext()`
   method + route.
3. `ListeningController`: factor out `buildTranscriptUnits()` from
   `listen()`; add `listenPassive()` + route + new template.
4. `js/listening-passive.js` + template + library entry.
5. `listening-start-modal.js` + `.css`: mode toggle, wire the "Start
   passive listening" button.
6. `ListeningStartForm`: mode toggle for no-JS parity.
7. Manual test pass through the checklist above, focused first on
   confirming zero regression to Active mode.

## Open Decisions (not yet settled — confirm before/while building)

- Whether the passive session's on-screen transcript should be fully
  hidden (audio-only, most "passive"/eyes-free) or shown read-only,
  optionally highlighted sentence-by-sentence as it's spoken (more
  reassuring for a student who wants to follow along, closer to how Active
  mode's revealed transcript looks).
- Exact pause length between transcript end → first vocab term, and
  between last vocab term → next passage's transcript start (propose
  ~800ms–1200ms, tune after trying it).
- Where "Stop" / "Session complete" should send the student — back to
  `/learn/flashcards`, back to this deck's page, or offer "listen to 3
  more" to chain another passive session immediately.
