# BUILD-SPEC — Nissei Compliance Training System prototype

**Read before authoring any page.** Adapted from `docs/C-Design/C10-prototypes/html/_shared/BUILD-SPEC.md`.
Basis documents: `../../roles.txt` (roles, scope model, content bible) and `../../sitemap.txt` (page registry, per-page notes). Read both first — this file only covers *how to build*, not *what to build*.

---

## 0. Non-negotiables

1. **Never hand-write chrome.** Header/footer/nav are injected by `js/shell.js` from its page registry. A page contains only the placeholders in §1.
2. **No sidebar navigation, anywhere.** Navigation = top menu + account mega menu + `.segmented` pills inside the page. The only allowed vertical rails are *business content* lists (lesson rail on P014, question rail on P015). This is inherited from the EverLMS prototype and is a hard rule.
3. **Tokens only.** Never invent a colour, spacing, radius or font size. Everything resolves to a CSS variable from `css/base.css` (scales) or `css/nissei.css` (theme + compliance components). Inline `style` is allowed only when its values are tokens.
4. **Relative paths only.** Assets `../_shared/…`. Cross-page links `../<role-folder>/<file>` — every page sits exactly one level under `html/`. Get the target folder from the registry in `shell.js`, never guess.
5. **No emojis. No dash-bullets. No lorem ipsum.** Icons are inline SVG (24×24, `stroke="currentColor"`, `fill="none"`, `stroke-width="2"`). Copy is real, professional, and English (see §5).
6. **Real data only.** Every number, name, branch code and course code comes from the content bible in `roles.txt` §6. Do not invent a sixth branch or a fourteenth course.
7. **Accessible.** Semantic landmarks, `alt` on every image, `<label>` on every input, `scope` on table headers, WCAG AA contrast. Status is never colour alone — always colour + label (see `.cstat`).
8. **Responsive** at Bootstrap 5 breakpoints. Wide tables go inside `.wtable__wrap` / `.matrixwrap` so the page body never scrolls horizontally.

---

## 1. Page skeleton (verbatim)

```html
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{Page title} · Nissei Training</title>
<meta name="description" content="{one line}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../_shared/css/base.css">
<link rel="stylesheet" href="../_shared/css/nissei.css">
</head>
<body>
<div id="site-header" data-shell="app" data-role="{role-key}" data-active="{P0NN}"></div>
<main id="main">
  … page sections …
</main>
<div id="site-footer"></div>
<script src="../_shared/js/shell.js"></script>
<script src="../_shared/js/app.js"></script>
</body>
</html>
```

- `nissei.css` **must** load after `base.css` — it re-themes it.
- `data-active` = the page's own id; the shell marks the matching menu item `aria-current="page"`.

### Which chrome a page gets — it is derived, not declared

There are three chromes, and **a page never picks one**. `shell.js` derives it from `data-role`, so adding a page means editing the registry, not the chrome:

| Page kind | Attributes | Chrome rendered |
|---|---|---|
| Sign-in, password reset (P001, P002) | `data-shell="auth"` | brand + "Back to website" only |
| Public pages (P003, P004) | `data-shell="front"` | public topbar + nav; guest sees **Sign in** |
| Employee's own area (P010–P020) | `data-shell="app" data-role="employee"` | the **same public chrome**, but signed in: bell + **My account** mega |
| Staff pages (P021–P058) | `data-shell="app" data-role="{staff-role}"` | **manage** chrome: brand + `Manage · {role}` + **scope pill** + search + tools, and a section nav with hover megas |

### Role keys vs folder names — do not confuse them

`data-role` carries the **role key**, never the folder name:

| Role key (`data-role`) | Folder on disk |
|---|---|
| `anonymous` | `01-anonymous/` |
| `employee` | `02-employee/` |
| `instructor` | `03-instructor/` |
| `site-training-manager` | `04-site-training-manager/` |
| `hr-hse-central` | `05-hr-hse-central/` |
| `hq-viewer` | `06-hq-viewer/` |
| `administrator` | `07-administrator/` |

Folders are numbered so they list in the order the system is actually walked rather than alphabetically. The key stays unnumbered, and `shell.js` maps key to folder in one place (`DIR`). So: a variant's `data-role` must be **the role whose folder it sits in** (for `05-hr-hse-central/P029-employees.html` that is `hr-hse-central`), and renumbering folders later is a one-line change in `DIR` plus the hrefs written into page bodies.

In page bodies, cross-folder links use the numbered folder: `../05-hr-hse-central/P036-training-matrix.html`. Take the exact path from the `shell.js` registry; never guess it.

### The system is a loop, not seven folders

This is the point of the whole set, and the reason the chrome is centralised:

```
P001 role picker ──▶ any role's home ──▶ "Public front-end" ──▶ P004 public home
      ▲                    │                                          │
      └──── "Switch role" ─┴──────────── Sign in ────────────────────┘
```

- **P001 carries the role picker** — every role is a door. It renders itself from the `shell.js` registry, so it can never list a role or a landing page that does not exist.
- **Every signed-in role can reach the public front-end**, from the account mega footer and from the home icon in the manage header.
- **Every signed-in role can Switch role**, from the account mega footer and from the page footer.

If you add a role or move a role's home page, edit `ROLES` in `shell.js`. Nothing else needs to change.

### The scope pill

The manage header carries a pill stating that role's reach: `NEV-HCM` / `NEH-HAN` (branch-scoped), `ALL BRANCHES` (global), `GROUP · READ ONLY`, `SYSTEM`. It is the header-level statement of the same rule §3 makes at page level. It is rendered from `ROLES[role].scope` — do not hand-write it into a page.

---

## 2. Page body pattern

Workspace / central / admin / HQ pages:

```html
<main id="main">
  <section class="workhero"><div class="container">
    <nav class="crumb" aria-label="Breadcrumb">…</nav>
    <!-- branch context: pick ONE, see §3 -->
    <div class="branchbar">…</div>
    <div class="pagehead">
      <div class="pagehead__t"><h1>Employees</h1><p class="lede">…</p></div>
      <div class="pagehead__actions"><a class="btn btn--brand">…</a></div>
    </div>
  </div></section>
  <section class="section"><div class="container">
    <nav class="segmented">…filters…</nav>
    <div class="tablecard">…</div>
  </div></section>
</main>
```

Employee app pages: same, minus `.crumb`, minus `.branchbar` (an employee has exactly one branch and no scope choice to make).

---

## 3. The branch scope rule — the most important thing in this prototype

The product's central idea is that **every screen is scoped to a branch, and the control you get depends on your scope**. Prototype it literally:

| Role | Branch UI | Markup |
|---|---|---|
| `site-training-manager` | fixed label, **no switcher, no branch column, no branch filter** | `.branchbar` + `.branchtag--lock` |
| `hr-hse-central`, `administrator` | switcher + `Branch` column + branch filter in `.segmented` | `.branchbar` + `.branchbar__sel` |
| `hq-viewer` | switcher, but **read-only** — no action buttons at all | `.branchbar__sel` + `.roband` |
| `employee`, `instructor` | none | — |

Fixed (site manager):
```html
<div class="branchbar">
  <span class="branchbar__lb">Branch</span>
  <span class="branchtag branchtag--lock">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    NEH-HAN · Nissei Electric Hanoi
  </span>
  <span class="branchbar__scope">Your role is scoped to one branch</span>
</div>
```

Switchable (central/admin/HQ):
```html
<div class="branchbar">
  <span class="branchbar__lb">Branch</span>
  <button class="branchbar__sel">All branches (5)
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
  </button>
  <span class="branchbar__scope">5,842 employees · VN 5,800 · CN 42</span>
</div>
```

When you build the same page id for two roles, the *diff between the two files must be visible on screen* — different branch UI, different columns, different actions. If two variants would look identical, the page should not be a variant at all.

---

## 4. Component vocabulary

**From `base.css`** (the EverLMS design system, reuse as-is):
`.container` · `.section` · `.workhero` · `.crumb` · `.pagehead` `.pagehead__t` `.pagehead__actions` · `.segmented` (`a.is-active`) · `.tablecard` · `.wtable` `.wtable__wrap` `.cell-user` `.rowbtns` · `.status` (`--success --warning --error --info --muted --dot`) · `.kpis` `.kpi` · `.editor` `.editor__aside` · `.panel` (`--soft`) · `.formgrid` (`--2`) · `.field` · `.hint` · `.switchrow` `.swtoggle` (`.is-on`) · `.dropzone` · `.barchart` `.chartcard` · `.authwrap` `.authwrap__aside` `.authwrap__form` · `.alert` (`--info --success --warning --error`) · `.btn` (`--brand --primary --ghost --white --sm --lg --block`) · `.badge` · `.chip` · `.card` (`--soft --pad-lg`) · `.grid` (`--2 --3 --4 --auto`) · `.progress` · `.ring` (`style="--p:NN"`) · `.tabs` `.tabpane` (`[data-tabs]/[data-tab]/[data-pane]`) · `.pager` · `.timeline` · `.note` · `.dtable` · `.certcard` · `.quizwrap` `.opts` `.opt` · `.player` · utilities `.stack .flex .items-center .justify-between .gap-1/2/3 .text-sm .text-muted .fw-700 .muted .lede .eyebrow .hidden`

**From `nissei.css`** (new — this product only):
`.branchbar` `.branchtag` · `.cstat` (`--ok --due --over --prog --none`) · `.duemeter` `.dueline` · `.matrixwrap` `.matrix` `.mcell` `.cycle` `.grouptag` · `.covrow` `.covbar` · `.certvalid` `.certno` `.certbi` · `.wizard` `.wstep` (`.is-done .is-active`) `.errrow` `.errmark` `.fixcell` · `.langgrid` `.langchip` (`--live --stale --missing --src`) `.vertag` · `.auditwrap` `.auditline` `.ev--*` `.immutable` · `.roband` `.reasonbox` `.impbanner` · `.annot` · `.ctile` (`--saf --fir --qms --env --off --cmp --cyb --lg`) `.cell-course` · `.acctmega__foot` `.acct__btn` · `.sr-only` · `.codecell` `.num`

> `.sr-only` lives in `nissei.css`, not `base.css` — the base design system never shipped one. Every data table here carries a `<caption class="sr-only">` and dense toolbars use `<label class="sr-only">`; without that rule they render visibly. Verify a class exists before using it (`grep -n '\.classname' _shared/css/*.css`) rather than assuming Bootstrap-style utilities are present.

**Compliance status — always this mapping** (`roles.txt` §6):
`Compliant → .cstat--ok` · `Due soon → .cstat--due` · `Overdue → .cstat--over` · `In progress → .cstat--prog` · `Not started → .cstat--none`

---

## 5. Copy & data

- **UI language: English.** Real language per persona is recorded with `.annot`, not by translating the page — except the two deliberate proof-of-mechanism files noted in `sitemap.txt` (`P012` `.zh` variant).
- All names, codes, branches, courses, KPIs: **content bible, `roles.txt` §6**. Reuse verbatim so the set reads as one system.
- Course codes drive the tile colour: `SAF-*` → `.ctile--saf`, `FIR-*` → `.ctile--fir`, `QMS-*` → `.ctile--qms`, `ENV-*` → `.ctile--env`, `OFF-*` → `.ctile--off`, `CMP-*` → `.ctile--cmp`, `CYB-*` → `.ctile--cyb`.
- **No stock course photography.** This is industrial compliance training, not a course marketplace — use `.ctile` coded tiles. `_shared/img/` holds only `avatar-*` (people) and `scene-*` (neutral scenes); anything else was deliberately not copied over.
- Dates: `DD MMM YYYY` (`14 Mar 2026`). Numbers: `.num` for tabular alignment.
- Today's date in all demo data: **15 Jul 2026**.

---

## 6. Annotations

This prototype adds one thing the EverLMS prototype did not have: a visible annotation block, because several design decisions are invisible by nature (a missing button, a language, an optional feature).

```html
<div class="annot"><b>Prototype note</b>HQ reporting is Japanese in production; shown in English per the prototype language decision.</div>
```

Use `.annot` for exactly these cases, and nowhere else:
1. **Real language** of a surface differs from the prototype (HQ = JA, CN office = ZH, factory floor = VI).
2. **Option-gated feature** — SSO / HRIS sync appear as *not connected*; never draw them as working.
3. **Scope rule made visible** — explain why a control is absent (HQ read-only, site manager has no branch filter).
4. **Deliberate absence** — e.g. no "delete" on the audit log because the log is immutable.

Never use `.annot` to apologise for unfinished markup.

---

## 7. Page states

One file per page, showing the **default/primary state richly**. Other states are inlined where they read naturally, using existing components — no separate files, no hidden toggles:

- `P041`/`P042` — validation errors (`.errrow`, `.fixcell`) *are* the main content; the reconciliation screen is the point of the page.
- `P032` — empty state ("no overdue training") is a **good** outcome; render it as a proper success card, not a grey void.
- `P029` (site variant) — the absent branch filter demonstrates permission scope.
- `P047` — absent action buttons demonstrate read-only.
- `P015` — inline validation when no option is selected.

---

## 8. Reference pages

Build these first, mimic them after:
- **Auth** → `01-anonymous/P001-sign-in.html`
- **Employee app** → `02-employee/P010-my-dashboard.html`
- **Dense admin table + scope** → `04-site-training-manager/P029-employees.html`
- **Complex central tool** → `05-hr-hse-central/P036-training-matrix.html`
