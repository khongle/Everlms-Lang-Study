<?php
// Script to batch generate slide content and SCORM for Course 21.
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require __DIR__ . '/../../../../autoload.php';

try {
    $autoloader = require __DIR__ . '/../../../../autoload.php';
    $request = Request::createFromGlobals();
    $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    $kernel->boot();
    $kernel->preHandle($request);
    \Drupal::setContainer($kernel->getContainer());

    $module_path = \Drupal::service('extension.list.module')->getPath('lms_ai');
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';
    $prompt_file = \Drupal::root() . '/' . $module_path . '/prompt/slide_content.txt';

    if (!file_exists($json_file)) {
        die("JSON file not found.");
    }
    if (!file_exists($prompt_file)) {
        die("Prompt file not found.");
    }

    $prompt_template = file_get_contents($prompt_file);
    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);

    $config = \Drupal::config('lms_ai.engine.settings');
    $training_program = $config->get('training_program') ?: 'Training Program';
    $langcode = $config->get('program_language') ?: 'en';
    $active_provider_id = $config->get('active_provider') ?: 'gemini';

    $aiProviderManager = \Drupal::service('plugin.manager.lms_ai_provider');
    $provider = $aiProviderManager->createInstance($active_provider_id);

    $scorm_builder = \Drupal::service('lms_ai.scorm_builder');
    $entity_type_manager = \Drupal::entityTypeManager();
    $file_storage = $entity_type_manager->getStorage('file');
    $lesson_storage = $entity_type_manager->getStorage('lms_lesson');

    $target_course_id = '21';
    $updated = FALSE;

    foreach ($data as &$category_data) {
        if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
            foreach ($category_data['courses'] as &$course_data) {
                $course_id = $course_data['id'] ?? NULL;
                if ($course_id !== $target_course_id) {
                    continue;
                }

                echo "Found Course $target_course_id: {$course_data['course_title']}<br/>\n";
                $course_name = $course_data['course_title'];
                $courses_list = json_encode(array_column($category_data['courses'], 'course_title'), JSON_UNESCAPED_UNICODE);

                if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
                    $chapter_list = json_encode(array_column($course_data['chapters'], 'chapter_title'), JSON_UNESCAPED_UNICODE);

                    foreach ($course_data['chapters'] as &$chapter_data) {
                        $chapter_name = $chapter_data['chapter_title'];
                        
                        if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                            $lesson_list = json_encode(array_column($chapter_data['lessons'], 'lesson_title'), JSON_UNESCAPED_UNICODE);

                            foreach ($chapter_data['lessons'] as &$lesson_data) {
                                $lesson_name = $lesson_data['lesson_title'];
                                
                                // STEP 1: Generate Slide Content if missing
                                if (empty($lesson_data['is_slide_content_generated'])) {
                                    echo "- Generating Slide Content for: $lesson_name...<br/>\n";
                                    ob_flush(); flush();
                                    $final_prompt = $prompt_template;
                                    $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
                                    $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
                                    $final_prompt = str_replace('{{course_name}}', $course_name, $final_prompt);
                                    $final_prompt = str_replace('{{chapter_name}}', $chapter_name, $final_prompt);
                                    $final_prompt = str_replace('{{lesson_name}}', $lesson_name, $final_prompt);
                                    $final_prompt = str_replace('{{courses_list}}', $courses_list, $final_prompt);
                                    $final_prompt = str_replace('{{chapter_list}}', $chapter_list, $final_prompt);
                                    $final_prompt = str_replace('{{lesson_list}}', $lesson_list, $final_prompt);

                                    $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON object. Do not include conversational text or markdown. Follow the exact JSON structure provided in your prompt.";
                                    $final_prompt .= $system_prompt;

                                    try {
                                        $ai_response = $provider->generateText($final_prompt);
                                        $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
                                        $slide_data = json_decode(trim($ai_response), TRUE);

                                        if (json_last_error() === JSON_ERROR_NONE && is_array($slide_data)) {
                                            $slide_content = $slide_data['slide_content'] ?? NULL;
                                            if ($slide_content) {
                                                $lesson_data['is_slide_content_generated'] = TRUE;
                                                $lesson_data['slide_content'] = $slide_content;
                                                $updated = TRUE;
                                                echo "  -> Slide content generated successfully.<br/>\n";
                                            } else {
                                                echo "  -> Failed: Array structure mismatch.<br/>\n";
                                            }
                                        } else {
                                            echo "  -> Failed to parse JSON from AI.<br/>\n";
                                        }
                                    } catch (\Exception $e) {
                                        echo "  -> Exception: " . $e->getMessage() . "<br/>\n";
                                    }

                                    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                } else {
                                    echo "- Slide Content already exists for: $lesson_name<br/>\n";
                                }

                                // STEP 2: Generate SCORM if missing and slide content is available
                                if (!empty($lesson_data['is_slide_content_generated']) && empty($lesson_data['is_scorm_generated'])) {
                                    echo "- Building SCORM package for: $lesson_name...<br/>\n";
                                    $scorm_payload = [
                                        'lesson_id' => $lesson_data['id'] ?? '',
                                        'lesson_title' => $lesson_data['lesson_title'] ?? '',
                                        'slides' => $lesson_data['slide_content']
                                    ];

                                    $zip_uri = $scorm_builder->buildScormFromSlides($scorm_payload);
                                    if ($zip_uri) {
                                        $files = $file_storage->loadByProperties(['uri' => $zip_uri]);
                                        $file = reset($files);
                                        if (!$file) {
                                            $file = $file_storage->create([
                                                'uri' => $zip_uri,
                                                'uid' => \Drupal::currentUser()->id() ?: 1, // Fallback to admin if CLI
                                                'status' => 1,
                                            ]);
                                            $file->save();
                                        }

                                        $lesson_entity_id = $lesson_data['id'] ?? NULL;
                                        if ($lesson_entity_id && $lesson = $lesson_storage->load($lesson_entity_id)) {
                                            $lesson->set('field_scorm_package', ['target_id' => $file->id()]);
                                            $lesson->set('field_type', 'scorm');
                                            $lesson->save();
                                            echo "  -> SCORM package attached to Lesson Entity ID $lesson_entity_id.<br/>\n";
                                        } else {
                                            echo "  -> Warning: Failed to load LMS Lesson Entity ID $lesson_entity_id.<br/>\n";
                                        }

                                        $lesson_data['is_scorm_generated'] = TRUE;
                                        $lesson_data['scorm_uri'] = $zip_uri;
                                        $updated = TRUE;
                                        echo "  -> SCORM build complete: $zip_uri<br/>\n";
                                    } else {
                                        echo "  -> Failed to build SCORM package.<br/>\n";
                                    }

                                    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                } elseif (!empty($lesson_data['is_scorm_generated'])) {
                                    echo "- SCORM already exists for: $lesson_name<br/>\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo "Batch process completed.<br/>\n";

} catch (\Throwable $e) {
    echo "================ FATAL ERROR ================\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
