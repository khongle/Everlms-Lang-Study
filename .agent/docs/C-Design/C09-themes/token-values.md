# C09-themes / token-values - Literal Token Values

**Source:** `web/themes/custom/lms_theme/scss/_variables.scss` (as-built, 2026-07-11). Đây là nơi DUY NHẤT trong C09 chứa giá trị hex/px literal — [`_index.md`](_index.md) chỉ mô tả token bằng tên.

## Color palette
| Token | Value | Usage |
| ----- | ----- | ----- |
| `$color-bg-primary` | `#0d1117` | nền trang chính (dark) |
| `$color-bg-secondary` | `#161b22` | nền khối/card |
| `$color-bg-tertiary` | `#21262d` | nền lớp phụ (input, hover) |
| `$color-bg-card` | `rgba(22, 27, 34, 0.85)` | nền card (glass) |
| `$color-bg-glass` | `rgba(255, 255, 255, 0.05)` | lớp kính (glassmorphism) |
| `$color-border` | `rgba(255, 255, 255, 0.1)` | viền mặc định |
| `$color-border-hover` | `rgba(255, 255, 255, 0.2)` | viền khi hover |
| `$color-text-primary` | `#e6edf3` | chữ chính |
| `$color-text-secondary` | `#8b949e` | chữ phụ |
| `$color-text-muted` | `#6e7681` | chữ mờ (meta, caption) |
| `$color-text-link` | `#58a6ff` | link |
| `$color-accent-primary` (`$color-primary`) | `#667eea` | brand primary |
| `$color-accent-secondary` (`$color-secondary`) | `#764ba2` | brand secondary |
| `$color-accent-success` (`$color-success`) | `#43e97b` | trạng thái success |
| `$color-accent-danger` (`$color-danger`) | `#f5576c` | trạng thái error |
| `$color-accent-warning` (`$color-warning`) | `#fee140` | trạng thái warning |
| `$color-accent-info` (`$color-info`) | `#4facfe` | trạng thái info |

## Gradients
| Token | Value |
| ----- | ----- |
| `$gradient-primary` | `linear-gradient(135deg, #667eea 0%, #764ba2 100%)` |
| `$gradient-secondary` | `linear-gradient(135deg, #f093fb 0%, #f5576c 100%)` |
| `$gradient-success` | `linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)` |
| `$gradient-info` | `linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)` |
| `$gradient-warning` | `linear-gradient(135deg, #fa709a 0%, #fee140 100%)` |

## Typography
| Token | Value |
| ----- | ----- |
| `$font-family-base` | `'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif` |
| `$font-size-xs` | `0.75rem` (12px) |
| `$font-size-sm` | `0.8125rem` (13px) |
| `$font-size-base` | `0.9375rem` (15px) |
| `$font-size-lg` | `1.125rem` (18px) |
| `$font-size-h4` | `1.125rem` (18px) |
| `$font-size-h3` | `1.375rem` (22px) |
| `$font-size-h2` | `1.75rem` (28px) |
| `$font-size-h1` | `2.25rem` (36px) |
| `$font-weight-light` … `$font-weight-extrabold` | `300 / 400 / 500 / 600 / 700 / 800` |
| `$line-height-base` | `1.6` |
| `$line-height-heading` | `1.2` |

## Spacing / radius / shadow
| Token | Value |
| ----- | ----- |
| `$spacer-xs` | `0.25rem` |
| `$spacer-sm` | `0.5rem` |
| `$spacer-md` (`$spacer`) | `1rem` |
| `$spacer-lg` | `1.5rem` |
| `$spacer-xl` | `2rem` |
| `$spacer-xxl` | `3rem` |
| `$radius-sm` | `0.375rem` |
| `$radius-md` | `0.5rem` |
| `$radius-lg` | `0.75rem` |
| `$radius-xl` | `1rem` |
| `$radius-full` | `50%` |
| `$shadow-sm` | `0 1px 3px rgba(0, 0, 0, 0.3)` |
| `$shadow-md` | `0 4px 12px rgba(0, 0, 0, 0.4)` |
| `$shadow-lg` | `0 8px 24px rgba(0, 0, 0, 0.5)` |
| `$shadow-glass` | `0 8px 32px rgba(0, 0, 0, 0.37)` |
| `$glass-blur` | `blur(10px)` |

## Motion
| Token | Value |
| ----- | ----- |
| `$transition-fast` | `0.15s ease` |
| `$transition-normal` | `0.25s ease` |
| `$transition-slow` | `0.4s ease` |

## Layout constants
| Token | Value |
| ----- | ----- |
| `$container-max-width` | `1320px` |
| `$container-padding` | `1.5rem` |
| `$navbar-height` | `64px` |
| `$navbar-bg` | `rgba(13, 17, 23, 0.95)` |
| `$z-navbar` / `$z-dropdown` / `$z-modal` / `$z-tooltip` | `1030 / 1040 / 1050 / 1060` |

## Breakpoint tiers
SCSS declaration (`_variables.scss`): `$breakpoint-sm: 576px; $breakpoint-md: 768px; $breakpoint-lg: 992px; $breakpoint-xl: 1200px; $breakpoint-xxl: 1400px;` (Bootstrap 5-compatible). Ngưỡng `max-width` thực tế dùng trên mọi C11 page spec (`## Responsive behavior`) lệch `.98px` theo quy ước CSS `max-width` chẵn — 4-tier áp dụng site-wide:

| Tier | Range | SCSS var gần nhất |
| ---- | ----- | ------------------ |
| mobile | ≤ 639.98px | `$breakpoint-sm` (576px) |
| tablet | 640px – 991.98px | `$breakpoint-md` (768px) |
| desktop | 992px – 1199.98px | `$breakpoint-lg` (992px) |
| wide | ≥ 1200px | `$breakpoint-xl` (1200px) / `$breakpoint-xxl` (1400px) |

> Skin re-map: 15 homepage skin (`home-emerald`, `home-midnight`, `home-ocean`, `home-rose`, `home-sunset`, …) remap `$color-accent-*`/`$gradient-*` per skin qua theme settings — bảng trên là skin mặc định (dark, blue/purple). Giá trị per-skin không liệt kê ở đây (theme settings runtime, không phải design token cố định).
