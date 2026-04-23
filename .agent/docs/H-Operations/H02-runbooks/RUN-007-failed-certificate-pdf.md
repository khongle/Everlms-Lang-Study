# RUN-007 — Failed Certificate PDF Regeneration

| Field | Value |
|-------|-------|
| **ID** | RUN-007 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV3 / SEV4 |

---

## Khi nào dùng

- User hoàn thành course nhưng không nhận được certificate
- Certificate entity tồn tại trong DB nhưng file PDF không có hoặc corrupt
- PDF download trả về 404 hoặc broken file
- Sau khi update template certificate → cần re-render certificate cũ
- Sau khi fix bug trong `CertificateGenerator` → cần regenerate cho users bị ảnh hưởng

---

## Prerequisites

- [ ] Xác nhận user đã hoàn thành course (completion_percentage = 100%)
- [ ] Nếu progress chưa đúng → chạy RUN-006 trước
- [ ] Có user ID và course ID
- [ ] DDEV đang chạy

---

## Services liên quan

| Service | Class | Mô tả |
|---------|-------|-------|
| `CertificateGenerator` | `lms_certificate/Service/CertificateGenerator.php` | Generate PDF |
| `CertificateManager` | `lms_certificate/Service/CertificateManager.php` | CRUD operations |

---

## Part A — Chẩn đoán

### 1. Kiểm tra certificate entity trong DB

```bash
ddev drush sql:query "
  SELECT c.id, c.uuid, c.user_id, c.course_id, c.issued_at, c.pdf_path, c.status
  FROM lms_certificate c
  WHERE c.user_id = {USER_ID}
    AND c.course_id = {COURSE_ID};"
```

### 2. Kiểm tra file PDF có tồn tại

```bash
# Nếu pdf_path = 'private://certificates/cert-xxx.pdf'
ddev exec ls -la /var/www/html/web/sites/default/files/private/certificates/

# Hoặc kiểm tra public files
ddev exec ls -la /var/www/html/web/sites/default/files/certificates/
```

### 3. Kiểm tra log lỗi

```bash
ddev drush watchdog:show --type=lms_certificate --count=30
# Tìm: "PDF generation failed", "mPDF error", "template not found"
```

---

## Part B — Regenerate Certificate

### Option 1: Qua Drush php-eval

```bash
ddev drush php-eval "
  \$manager = \Drupal::service('lms_certificate.manager');
  \$generator = \Drupal::service('lms_certificate.generator');

  // Load certificate entity
  \$cert_ids = \Drupal::entityQuery('lms_certificate')
    ->condition('user_id', {USER_ID})
    ->condition('course_id', {COURSE_ID})
    ->execute();

  if (empty(\$cert_ids)) {
    // Certificate chưa tồn tại — tạo mới
    \$cert = \$manager->createCertificate({USER_ID}, {COURSE_ID});
    echo 'Certificate entity created: ' . \$cert->id() . PHP_EOL;
  } else {
    \$cert = \Drupal\lms_certificate\Entity\Certificate::load(reset(\$cert_ids));
    echo 'Certificate exists: ' . \$cert->id() . PHP_EOL;
  }

  // Regenerate PDF
  \$result = \$generator->generate(\$cert);
  if (\$result) {
    echo 'PDF generated successfully: ' . \$cert->get('pdf_path')->value . PHP_EOL;
  } else {
    echo 'PDF generation FAILED — check watchdog' . PHP_EOL;
  }
"
```

### Option 2: Xóa và tạo lại certificate

```bash
ddev drush php-eval "
  // Xóa certificate cũ (bao gồm PDF)
  \$cert_ids = \Drupal::entityQuery('lms_certificate')
    ->condition('user_id', {USER_ID})
    ->condition('course_id', {COURSE_ID})
    ->execute();
  if (\$cert_ids) {
    \$certs = \Drupal\lms_certificate\Entity\Certificate::loadMultiple(\$cert_ids);
    foreach (\$certs as \$c) \$c->delete();
    echo 'Old certificate deleted' . PHP_EOL;
  }

  // Trigger certificate issuance (qua event/hook)
  \Drupal::moduleHandler()->invokeAll('lms_course_completed', [
    'user_id' => {USER_ID},
    'course_id' => {COURSE_ID},
  ]);
  echo 'Certificate re-issuance triggered' . PHP_EOL;
"
```

---

## Part C — Batch Regeneration (nhiều users)

```bash
# Tìm tất cả certificate có pdf_path rỗng hoặc file không tồn tại
ddev drush sql:query "
  SELECT id, user_id, course_id FROM lms_certificate
  WHERE pdf_path IS NULL OR pdf_path = ''
  ORDER BY issued_at DESC;"

# Sau đó loop qua từng record bằng php-eval hoặc script
```

---

## Part D — Debug Template Lỗi

```bash
# Nếu lỗi do Twig template
ddev drush php-eval "
  \$template = \Drupal::theme()->getActiveTheme()->getPath()
    . '/templates/lms-certificate.html.twig';
  echo file_exists(\$template) ? 'Template exists' : 'Template MISSING';
"

# Nếu lỗi do mPDF / PDF library
ddev drush php-eval "
  \$composer = new \Mpdf\Mpdf();
  echo 'mPDF loaded OK' . PHP_EOL;
"
# PROPOSED: mPDF hoặc library PDF chưa xác nhận được dùng
```

---

## Verification

- [ ] Certificate entity có `pdf_path` không rỗng
- [ ] File PDF tồn tại trên disk
- [ ] User có thể download certificate qua `/my-learning/certificates/{cert-uuid}`
- [ ] PDF mở được và nội dung đúng (tên user, tên course, ngày cấp)

---

## Tham chiếu

- RUN-006: Kiểm tra progress trước khi regenerate certificate
- H01: Log nếu nhiều users bị ảnh hưởng
- lms_certificate: `web/modules/custom/lms_certificate/`
  - `CertificateGenerator.php`
  - `CertificateManager.php`
