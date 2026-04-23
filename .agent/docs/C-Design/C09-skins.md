# C09 — Skins (Design System)

## Design Tokens

### Theme
- **Style:** Dark premium theme
- **Framework:** Bootstrap 5.3.3
- **SCSS Files:** 16 files

### Colors (Dark Theme)
| Token | Value | Usage |
|-------|-------|-------|
| `$primary` | Bootstrap primary | CTAs, links, active states |
| `$bg-dark` | Dark background | Page background |
| `$surface` | Slightly lighter dark | Cards, panels |
| `$text-primary` | Light text | Body text |
| `$text-secondary` | Muted text | Secondary info |
| `$success` | Green | Pass, complete, success states |
| `$danger` | Red | Error, fail states |
| `$warning` | Yellow/Orange | Warnings, pending states |

### Typography
| Token | Value |
|-------|-------|
| Font family | System font stack (Bootstrap default) |
| Base size | 16px |
| Heading scale | Bootstrap default scale |

### Breakpoints
| Breakpoint | Width | Usage |
|-----------|-------|-------|
| `xs` | <576px | Mobile |
| `sm` | ≥576px | Mobile landscape |
| `md` | ≥768px | Tablet |
| `lg` | ≥992px | Desktop |
| `xl` | ≥1200px | Large desktop |
| `xxl` | ≥1400px | Extra large |

### SCSS Architecture

```
lms_theme/
├── scss/
│   ├── _variables.scss      # Custom variables
│   ├── _mixins.scss         # Custom mixins
│   ├── _base.scss           # Base styles
│   ├── _layout.scss         # Layout grid
│   ├── _typography.scss     # Typography
│   ├── _buttons.scss        # Button styles
│   ├── _forms.scss          # Form elements
│   ├── _cards.scss          # Card components
│   ├── _navigation.scss     # Nav/menu
│   ├── _homepage.scss       # Homepage specific
│   ├── _catalog.scss        # Catalog page
│   ├── _course-detail.scss  # Course detail
│   ├── _lesson.scss         # Lesson view
│   ├── _management.scss     # Management pages
│   ├── _dashboard.scss      # Dashboard
│   └── style.scss           # Main entry (imports all)
```

### Component Patterns
- **Card:** Course card, quiz card, certificate card
- **Navigation:** Top navbar (responsive), sidebar (management)
- **Forms:** Drupal Form API + Bootstrap form styles
- **Tables:** Bootstrap tables with sorting
- **Modals:** Bootstrap modals for confirmations
- **Alerts:** Bootstrap alerts for messages

---

*End of C09-skins.md*
