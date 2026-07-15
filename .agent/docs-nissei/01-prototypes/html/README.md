# Nissei Compliance Training System — HTML prototype

Static, self-contained prototype. **Open `anonymous/P001-sign-in.html` in a browser** — no server, no build step, works over `file://`.

Basis documents (read these to understand *why* the set looks like this):
- `../roles.txt` — the 7 roles, the branch scope model, the content bible
- `../sitemap.txt` — the 52 pages, per-page notes, the D1–D6 mapping
- `_shared/BUILD-SPEC.md` — the authoring contract for these files

---

## The set is one system, not seven folders

```
P001 role picker ──▶ any role's home ──▶ "Public front-end" ──▶ P004 public home
      ▲                    │                                          │
      └──── "Switch role" ─┴──────────── Sign in ────────────────────┘
```

**Start at `anonymous/P001-sign-in.html`.** Below the sign-in form is a **role picker**: every role is a door into its own prototype. Once inside any role, **My account → Switch role** moves you to another role, and **My account → Public front-end** takes you back out to the public site. You never have to open a file by hand.

Three chromes, derived from the page's role — never declared by the page itself:

| Chrome | Pages | What it looks like |
|---|---|---|
| `auth` | P001, P002 | brand + "Back to website" |
| `front` | P003, P004 | public topbar + nav; a guest sees **Sign in** |
| `front` (signed in) | P010–P020 | the *same* public chrome, plus bell + **My account** mega |
| `manage` | P021–P058 | brand + `Manage · {role}` + **scope pill** + search + tools, and a section nav with hover menus |

The **scope pill** in the manage header states a role's reach in one glance: `NEH-HAN` (one branch) / `ALL BRANCHES` / `GROUP · READ ONLY` / `SYSTEM`. It is the header-level version of the same rule the pages make at content level.

The public surface is deliberately only two pages. This is an internal system: no marketing site, no public catalog, no self-registration. It exists because two audiences legitimately arrive without an account — inspectors verifying a certificate, and employees on their way to sign in.

## Where to start

| If you want to see… | Open |
|---|---|
| **The door into everything** | `anonymous/P001-sign-in.html` |
| The system as a factory worker sees it | `employee/P010-my-dashboard.html` |
| **The core of the whole product** — the training matrix | `hr-hse-central/P036-training-matrix.html` |
| **Branch scope, side by side** | `site-training-manager/P029-employees.html` then `hr-hse-central/P029-employees.html` |
| One training record following a person across branches (D3) | `employee/P017-my-training-record.html` |
| The day-to-day job of a site training manager | `site-training-manager/P032-overdue-worklist.html` |
| Translations pinned to a source version (D6) | `hr-hse-central/P045-translation-workspace.html` |
| What HQ Hamamatsu sees — and cannot do | `hq-viewer/P047-hq-dashboard.html` |
| The audit trail an inspector would ask for | `administrator/P055-audit-log.html` |
| Why a sixth country is data, not a release | `administrator/P052-branch-configuration.html` |

---

## Folders = roles

One folder per role. A folder holds the pages that role works with. Where two roles reach the same page id and **see something different**, the page is built twice — once per folder — and the difference on screen is the deliverable. Where the rendering would be identical, the page is built once and reached by cross-link from the account menu (rule and rationale: `../roles.txt` §5).

| Folder | Role | Scope | Pages |
|---|---|---|---|
| `anonymous/` | Anonymous | none | P001–P003 |
| `employee/` | Employee | own | P010–P020 |
| `instructor/` | Instructor | own + own classes | P021–P027 |
| `site-training-manager/` | Site Training Manager | **one branch** | P021, P022, P027–P034 |
| `hr-hse-central/` | HR/HSE Central | **all branches** | P029–P046 |
| `hq-viewer/` | HQ Viewer | all branches, **read-only** | P033, P047–P049 |
| `administrator/` | Administrator | all + system config | P029, P050–P058 |

`_shared/` is not a role folder — it holds the design system and the page registry.

## Pages built twice (the variants worth comparing)

| Page | Compare | The point |
|---|---|---|
| P029 Employees | `site-training-manager/` vs `hr-hse-central/` vs `administrator/` | branch-locked vs global vs technical view |
| P033 Reports | `site-training-manager/` vs `hr-hse-central/` vs `hq-viewer/` | one branch vs consolidated vs read-only |
| P021 Workspace home | `instructor/` vs `site-training-manager/` | my classes vs my site |
| P022 Training schedule | `instructor/` vs `site-training-manager/` | my sessions vs all sessions + trainer assignment |
| P027 Learner progress | `instructor/` vs `site-training-manager/` | my class vs whole site |
| P030, P031, P032, P034 | `site-training-manager/` vs `hr-hse-central/` | branch scope vs global scope and central-only powers |

---

## How the chrome works

Pages carry no navigation markup. Each page has only:

```html
<div id="site-header" data-shell="app" data-role="{folder}" data-active="{P0NN}"></div>
<main id="main"> … </main>
<div id="site-footer"></div>
```

`_shared/js/shell.js` holds one registry — 53 pages and 6 roles — and renders every header, menu, mega, scope pill, role picker and footer from it. **To add a page, a role, or to move a role's landing page, edit the registry.** Never hand-write a nav link; nothing else needs to change.

There is no sidebar anywhere. Navigation is the top menu, the section menus, the account mega, and `.segmented` pills inside a page. That rule is inherited from the EverLMS prototype.

The navigation *logic* — login as the door to every role, one account mega with grouped icon-and-description columns, and a footer that leads back to the public front-end — follows the reference layouts in `../references/menu/`. The roles, the sections and the pages themselves come from this project's own `roles.txt` and `sitemap.txt`, not from those references.

## Design system

- `_shared/css/base.css` — the EverLMS design system, copied verbatim from `docs/C-Design/C10-prototypes`. Do not edit.
- `_shared/css/nissei.css` — loaded after it. Two jobs: re-theme to the enterprise navy/slate palette (`39-enterprise`, from the `Templates` design library), and add the components a compliance system needs and a course-selling LMS does not: `.branchbar`, `.cstat`, `.matrix`, `.covbar`, `.certbi`, `.wizard`, `.langchip`, `.auditline`, `.roband`, `.annot`, `.ctile`.
- `_shared/img/` — `avatar-*` (people) and `scene-*` only. The consumer course photography from the source prototype was deliberately not copied: this is industrial safety training, not a course marketplace, so courses render as coded `.ctile` tiles.

## Reading the annotations

Dashed blue blocks marked **Prototype note** / **Scope rule made visible** / **Architecture made visible** are design commentary, not product UI. They exist because several decisions here are invisible by nature — a missing button, a language, an ungated option. They cover exactly four cases (`_shared/BUILD-SPEC.md` §6): real language of a surface, option-gated features shown as not connected, a scope rule explaining an absent control, and a deliberate absence such as the audit log having no delete.

## Status

Demo data only — every name is fictional, every number comes from the content bible in `../roles.txt` §6. Nothing here is production data, and the Vietnamese figures for My Tho are placeholders (the source company profile never established them).
