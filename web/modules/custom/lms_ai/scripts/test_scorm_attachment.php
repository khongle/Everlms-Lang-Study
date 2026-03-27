<?php
// Script to test Lesson and File loading
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../../../../autoload.php';

$autoloader = require __DIR__ . '/../../../../autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->preHandle($request);
\Drupal::setContainer($kernel->getContainer());

$lesson_storage = \Drupal::entityTypeManager()->getStorage('lms_lesson');
$file_storage = \Drupal::entityTypeManager()->getStorage('file');

$lesson = $lesson_storage->load(301);
if ($lesson) {
    echo "Lesson 301 loaded.\n";
    $scorm_field = $lesson->get('field_scorm_package')->getValue();
    echo "SCORM Field Value:\n";
    print_r($scorm_field);
    
    $file_id = $lesson->get('field_scorm_package')->target_id;
    echo "Target ID: " . $file_id . "\n";
    if ($file_id) {
        $file = $file_storage->load($file_id);
        if ($file) {
            echo "File loaded: " . $file->getFileUri() . "\n";
            echo "Is Permanent: " . $file->isPermanent() . "\n";
        } else {
            echo "File not found!\n";
        }
    }
} else {
    echo "Lesson 301 not found!\n";
}

$files = $file_storage->loadByProperties(['uri' => 'public://lessons/scorm/lesson_301_scorm_slider.zip']);
$file = reset($files);
if ($file) {
    echo "File found by URI! ID: " . $file->id() . "\n";
} else {
    echo "File not found by URI!\n";
}
