<?php

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

$curriculum = json_decode(file_get_contents($json_path), TRUE);
$course_storage = \Drupal::entityTypeManager()->getStorage('lms_course');

$updated = false;

foreach ($curriculum as &$cat) {
  $cat_name = $cat['category'];
  if (empty($cat['courses'])) continue;

  foreach ($cat['courses'] as &$course) {
    if (empty($course['is_detail_generated']) || empty($course['details'])) {
      if (!empty($course['id'])) {
        $cid = $course['id'];
        /** @var \Drupal\lms_course\Entity\Course $course_entity */
        $course_entity = $course_storage->load($cid);
        
        if ($course_entity) {
          echo "> Syncing details for course ID $cid: {$course_entity->label()} ...\n";
          
          $details = [];
          
          // Field mappings based on the JSON output structure that you described in course_detail.txt
          $fields_to_map = [
            'course_title' => 'name',
            'short_description' => 'field_short_description', 
            'description' => 'field_description',
            'what_you_will_learn' => 'field_what_you_will_learn', 
            'prerequisites' => 'field_prerequisites', 
            'who_is_this_for' => 'field_who_is_this_for', 
            'category' => 'field_category',
            'price_type' => 'field_price_type', 
            'price' => 'field_price', 
            'is_featured' => 'field_is_featured', 
            'is_bestseller' => 'field_is_bestseller', 
            'duration_minutes' => 'field_duration_minutes', 
            'meta_abstract' => 'field_meta_abstract', 
            'meta_description' => 'field_meta_description', 
            'meta_keywords' => 'field_meta_keywords', 
            'og_description' => 'field_og_description', 
            'og_determiner' => 'field_og_determiner'
          ];
          
          foreach ($fields_to_map as $json_key => $field_name) {
            if ($course_entity->hasField($field_name) && !$course_entity->get($field_name)->isEmpty()) {
              $field_def = $course_entity->getFieldDefinition($field_name);
              $type = $field_def ? $field_def->getType() : 'string';
              
              if ($type == 'boolean') {
                $details[$json_key] = (bool) $course_entity->get($field_name)->value;
              } elseif (in_array($type, ['integer', 'decimal', 'float'])) {
                $details[$json_key] = (float) $course_entity->get($field_name)->value;
              } elseif ($type == 'entity_reference') {
                // Return referenced entity name instead of ID
                $details[$json_key] = $course_entity->get($field_name)->entity ? $course_entity->get($field_name)->entity->label() : "";
              } elseif (in_array($type, ['text_long', 'text_with_summary'])) {
                $details[$json_key] = current($course_entity->get($field_name)->getValue())['value'] ?? $course_entity->get($field_name)->value;
              } else {
                $details[$json_key] = $course_entity->get($field_name)->value;
              }
            } else {
              // Defaults if field is missing or empty
              if (in_array($json_key, ['is_featured', 'is_bestseller'])) {
                $details[$json_key] = false;
              } elseif ($json_key == 'price' || $json_key == 'duration_minutes') {
                $details[$json_key] = 0;
              } else {
                $details[$json_key] = "";
              }
            }
          }
          
          // Force Category name from JSON structure in case field extraction failed
          if (empty($details['category'])) {
            $details['category'] = $cat_name;
          }
          // Force Title from label
          $details['course_title'] = $course_entity->label();

          $course['details'] = $details;
          $course['is_detail_generated'] = true;
          $updated = true;
          echo "   -> Done!\n";
        } else {
          echo "> Course ID $cid not found in database.\n";
        }
      }
    }
  }
}

if ($updated) {
  file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  echo "\nSuccessfully extracted logic from db and updated ai_curriculum.json!\n";
} else {
  echo "\nNo updates needed, all courses in JSON either have details already or lack an ID.\n";
}
