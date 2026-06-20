# U02 — Getting Started

> Installation, first login, và các bước đầu tiên cho ba persona: Developer (local DDEV), End-user (learner/instructor), Admin.

## Prerequisites

| Requirement | Minimum | Notes |
|-------------|---------|-------|
| Browser | Chrome 120+, Firefox 115+, Safari 17+ | Mobile responsive OK từ iOS/Android hiện hành |
| Network | 5 Mbps+ | Video lesson cần ≥ 3 Mbps |
| DDEV (dev only) | 1.23+ | Docker Desktop / Colima |
| PHP (prod only) | 8.3+ | Với extensions: gd, intl, mbstring, opcache, pdo_mysql, zip |
| MariaDB (prod only) | 10.11+ | hoặc MySQL 8.0+ |
| Composer | 2.7+ | Dev và prod |

## Installation (Developer — DDEV)

```bash
# Clone repo
git clone <repo-url> everlms && cd everlms

# Start DDEV
ddev start

# Install dependencies
ddev composer install

# Import DB (nếu có dump), hoặc fresh install
ddev exec drush si standard -y   # or: ddev import-db < dump.sql.gz

# Enable custom modules
ddev exec drush en -y lms_base lms_course lms_chapter lms_lesson lms_quiz \
  lms_question lms_progress lms_order lms_membership lms_payment lms_certificate \
  lms_forum lms_notification lms_rating lms_note lms_faq lms_badge lms_ai \
  lms_analytics lms_zoom lms_bundle lms_wishlist lms_blog lms_stripe lms_paypal \
  lms_content_tools open_switcher

# Set default theme
ddev exec drush theme:enable lms_theme -y
ddev exec drush config:set system.theme default lms_theme -y

# Clear cache
ddev exec drush cr

# Launch
ddev launch
```

Xem `A-Project-Information/A01-CLAUDE.md` §Commands cho lệnh build/test/lint chuẩn.

## Installation (Production)

Xem [U08 — Admin Guide](U08-admin-guide.md) §Production Configuration. DDEV **không** dùng cho production.

## Configuration (Sau cài đặt)

| Setting | Default | Description | Required |
|---------|---------|-------------|----------|
| `system.site.name` | EverLMS | Site name hiển thị header | Yes |
| `lms_base.settings.default_currency` | VND | Tiền tệ mặc định cho Order | Yes |
| `lms_payment.stripe.publishable_key` | (empty) | Stripe pub key | Yes for Stripe |
| `lms_payment.stripe.secret_key` | (empty) | Stripe secret (config override hoặc env) | Yes for Stripe |
| `lms_payment.paypal.client_id` | (empty) | PayPal client id | Yes for PayPal |
| `lms_ai.default_provider` | openai | Provider mặc định cho AI course builder | If AI in use |
| `lms_ai.openai.api_key` | (empty) | OpenAI API key | If AI in use |
| `lms_zoom.jwt_key` / `.secret` | (empty) | Zoom JWT credentials | If Zoom in use |

> **Secrets:** Tuyệt đối **không** commit API key vào repo. Dùng `settings.local.php` (dev) hoặc environment variables + `$config[...]` override (prod). Xem U08 §Environment Variables.

## First Steps — Learner

1. Mở `/` → trang chủ hiển thị Featured Courses.
2. Click **Register** → điền email/password → nhận email confirm (xem Drupal mail log nếu chưa setup SMTP).
3. Sau khi đăng nhập, mở **/courses** để duyệt danh sách khóa học.
4. Click vào một khóa → **Enroll** (nếu miễn phí) hoặc **Add to Cart** (nếu có phí).
5. Vào **My Learning** từ user menu để tiếp tục học bài.

## First Steps — Instructor

1. Admin phải gán role **instructor** cho user (User management → Edit user → Roles).
2. Đăng nhập → menu user có thêm **Instructor Dashboard**.
3. **Create Course** → điền tên, category, summary → save draft.
4. Add **Chapter** → Add **Lesson** (chọn 1 trong 9 type: video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed).
5. **Publish** khi đã hoàn thành.

Xem [U06/tutorial-01-create-first-course.md](U06-tutorials/tutorial-01-create-first-course.md).

## First Steps — Admin

1. Đăng nhập bằng user 1 (tạo lúc `drush si`).
2. **/admin/config/lms** → cấu hình core (payment, AI, zoom).
3. **/admin/people/roles** → review 7 role mặc định (Admin, Manager, Instructor, Content Editor, Student, Authenticated, Anonymous).
4. **/admin/structure/taxonomy** → review 8 taxonomy vocabularies (Category, Course Level, Topic, Skill, Language, Tag, Certificate Type, Badge Level).
5. **/admin/lms/dashboard** → kiểm tra Analytics Dashboard.

Đầy đủ: [U08 — Admin Guide](U08-admin-guide.md).

## Verification (confirm install thành công)

```bash
# Dev
ddev exec drush status
ddev exec drush core:requirements --severity=2   # phải sạch (≤ Warning)

# Smoke test từng phân hệ
curl -I http://everlms.ddev.site/                # 200
curl -I http://everlms.ddev.site/courses         # 200
curl -I http://everlms.ddev.site/user/login      # 200
```

Trong browser:
- `/` render đủ section (hero, featured courses, testimonial).
- `/user/register` form load, không lỗi.
- Đăng nhập admin → `/admin` không 500.

## Upgrading

### From v0.1.0 → v0.2.0 (planned)
- Run `ddev composer update`.
- Run `ddev exec drush updb -y`.
- Run `ddev exec drush cim -y` để import config changes.
- Run `ddev exec drush cr`.

Xem `F-Release/F03-changelog.md` cho technical changelog và [U07 Release Notes](U07-release-notes/) cho user-facing changes.

---

*End of U02-getting-started.md*
