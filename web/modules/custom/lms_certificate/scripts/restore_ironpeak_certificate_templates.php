<?php

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;

$jsonPath = '/var/www/html/.agent/generated/certificates/silvercare-templates-from-everlms.json';
if (!is_file($jsonPath)) {
  throw new RuntimeException("Missing export file: {$jsonPath}");
}

$data = json_decode(file_get_contents($jsonPath), TRUE);
if (!is_array($data) || empty($data['templates'])) {
  throw new RuntimeException('Export file does not contain certificate templates.');
}

$entityTypeManager = \Drupal::entityTypeManager();
$templateStorage = $entityTypeManager->getStorage('lms_certificate_template');
$fileSystem = \Drupal::service('file_system');

$imageSources = [
  'background' => '/var/www/html/.agent/images/image_background',
  'logo' => '/var/www/html/.agent/images/image_logo/transparent',
  'signature' => '/var/www/html/.agent/images/image_signature/transparent',
  'seal' => '/var/www/html/.agent/images/image_seal/transparent',
];

$imageDestinations = [
  'background' => 'public://certificates/backgrounds',
  'logo' => 'public://certificates/logos',
  'signature' => 'public://certificates/signatures',
  'seal' => 'public://certificates/seals',
];

foreach ($imageDestinations as $destination) {
  $fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
}

$managedFile = static function (string $sourcePath, string $destinationUri) use ($fileSystem): File {
  $existing = \Drupal::entityQuery('file')
    ->accessCheck(FALSE)
    ->condition('uri', $destinationUri)
    ->range(0, 1)
    ->execute();

  if ($existing) {
    $file = File::load(reset($existing));
    if ($file instanceof File) {
      $file->setPermanent();
      $file->save();
      return $file;
    }
  }

  if (!is_file($sourcePath)) {
    throw new RuntimeException("Missing image source: {$sourcePath}");
  }

  $fileSystem->copy($sourcePath, $destinationUri, FileSystemInterface::EXISTS_REPLACE);
  $file = File::create([
    'uri' => $destinationUri,
    'filename' => basename($destinationUri),
    'status' => FILE_STATUS_PERMANENT,
  ]);
  $file->save();
  return $file;
};

$makeIronPeakLayout = static function (string $layout): string {
  $layout = preg_replace(
    '/<h1\b([^>]*)>.*?<\/h1>/is',
    '<h1$1>CHỨNG NHẬN HOÀN THÀNH</h1>',
    $layout,
    1
  ) ?? $layout;

  $body = <<<HTML
<p style="font-size: 16pt; line-height: 1.8; color: #334155; margin: 0 40px;">
            Chứng nhận <strong style="color: #0f172a; font-size: 20pt; font-style: italic;">[student_name]</strong>
            đã hoàn thành khóa học <strong style="color: #0f172a;">[course_name]</strong>
            vào ngày <strong>[issue_date]</strong>.
            <br>Chứng nhận này ghi nhận nỗ lực học tập, thực hành và hoàn thành đầy đủ yêu cầu đào tạo của IronPeak Gym Academy.
        </p>
HTML;

  $layout = preg_replace(
    '/<p\b[^>]*>.*?(?:\[student_name\]|\[course_name\]).*?<\/p>/is',
    $body,
    $layout,
    1
  ) ?? $layout;

  return str_replace([
    'Date of Issue',
    'Conferred On',
    'Authorized Signature',
    'Director of Nursing',
    'Academic Director',
    'SILVERCARE DEUTSCHLAND',
    'German Nursing Quality Assurance',
    'LifeSaver Training Institute',
    'Palliative Foundation Germany',
    'NeuroCare Academy',
    'Psychiatry Training Network',
    'SilverCare Linguistic Center',
  ], [
    'Ngày cấp',
    'Ngày cấp',
    'Đại diện đào tạo',
    'Giám đốc đào tạo',
    'Giám đốc đào tạo',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
    'IRONPEAK GYM ACADEMY',
  ], $layout);
};

$deleteNames = array_column($data['templates'], 'name');
$deleteIds = \Drupal::entityQuery('lms_certificate_template')
  ->accessCheck(FALSE)
  ->condition('name', $deleteNames, 'IN')
  ->execute();

$ironPeakIds = \Drupal::entityQuery('lms_certificate_template')
  ->accessCheck(FALSE)
  ->condition('name', 'Chứng chỉ IronPeak - ', 'STARTS_WITH')
  ->execute();

$deleteIds = array_unique(array_merge($deleteIds, $ironPeakIds));
if ($deleteIds) {
  $templateStorage->delete($templateStorage->loadMultiple($deleteIds));
}

$templateNames = [
  'Chứng chỉ IronPeak - Cổ điển trang trọng',
  'Chứng chỉ IronPeak - Xanh học viện',
  'Chứng chỉ IronPeak - Đỏ cao cấp',
  'Chứng chỉ IronPeak - Chuẩn chuyên nghiệp',
  'Chứng chỉ IronPeak - Tối giản hiện đại',
  'Chứng chỉ IronPeak - Vàng danh dự',
  'Chứng chỉ IronPeak - Slate mạnh mẽ',
  'Chứng chỉ IronPeak - Crimson hiện đại',
  'Chứng chỉ IronPeak - Teal năng động',
  'Chứng chỉ IronPeak - Hoàn thiện toàn diện',
];

$createdIds = [];
foreach (array_values($data['templates']) as $index => $item) {
  $fields = [
    'name' => $templateNames[$index] ?? ('Chứng chỉ IronPeak - Mẫu ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)),
    'status' => !empty($item['status']),
    'field_design' => $item['design'] ?? 'modern',
    'field_orientation' => $item['orientation'] ?? 'landscape',
    'field_layout' => $makeIronPeakLayout($item['layout'] ?? ''),
    'field_color_scheme' => $item['color_scheme'] ?? NULL,
  ];

  foreach ($item['images'] ?? [] as $key => $image) {
    if (empty($image['filename']) || empty($imageSources[$key]) || empty($imageDestinations[$key])) {
      continue;
    }
    $file = $managedFile(
      $imageSources[$key] . '/' . $image['filename'],
      $imageDestinations[$key] . '/' . $image['filename']
    );
    $fields['field_' . $key] = [
      'target_id' => $file->id(),
      'alt' => $image['alt'] ?? $fields['name'],
      'title' => $image['title'] ?? '',
    ];
  }

  $template = $templateStorage->create($fields);
  $template->save();
  $createdIds[] = (int) $template->id();
}

$courseStorage = $entityTypeManager->getStorage('lms_course');
$courseIds = \Drupal::entityQuery('lms_course')
  ->accessCheck(FALSE)
  ->execute();
$courseIndex = 0;
foreach ($courseStorage->loadMultiple($courseIds) as $course) {
  if (!$course->hasField('field_certificate_template')) {
    continue;
  }
  $course->set('field_certificate_template', ['target_id' => $createdIds[$courseIndex % count($createdIds)]]);
  $course->save();
  $courseIndex++;
}

$certStorage = $entityTypeManager->getStorage('lms_user_certificate');
$certIds = \Drupal::entityQuery('lms_user_certificate')
  ->accessCheck(FALSE)
  ->execute();
$certIndex = 0;
foreach ($certStorage->loadMultiple($certIds) as $certificate) {
  $templateId = NULL;
  if ($certificate->hasField('field_course') && !$certificate->get('field_course')->isEmpty()) {
    $course = $certificate->get('field_course')->entity;
    if ($course && $course->hasField('field_certificate_template') && !$course->get('field_certificate_template')->isEmpty()) {
      $templateId = (int) $course->get('field_certificate_template')->target_id;
    }
  }

  $certificate->set('field_template', ['target_id' => $templateId ?? $createdIds[$certIndex % count($createdIds)]]);
  if ($certificate->hasField('field_pdf_file') && !$certificate->get('field_pdf_file')->isEmpty()) {
    $certificate->set('field_pdf_file', []);
  }
  $certificate->save();
  $certIndex++;
}

drupal_flush_all_caches();

echo json_encode([
  'created_template_ids' => $createdIds,
  'course_relinked' => $courseIndex,
  'user_certificates_relinked' => $certIndex,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
