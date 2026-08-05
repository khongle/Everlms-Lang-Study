<?php

$jsonPath = '/var/www/html/.agent/generated/certificates/silvercare-templates-from-everlms.json';
if (!is_file($jsonPath)) {
  throw new RuntimeException("Missing export file: {$jsonPath}");
}

$data = json_decode(file_get_contents($jsonPath), TRUE);
$oldNames = array_values(array_filter(array_column($data['templates'] ?? [], 'name')));
if (empty($oldNames)) {
  throw new RuntimeException('No template names found in export file.');
}

$storage = \Drupal::entityTypeManager()->getStorage('lms_certificate_template');
$ids = \Drupal::entityQuery('lms_certificate_template')
  ->accessCheck(FALSE)
  ->condition('name', 'Chứng chỉ IronPeak - ', 'STARTS_WITH')
  ->sort('id')
  ->execute();

if (count($ids) !== count($oldNames)) {
  throw new RuntimeException(sprintf('Expected %d IronPeak templates, found %d.', count($oldNames), count($ids)));
}

$renamed = [];
foreach (array_values($storage->loadMultiple($ids)) as $index => $template) {
  $template->set('name', $oldNames[$index]);
  $template->save();
  $renamed[] = [
    'id' => $template->id(),
    'name' => $oldNames[$index],
  ];
}

drupal_flush_all_caches();

echo json_encode([
  'renamed' => $renamed,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
