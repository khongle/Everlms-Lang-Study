# Nissei Training System — HTML prototype

Static, self-contained prototype. **Open `html/01-anonymous/P001-sign-in.html` in a browser** — no server, no build step, works over `file://`.

Basis documents (read these to understand *why* the set looks like this):
- `../02-design/roles.txt` — the 8 roles, the two-axis scope model, the content bible
- `../02-design/sitemap.txt` — the 58 pages, per-page notes, the D1–D6 mapping
- `BUILD-SPEC.md` — the authoring contract for these files

---

## The set is one system, not seven folders

```
P001 role picker ──▶ any role's home ──▶ "Public front-end" ──▶ P004 public home
      ▲                    │                                          │
      └─── "Switch account" ┴──────────── Sign in ────────────────────┘
```

**Start at `html/01-anonymous/P001-sign-in.html`.** Below the sign-in form is a **role picker**: every role is a door into its own prototype. Once inside any role, **My account → Switch account** moves you to another role, and **My account → Public front-end** takes you back out to the public site. You never have to open a file by hand.

One chrome, two tiers, every shell — public, member and back-office share the same markup so there is no seam between them:

```
tier 1  .topbar        context (left)          [scope] [bell] [LANGUAGE] [auth]  (right)
tier 2  .site-header   LOGO (left)                            main menu  (right)
```

Three invariants make it consistent everywhere:

1. **The logo is only ever tier 2, left** — and clicking it goes to *your* home: a guest lands on the public page, a member on their Overview, staff on their role's home. The brand block itself follows one rule everywhere: **line 1 is the company** (`Nissei Electric`, constant, so the logo is an anchor) and **line 2 is context** (`Training system`, or `Manage · {role}` in the back-office). Line 2 never restates line 1.
2. **The main menu is only ever tier 2, right** — and it is the same `.nav__links` component in the public site and the back-office. The only difference is that a multi-page back-office section also opens a hover panel.
3. **Tier 1 carries no destinations, only identity** — scope, notifications, language, and the auth control, with **language always immediately beside Sign in / My account**. "Verify a certificate" is a destination, so it lives in the menu and never beside Sign in.

| Shell | Pages | Tier 1 context | Tier 2 menu |
|---|---|---|---|
| `auth` | P001, P002 | — | Back to website |
| `front` guest | P003, P004 | announcement | Home · Verify certificate |
| `front` member | P010–P020 | branch · job title | the member's own workspace |
| `manage` | P021–P063 | global search | the role's sections |

The **scope pill** sits beside the identity because scope *is* identity: `NEH-HAN` (one branch) / `ALL BRANCHES` / `GROUP · READ ONLY` / `PLATFORM · BREAK-GLASS`.

The public surface is deliberately only two pages. This is an internal system: no marketing site, no public catalog, no self-registration. It exists because two audiences legitimately arrive without an account — inspectors verifying a certificate, and employees on their way to sign in.

## Where to start

| If you want to see… | Open |
|---|---|
| **The door into everything** | `html/01-anonymous/P001-sign-in.html` |
| The system as a factory worker sees it | `html/02-employee/P010-my-dashboard.html` |
| **The core of the whole product** — the training matrix | `html/05-hr-hse-central/P036-training-matrix.html` |
| **Branch scope, side by side** | `html/04-site-training-manager/P029-employees.html` then `html/05-hr-hse-central/P029-employees.html` |
| One training record following a person across branches (D3) | `html/02-employee/P017-my-training-record.html` |
| The day-to-day job of a site training manager | `html/04-site-training-manager/P032-overdue-worklist.html` |
| Translations pinned to a source version (D6) | `html/05-hr-hse-central/P045-translation-workspace.html` |
| What HQ Hamamatsu sees — and cannot do | `html/06-hq-viewer/P047-hq-dashboard.html` |
| The audit trail an inspector would ask for | `html/07-nissei-admin/P055-audit-log.html` |
| Why a sixth country is data, not a release | `html/07-nissei-admin/P052-branch-configuration.html` |
| The fence that keeps the deployment upgradable | `html/08-administrator/P061-site-structure.html` |
| Where core-vs-customer drift gets caught | `html/08-administrator/P063-config-sync.html` |

---

## Folders = roles

One folder per role. A folder holds the pages that role works with. Where two roles reach the same page id and **see something different**, the page is built twice — once per folder — and the difference on screen is the deliverable. Where the rendering would be identical, the page is built once and reached by cross-link from the account menu (rule and rationale: `../02-design/roles.txt` §5).

| Folder | Role (`data-role`) | Sees | Can change | Pages |
|---|---|---|---|---|
| `01-anonymous/` | `anonymous` | public only | nothing | P001–P004 |
| `02-employee/` | `employee` | own record | own preferences | P010–P020 |
| `03-instructor/` | `instructor` | own classes | own courses | P021–P027 |
| `04-site-training-manager/` | `site-training-manager` | **one branch** | that branch's operations | P021, P022, P027–P034 |
| `05-hr-hse-central/` | `hr-hse-central` | **all branches** | the training standard | P029–P046 |
| `06-hq-viewer/` | `hq-viewer` | all branches | **nothing (read-only)** | P033, P047–P049 |
| `07-nissei-admin/` | `org_admin` | all branches | **data + delta** | P029, P050–P056, P058, P059 |
| `08-administrator/` | `administrator` | the platform | **core** | P055–P057, P060–P063 |

### Two admins, and why

The roles are scoped along **two** axes, not one:

- **Branch scope** — whose data you see: `own` / `branch` / `global`. This is why `site-training-manager` and `hr-hse-central` are separate roles: same job, different reach.
- **Layer scope** — what part of the system you may change: `data` (business records) / `delta` (this customer's configuration, branding, content) / `core` (the product's structure: entities, fields, views, modules).

`org_admin` and `administrator` have the *same* data reach and *different* layers:

- **`org_admin` — "Nissei Administrator"** (the customer's IT): accounts, roles, branch config, languages, integrations, editorial wording, audit, retention. Everything Nissei needs daily, and it never needs to ask the platform for routine work.
- **`administrator` — "Platform Administrator"** (the platform team, break-glass): structure, modules, releases, config sync. Note it has **no `full` on any business surface** — it is a technical superuser, not a business one.

The fence exists because the product ships as **CORE / DELTA / DATA** (`strategy-final.txt`, principle E4). If the customer could edit structure, this deployment would drift away from the product core and stop being upgradable. `html/08-administrator/P061-site-structure.html` is that fence, `P063-config-sync.html` is where drift gets caught, and `P062-modules-updates.html` is the only channel structure changes travel through.

The role key stays generic (`org_admin`) while the label is Nissei's, so the same core role serves the next customer with only a label change.

`_shared/` is not a role folder — it holds the design system and the page registry.

## Pages built twice (the variants worth comparing)

| Page | Compare | The point |
|---|---|---|
| P029 Employees | `04-site-training-manager/` vs `05-hr-hse-central/` vs `07-nissei-admin/` | branch-locked vs global vs accounts/technical view |
| P033 Reports | `04-site-training-manager/` vs `05-hr-hse-central/` vs `06-hq-viewer/` | one branch vs consolidated vs read-only |
| P021 Workspace home | `03-instructor/` vs `04-site-training-manager/` | my classes vs my site |
| P022 Training schedule | `03-instructor/` vs `04-site-training-manager/` | my sessions vs all sessions + trainer assignment |
| P027 Learner progress | `03-instructor/` vs `04-site-training-manager/` | my class vs whole site |
| P030, P031, P032, P034 | `04-site-training-manager/` vs `05-hr-hse-central/` | branch scope vs global scope and central-only powers |
| P055 Audit log | `07-nissei-admin/` vs `08-administrator/` | business events vs full trail incl. config/deploy |
| P056 Switch user | `07-nissei-admin/` vs `08-administrator/` | support impersonation vs time-boxed break-glass |

---

## How the chrome works

Pages carry no navigation markup. Each page has only:

```html
<div id="site-header" data-shell="app" data-role="{folder}" data-active="{P0NN}"></div>
<main id="main"> … </main>
<div id="site-footer"></div>
```

`html/_shared/js/shell.js` holds one registry — 58 pages and 7 signed-in roles — and renders every header, menu, mega, scope pill, role picker and footer from it. **To add a page, a role, or to move a role's landing page, edit the registry.** Never hand-write a nav link; nothing else needs to change.

There is no sidebar anywhere. Navigation is the top menu, the section menus, the account mega, and `.segmented` pills inside a page. That rule is inherited from the EverLMS prototype.

The navigation *logic* — login as the door to every role, one account mega with grouped icon-and-description columns, and a footer that leads back to the public front-end — follows the reference layouts in `../references/menu/`. The roles, the sections and the pages themselves come from this project's own `roles.txt` and `sitemap.txt`, not from those references.

## Design system

- `html/_shared/css/base.css` — the EverLMS design system, copied verbatim from `docs/C-Design/C10-prototypes`. Do not edit.
- `html/_shared/css/nissei.css` — loaded after it. Two jobs: re-theme to the enterprise navy/slate palette (`39-enterprise`, from the `Templates` design library), and add the components a compliance system needs and a course-selling LMS does not: `.branchbar`, `.cstat`, `.matrix`, `.covbar`, `.certbi`, `.wizard`, `.langchip`, `.auditline`, `.ctile`.
- `html/_shared/img/` — `avatar-*` (people) and `scene-*` only. The consumer course photography from the source prototype was deliberately not copied: this is industrial safety training, not a course marketplace, so courses render as coded `.ctile` tiles.

## No commentary inside the pages — by request

The pages carry **no design annotations, no prototype notes, no meta copy**: what a customer opens is the product speaking in its own voice, nothing else. Every rationale that used to be annotated in-page now lives where documentation belongs:

- why a control is absent (HQ read-only, no branch filter for a site manager, no delete on the audit log) → `../02-design/roles.txt` (scope model) and `../02-design/decisions.txt`
- option-gated features (SSO, HRIS, offline) → `../01-requirements/requirements.txt` — the Integrations page simply shows them as *Not connected*, which is the true product state
- real language per persona (HQ = JA, CN office = ZH, factory floor = VI) → `../02-design/roles.txt` §6 and `../02-design/mobile.txt`; the catalog page's EN↔ZH language switch in the account menu is a working link, so the mechanism is demonstrated, not explained

If you are authoring new pages: do **not** add explanatory blocks to the customer-facing set. Write the rationale into `02-design/` instead.

## Status

Demo data only — every name is fictional, every number comes from the content bible in `../02-design/roles.txt` §6. Nothing here is production data, and the Vietnamese figures for My Tho are placeholders (the source company profile never established them).
