# U09 — HTML Output

> Generated HTML documentation output. **Not hand-authored** — regenerated from `U01-U08` markdown files.

## Build Process

U09 chứa static HTML render của user-facing documentation. Source of truth luôn là các `.md` file ở các folder U01-U08.

### Recommended generator

Không lock vào tool cụ thể. Options:

1. **MkDocs + Material theme** (Python):
   ```yaml
   # mkdocs.yml
   site_name: EverLMS User Guide
   docs_dir: .agent/docs/U-Userguide
   site_dir: .agent/docs/U-Userguide/U09-html
   theme:
     name: material
   nav:
     - Overview: U01-user-guide.md
     - Getting Started: U02-getting-started.md
     - Feature Guides: U03-feature-guides/
     - FAQ: U04-faq.md
     - Troubleshooting: U05-troubleshooting.md
     - Tutorials: U06-tutorials/
     - Release Notes: U07-release-notes/
     - Admin Guide: U08-admin-guide.md
   ```
   Build: `mkdocs build -f mkdocs.yml`.

2. **Docusaurus** (Node.js) — nhiều interactive component.
3. **VitePress** (Vue-based, nhanh, gọn).

### Build Command (when configured)

```bash
# Ví dụ với MkDocs
pip install mkdocs-material
mkdocs build
```

### Regeneration Triggers

Rebuild U09 khi:
- Release mới tag → refresh content + version banner.
- U01-U08 có update đáng kể.
- Changelog user-facing cập nhật.

CI recommendation: GitHub Actions / GitLab CI job chạy build → publish lên S3 + CloudFront hoặc GitHub Pages.

## Current Status

**Not built yet.** Build sẽ setup trong Phase 5 (Quality) / Phase 6 (Release).

## Distribution

Sau khi build:
- Zip `U09-html/` → attach vào release artifact.
- Hoặc host tại `docs.everlms.tld` (subdomain).
- Offline bundle: tắt CDN references, bundle CSS/JS local.

## Rules

- HTML files **generated**, không hand-edit.
- Không commit `U09-html/` build output vào git (thêm vào `.gitignore` nếu muốn).
- Nếu commit (cho offline distribution), dùng LFS cho asset lớn (images, fonts).

---

*End of U09-html/README.md*
